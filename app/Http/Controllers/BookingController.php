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
    public const MAKS_JAM = 4;

    // Halaman "Booking Saya"
    public function index(Request $request)
    {
        $bookings = $request->user()->bookings()
            ->with('lapangan')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->get();

        return view('booking.index', compact('bookings'));
    }

    // Halaman konfirmasi sebelum booking disimpan
    public function create(Request $request)
    {
        [$lapangan, $tanggal, $jamList] = $this->validasiPilihan($request);

        $sudahTerisi = BookingSlot::where('lapangan_id', $lapangan->id)
            ->where('tanggal', $tanggal->toDateString())
            ->whereIn('jam', $jamList)
            ->exists();

        if ($sudahTerisi) {
            throw ValidationException::withMessages([
                'jam' => 'Sebagian jam yang kamu pilih sudah dibooking. Silakan pilih jam lain.',
            ]);
        }

        $totalHarga = $lapangan->harga_per_jam * count($jamList);

        return view('booking.konfirmasi', compact('lapangan', 'tanggal', 'jamList', 'totalHarga'));
    }

    // Menyimpan booking
    public function store(Request $request)
    {
        [$lapangan, $tanggal, $jamList] = $this->validasiPilihan($request);

        try {
            DB::transaction(function () use ($request, $lapangan, $tanggal, $jamList) {
                $booking = Booking::create([
                    'user_id' => $request->user()->id,
                    'lapangan_id' => $lapangan->id,
                    'tanggal' => $tanggal->toDateString(),
                    'jam_mulai' => sprintf('%02d:00', $jamList[0]),
                    'jam_selesai' => sprintf('%02d:00', $jamList[count($jamList) - 1] + 1),
                    'total_harga' => $lapangan->harga_per_jam * count($jamList),
                    'status' => 'pending',
                ]);

                foreach ($jamList as $jam) {
                    BookingSlot::create([
                        'booking_id' => $booking->id,
                        'lapangan_id' => $lapangan->id,
                        'tanggal' => $tanggal->toDateString(),
                        'jam' => $jam,
                    ]);
                }
            });
        } catch (UniqueConstraintViolationException $e) {
            return redirect()
                ->route('lapangan.show', ['lapangan' => $lapangan, 'tanggal' => $tanggal->toDateString()])
                ->withErrors(['jam' => 'Maaf, jam yang kamu pilih baru saja dibooking orang lain. Silakan pilih jam lain.']);
        }

        return redirect()->route('booking.index')
            ->with('success', 'Booking berhasil dibuat dan menunggu konfirmasi admin.');
    }

    // Membatalkan booking milik sendiri
    public function batal(Request $request, Booking $booking)
    {
        abort_if($booking->user_id !== $request->user()->id, 403);

        if (! $booking->bisaDibatalkan()) {
            return redirect()->route('booking.index')
                ->with('error', 'Booking ini tidak bisa dibatalkan.');
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'dibatalkan']);
            $booking->slots()->delete();
        });

        return redirect()->route('booking.index')
            ->with('success', 'Booking berhasil dibatalkan.');
    }

    // Validasi pilihan lapangan, tanggal, dan jam. Dipakai oleh create() dan store().
    private function validasiPilihan(Request $request): array
    {
        $hariTerakhir = today()->addDays(JadwalController::HARI_KE_DEPAN - 1)->toDateString();

        $data = $request->validate([
            'lapangan_id' => ['required', 'integer', Rule::exists('lapangan', 'id')->where('status', 'aktif')],
            'tanggal' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:' . $hariTerakhir],
            'jam' => ['required', 'array', 'min:1', 'max:' . self::MAKS_JAM],
            'jam.*' => ['integer', 'distinct', 'min:' . Lapangan::JAM_BUKA, 'max:' . (Lapangan::JAM_TUTUP - 1)],
        ], [
            'jam.required' => 'Pilih minimal satu jam.',
            'jam.max' => 'Maksimal ' . self::MAKS_JAM . ' jam dalam satu booking.',
        ]);

        $jamList = collect($data['jam'])->map(fn ($jam) => (int) $jam)->sort()->values()->all();

        for ($i = 1; $i < count($jamList); $i++) {
            if ($jamList[$i] !== $jamList[$i - 1] + 1) {
                throw ValidationException::withMessages([
                    'jam' => 'Jam yang dipilih harus berurutan, misalnya 19.00 dan 20.00.',
                ]);
            }
        }

        $tanggal = Carbon::parse($data['tanggal']);

        if ($tanggal->isToday() && $jamList[0] <= now()->hour) {
            throw ValidationException::withMessages([
                'jam' => 'Jam yang dipilih sudah lewat.',
            ]);
        }

        return [Lapangan::findOrFail($data['lapangan_id']), $tanggal, $jamList];
    }
}