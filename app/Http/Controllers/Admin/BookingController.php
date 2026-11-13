<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Booking;
use App\Models\BookingSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public const STATUS = ['menunggu_pembayaran', 'dikonfirmasi', 'dibatalkan', 'kedaluwarsa'];

    public function index(Request $request)
    {
        Booking::bersihkanKedaluwarsa();

        $filter = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'arena_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(self::STATUS)],
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

        return view('admin.booking.index', compact('daftarBooking', 'daftarArena', 'filter'));
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
            return back()->with('error', __('This booking is no longer active.'));
        }

        $pesan = $booking->status === 'dikonfirmasi'
            ? __('Booking cancelled. It was already paid, so please refund the customer.')
            : __('Booking cancelled.');

        return back()->with('success', $pesan);
    }
}