<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Lapangan;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public const MAKS_BELUM_BAYAR = 2;

    // Halaman "Booking saya", dengan tab Upcoming dan Past
    public function index(Request $request)
    {
        Booking::bersihkanKedaluwarsa();

        $tab = $request->query('tab') === 'past' ? 'past' : 'upcoming';

        $query = $request->user()->bookings()->with('lapangan.jenis');

        if ($tab === 'upcoming') {
            // Masih aktif dan tanggalnya hari ini atau ke depan
            $query->whereIn('status', ['menunggu_pembayaran', 'dikonfirmasi'])
                ->where('tanggal', '>=', today()->toDateString())
                ->orderBy('tanggal')
                ->orderBy('jam_mulai');
        } else {
            // Sudah dibatalkan / kedaluwarsa, ATAU tanggalnya sudah lewat
            $query->where(function ($q) {
                $q->whereIn('status', ['dibatalkan', 'kedaluwarsa'])
                    ->orWhere('tanggal', '<', today()->toDateString());
            })
                ->orderByDesc('tanggal')
                ->orderByDesc('jam_mulai');
        }

        $bookings = $query->get();

        return view('booking.index', compact('bookings', 'tab'));
    }

    // Halaman konfirmasi lama (tidak dipakai lagi oleh tampilan, tapi tetap aman)
    public function create(Request $request)
    {
        Booking::bersihkanKedaluwarsa();

        [$lapangan, $tanggal, $jamList] = $this->validasiPilihan($request);

        $sudahTerisi = BookingSlot::where('lapangan_id', $lapangan->id)
            ->where('tanggal', $tanggal->toDateString())
            ->whereIn('jam', $jamList)
            ->exists();

        if ($sudahTerisi) {
            throw ValidationException::withMessages([
                'jam' => __('Sorry, someone just booked that time. Please pick another one.'),
            ]);
        }

        $totalHarga = $lapangan->harga_per_jam * count($jamList);

        return view('booking.konfirmasi', compact('lapangan', 'tanggal', 'jamList', 'totalHarga'));
    }

    // Membuat booking dan menahan jadwal selama batas waktu pembayaran
    public function store(Request $request)
    {
        Booking::bersihkanKedaluwarsa();

        [$lapangan, $tanggal, $jamList] = $this->validasiPilihan($request);

        $belumBayar = $request->user()->bookings()
            ->where('status', 'menunggu_pembayaran')
            ->count();

        if ($belumBayar >= self::MAKS_BELUM_BAYAR) {
            throw ValidationException::withMessages([
                'jam' => __('You still have an unpaid booking. Pay or cancel it before making a new one.'),
            ]);
        }

        try {
            $booking = DB::transaction(function () use ($request, $lapangan, $tanggal, $jamList) {
                $booking = Booking::create([
                    'user_id' => $request->user()->id,
                    'lapangan_id' => $lapangan->id,
                    'tanggal' => $tanggal->toDateString(),
                    'jam_mulai' => sprintf('%02d:00', $jamList[0]),
                    'jam_selesai' => sprintf('%02d:00', $jamList[count($jamList) - 1] + 1),
                    'rentang_jam' => Booking::susunRentang($jamList),
                    'total_harga' => $lapangan->harga_per_jam * count($jamList),
                    'status' => 'menunggu_pembayaran',
                    'batas_bayar' => now()->addMinutes(Booking::BATAS_BAYAR_MENIT),
                ]);

                foreach ($jamList as $jam) {
                    BookingSlot::create([
                        'booking_id' => $booking->id,
                        'lapangan_id' => $lapangan->id,
                        'tanggal' => $tanggal->toDateString(),
                        'jam' => $jam,
                    ]);
                }

                return $booking;
            });
        } catch (UniqueConstraintViolationException $e) {
            return redirect()
                ->route('lapangan.show', ['lapangan' => $lapangan, 'tanggal' => $tanggal->toDateString()])
                ->withErrors(['jam' => __('Sorry, someone just booked that time. Please pick another one.')]);
        }

        return redirect()->route('booking.bayar', $booking);
    }

    // Halaman pembayaran (simulasi)
    public function bayar(Request $request, Booking $booking)
    {
        abort_if($booking->user_id !== $request->user()->id, 403);

        Booking::bersihkanKedaluwarsa();
        $booking->refresh();

        if ($booking->status !== 'menunggu_pembayaran') {
            return redirect()->route('booking.index')
                ->with('error', __('This booking is not awaiting payment.'));
        }

        $booking->load('lapangan');
        $sisaDetik = max(0, (int) now()->diffInSeconds($booking->batas_bayar, false));

        return view('booking.bayar', compact('booking', 'sisaDetik'));
    }

    // Memproses pembayaran (simulasi): langsung dianggap berhasil
    public function prosesBayar(Request $request, Booking $booking)
    {
        abort_if($booking->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'metode_bayar' => ['required', Rule::in(array_keys(Booking::METODE_BAYAR))],
        ], [
            'metode_bayar.required' => __('Choose a payment method.'),
        ]);

        // Update bersyarat: hanya berhasil kalau masih menunggu pembayaran DAN belum lewat batas waktu.
        $berhasil = Booking::whereKey($booking->id)
            ->where('status', 'menunggu_pembayaran')
            ->where('batas_bayar', '>=', now())
            ->update([
                'status' => 'dikonfirmasi',
                'metode_bayar' => $data['metode_bayar'],
                'dibayar_at' => now(),
            ]);

        if (! $berhasil) {
            Booking::bersihkanKedaluwarsa();

            return redirect()->route('booking.index')
                ->with('error', __('Payment failed because the time ran out or the booking is no longer active.'));
        }

        return redirect()->route('booking.index')
            ->with('success', __('Payment successful. Your booking is confirmed.'));
    }

    // Membatalkan booking yang belum dibayar
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

            return $diubah;
        });

        if (! $berhasil) {
            return redirect()->route('booking.index')
                ->with('error', __("This booking can't be cancelled."));
        }

        return redirect()->route('booking.index')
            ->with('success', __('Booking cancelled.'));
    }

    // Validasi pilihan lapangan, tanggal, dan jam. Dipakai oleh create() dan store().
    private function validasiPilihan(Request $request): array
    {
        $hariTerakhir = today()->addDays(JadwalController::HARI_KE_DEPAN - 1)->toDateString();

        $data = $request->validate([
            'lapangan_id' => ['required', 'integer', Rule::exists('lapangan', 'id')->where('status', 'aktif')],
            'tanggal' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:' . $hariTerakhir],
            'jam' => ['required', 'array', 'min:1'],
            'jam.*' => ['integer', 'distinct', 'min:' . Lapangan::JAM_BUKA, 'max:' . (Lapangan::JAM_TUTUP - 1)],
        ], [
            'jam.required' => __('Pick at least one hour.'),
        ]);

        $jamList = collect($data['jam'])->map(fn ($jam) => (int) $jam)->sort()->values()->all();

        $tanggal = Carbon::parse($data['tanggal']);

        // Jam sudah diurutkan, jadi cukup cek jam paling awal
        if ($tanggal->isToday() && $jamList[0] <= now()->hour) {
            throw ValidationException::withMessages([
                'jam' => __('The hour you picked has already passed.'),
            ]);
        }

        return [Lapangan::findOrFail($data['lapangan_id']), $tanggal, $jamList];
    }
}