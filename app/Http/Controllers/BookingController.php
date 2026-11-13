<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Lapangan;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public const MAKS_BELUM_BAYAR = 2;

    // Halaman "Booking saya", dengan tab Upcoming dan Past
    public function index(Request $request)
    {
        Booking::bersihkanKedaluwarsa();

        $tab = $request->query('tab') === 'past' ? 'past' : 'upcoming';
        $aktif = ['menunggu_pembayaran', 'dikonfirmasi'];

        $query = $request->user()->bookings()->with('arena.jenis');

        if ($tab === 'upcoming') {
            $query->whereDate('tanggal', '>=', today())
                ->whereIn('status', $aktif)
                ->orderBy('tanggal')
                ->orderBy('jam_mulai');
        } else {
            $query->where(function ($q) use ($aktif) {
                $q->whereDate('tanggal', '<', today())
                    ->orWhereNotIn('status', $aktif);
            })
                ->orderByDesc('tanggal')
                ->orderByDesc('jam_mulai');
        }

        $daftarBooking = $query->get();

        return view('booking.index', compact('tab', 'daftarBooking'));
    }

    // Halaman konfirmasi lama sudah tidak dipakai
    public function create()
    {
        return redirect()->route('lapangan.index');
    }

    // Membuat booking: satu venue, boleh beberapa court, jam boleh tidak berurutan
    public function store(Request $request)
    {
        Booking::bersihkanKedaluwarsa();

        $data = $request->validate([
            'arena_id' => ['required', 'integer', Rule::exists('arena', 'id')->where('status', 'aktif')],
            'tanggal' => [
                'required', 'date_format:Y-m-d', 'after_or_equal:today',
                'before_or_equal:' . today()->addDays(JadwalController::HARI_KE_DEPAN - 1)->toDateString(),
            ],
            'pilihan' => ['required', 'array'],
            'pilihan.*' => ['array'],
            'pilihan.*.*' => ['integer', 'between:' . Lapangan::JAM_BUKA . ',' . (Lapangan::JAM_TUTUP - 1)],
        ], [
            'pilihan.required' => __('Pick at least one hour.'),
        ]);

        // Rapikan pilihan: [court_id => [jam, ...]], tanpa jam ganda, urut
        $pilihan = collect($data['pilihan'])
            ->map(fn ($daftarJam) => collect($daftarJam)->map(fn ($jam) => (int) $jam)->unique()->sort()->values()->all())
            ->filter(fn ($daftarJam) => count($daftarJam) > 0)
            ->sortKeys();

        if ($pilihan->isEmpty()) {
            return back()->withErrors(['jam' => __('Pick at least one hour.')]);
        }

        // Semua court harus milik venue ini dan masih aktif
        $daftarCourt = Lapangan::where('arena_id', $data['arena_id'])
            ->where('status', 'aktif')
            ->whereIn('id', $pilihan->keys())
            ->get()
            ->keyBy('id');

        abort_if($daftarCourt->count() !== $pilihan->count(), 422);

        // Jam yang sudah lewat tidak boleh dipilih
        if ($data['tanggal'] === today()->toDateString()) {
            $jamTerawal = $pilihan->flatten()->min();

            if ($jamTerawal <= now()->hour) {
                return back()->withErrors(['jam' => __('The hour you picked has already passed.')]);
            }
        }

        $belumBayar = $request->user()->bookings()->where('status', 'menunggu_pembayaran')->count();

        if ($belumBayar >= self::MAKS_BELUM_BAYAR) {
            return back()->withErrors(['jam' => __('You still have an unpaid booking. Pay or cancel it before making a new one.')]);
        }

        try {
            $booking = DB::transaction(function () use ($request, $data, $pilihan, $daftarCourt) {
                $semuaJam = $pilihan->flatten();
                $rincian = [];
                $total = 0;

                // Harga dihitung di server dari harga tiap court, bukan dari form
                foreach ($pilihan as $courtId => $daftarJam) {
                    $court = $daftarCourt[$courtId];
                    $total += count($daftarJam) * $court->harga_per_jam;
                    $rincian[] = ['nama' => $court->nama, 'jam' => $daftarJam];
                }

                $booking = $request->user()->bookings()->create([
                    'arena_id' => $data['arena_id'],
                    'tanggal' => $data['tanggal'],
                    'jam_mulai' => sprintf('%02d:00', $semuaJam->min()),
                    'jam_selesai' => sprintf('%02d:00', $semuaJam->max() + 1),
                    'rentang_jam' => Booking::susunRincian($rincian),
                    'total_harga' => $total,
                    'status' => 'menunggu_pembayaran',
                    'batas_bayar' => now()->addMinutes(Booking::BATAS_BAYAR_MENIT),
                ]);

                // Satu baris per court per jam. Unique constraint di tabel ini yang mencegah double booking
                foreach ($pilihan as $courtId => $daftarJam) {
                    foreach ($daftarJam as $jam) {
                        $booking->slots()->create([
                            'lapangan_id' => $courtId,
                            'tanggal' => $data['tanggal'],
                            'jam' => $jam,
                            'harga' => $daftarCourt[$courtId]->harga_per_jam,
                        ]);
                    }
                }

                return $booking;
            });
        } catch (UniqueConstraintViolationException) {
            return redirect()
                ->route('lapangan.show', ['arena' => $data['arena_id'], 'tanggal' => $data['tanggal']])
                ->withErrors(['jam' => __('Sorry, someone just booked that time. Please pick another one.')]);
        }

        return redirect()->route('booking.bayar', $booking);
    }

    // Halaman pembayaran
    public function bayar(Request $request, Booking $booking)
    {
        abort_if($booking->user_id !== $request->user()->id, 403);

        Booking::bersihkanKedaluwarsa();
        $booking->refresh();

        if ($booking->status !== 'menunggu_pembayaran') {
            return redirect()->route('booking.index')
                ->with('error', __('This booking is not awaiting payment.'));
        }

        $booking->load('arena.jenis');

        return view('booking.bayar', compact('booking'));
    }

    // Simulasi pembayaran
    public function prosesBayar(Request $request, Booking $booking)
    {
        abort_if($booking->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'metode_bayar' => ['required', Rule::in(array_keys(Booking::METODE_BAYAR))],
        ], [
            'metode_bayar.required' => __('Choose a payment method.'),
        ]);

        Booking::bersihkanKedaluwarsa();

        // Update bersyarat: hanya berhasil kalau statusnya masih menunggu dan belum lewat batas
        $berhasil = Booking::whereKey($booking->id)
            ->where('status', 'menunggu_pembayaran')
            ->where('batas_bayar', '>', now())
            ->update([
                'status' => 'dikonfirmasi',
                'metode_bayar' => $data['metode_bayar'],
                'dibayar_at' => now(),
            ]);

        if (! $berhasil) {
            return redirect()->route('booking.index')
                ->with('error', __('Payment failed because the time ran out or the booking is no longer active.'));
        }

        return redirect()->route('booking.index')
            ->with('success', __('Payment successful. Your booking is confirmed.'));
    }

    // Customer membatalkan booking yang belum dibayar
    public function batal(Request $request, Booking $booking)
    {
        abort_if($booking->user_id !== $request->user()->id, 403);

        $berhasil = DB::transaction(function () use ($booking) {
            $diubah = Booking::whereKey($booking->id)
                ->where('status', 'menunggu_pembayaran')
                ->update(['status' => 'dibatalkan']);

            if ($diubah) {
                BookingSlot::where('booking_id', $booking->id)->delete();
            }

            return $diubah > 0;
        });

        if (! $berhasil) {
            return redirect()->route('booking.index')
                ->with('error', __("This booking can't be cancelled."));
        }

        return redirect()->route('booking.index', ['tab' => 'past'])
            ->with('success', __('Booking cancelled.'));
    }
}