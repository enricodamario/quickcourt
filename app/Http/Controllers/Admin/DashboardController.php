<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Lapangan;

class DashboardController extends Controller
{
    public function index()
    {
        Booking::bersihkanKedaluwarsa();

        // Court aktif yang venue-nya juga aktif (court ini yang bisa dibooking customer)
        $jumlahLapangan = Lapangan::where('lapangan.status', 'aktif')
            ->whereHas('arena', fn ($q) => $q->where('arena.status', 'aktif'))
            ->count();

        $menungguPembayaran = Booking::where('status', 'menunggu_pembayaran')->count();

        $bookingHariIni = Booking::whereDate('tanggal', today())
            ->where('status', 'dikonfirmasi')
            ->count();

        // Pendapatan = uang yang masuk hari ini, dihitung dari waktu pembayaran
        $pendapatanHariIni = Booking::where('status', 'dikonfirmasi')
            ->whereDate('dibayar_at', today())
            ->sum('total_harga');

        $jadwalHariIni = Booking::with(['user', 'arena.jenis'])
            ->whereDate('tanggal', today())
            ->whereIn('status', ['menunggu_pembayaran', 'dikonfirmasi'])
            ->orderBy('jam_mulai')
            ->get();

        return view('admin.dashboard', compact(
            'jumlahLapangan', 'menungguPembayaran', 'bookingHariIni', 'pendapatanHariIni', 'jadwalHariIni'
        ));
    }
}