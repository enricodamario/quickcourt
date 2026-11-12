<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Lapangan;
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
            'lapangan_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(self::STATUS)],
        ]);

        $bookings = Booking::with(['user', 'lapangan'])
            ->when($filter['tanggal'] ?? null, fn ($query, $tanggal) => $query->where('tanggal', $tanggal))
            ->when($filter['lapangan_id'] ?? null, fn ($query, $id) => $query->where('lapangan_id', $id))
            ->when($filter['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('tanggal')
            ->orderBy('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        $daftarLapangan = Lapangan::orderBy('nama')->get(['id', 'nama']);

        return view('admin.booking.index', compact('bookings', 'daftarLapangan', 'filter'));
    }

    public function batalkan(Booking $booking)
    {
        $statusAwal = $booking->status;

        $berhasil = DB::transaction(function () use ($booking) {
            $diubah = Booking::whereKey($booking->id)
                ->whereIn('status', ['menunggu_pembayaran', 'dikonfirmasi'])
                ->update(['status' => 'dibatalkan']);

            if ($diubah) {
                BookingSlot::where('booking_id', $booking->id)->delete();
            }

            return $diubah;
        });

        if (! $berhasil) {
            return back()->with('error', 'Booking ini sudah tidak aktif, jadi tidak bisa dibatalkan.');
        }

        $pesan = $statusAwal === 'dikonfirmasi'
            ? 'Booking dibatalkan dan jadwalnya dibuka kembali. Booking ini sudah dibayar, jadi lakukan pengembalian dana ke customer.'
            : 'Booking dibatalkan dan jadwalnya dibuka kembali.';

        return back()->with('success', $pesan);
    }
}