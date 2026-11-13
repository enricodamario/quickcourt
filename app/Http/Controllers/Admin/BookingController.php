<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Booking;
use App\Models\BookingSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public const STATUS = [
        'menunggu_pembayaran' => 'Menunggu pembayaran',
        'dikonfirmasi' => 'Lunas',
        'dibatalkan' => 'Dibatalkan',
        'kedaluwarsa' => 'Kedaluwarsa',
    ];

    public function index(Request $request)
    {
        Booking::bersihkanKedaluwarsa();

        $filter = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'arena_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:' . implode(',', array_keys(self::STATUS))],
        ]);

        $daftarBooking = Booking::with(['user', 'arena.jenis'])
            ->when($filter['tanggal'] ?? null, fn ($q, $tanggal) => $q->whereDate('tanggal', $tanggal))
            ->when($filter['arena_id'] ?? null, fn ($q, $id) => $q->where('arena_id', $id))
            ->when($filter['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        $daftarArena = Arena::with('jenis')->orderBy('nama')->get();

        return view('admin.booking.index', [
            'daftarBooking' => $daftarBooking,
            'daftarArena' => $daftarArena,
            'daftarStatus' => self::STATUS,
            'filter' => $filter,
        ]);
    }

    // Admin membatalkan booking (misalnya lapangan rusak). Kalau sudah dibayar, refund dilakukan manual
    public function batalkan(Booking $booking)
    {
        $berhasil = DB::transaction(function () use ($booking) {
            $diubah = Booking::whereKey($booking->id)
                ->whereIn('status', ['menunggu_pembayaran', 'dikonfirmasi'])
                ->update(['status' => 'dibatalkan']);

            if ($diubah) {
                BookingSlot::where('booking_id', $booking->id)->delete();
            }

            return $diubah > 0;
        });

        if (! $berhasil) {
            return back()->with('error', 'Booking ini sudah tidak aktif.');
        }

        $pesan = $booking->status === 'dikonfirmasi'
            ? 'Booking dibatalkan. Booking ini sudah dibayar, jadi lakukan refund ke customer.'
            : 'Booking dibatalkan.';

        return back()->with('success', $pesan);
    }
}