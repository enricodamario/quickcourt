<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\JenisLapanganController;
use App\Http\Controllers\Admin\LapanganController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\SetLocale;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Lapangan;
use Illuminate\Support\Facades\Route;

// Halaman depan: tamu melihat halaman login QuickCourt, yang sudah login langsung ke aplikasi.
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    Booking::bersihkanKedaluwarsa();

    // Hitung jumlah jam yang masih kosong hari ini di semua lapangan aktif
    $lapanganAktif = Lapangan::where('status', 'aktif')->pluck('id');
    $jamMulai = max(Lapangan::JAM_BUKA, now()->hour + 1);
    $semuaJam = max(0, Lapangan::JAM_TUTUP - $jamMulai) * $lapanganAktif->count();

    $jamTerisi = BookingSlot::whereIn('lapangan_id', $lapanganAktif)
        ->where('tanggal', today()->toDateString())
        ->where('jam', '>=', $jamMulai)
        ->count();

    $jamTersedia = max(0, $semuaJam - $jamTerisi);

    return view('auth.login', compact('jamTersedia'));
})->name('home');

// Ganti bahasa (EN / ID). Bisa dipakai tamu maupun yang sudah login.
Route::get('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, SetLocale::BAHASA, true), 404);

    session(['locale' => $locale]);

    return back();
})->name('language.switch');

// Pengarah setelah login: admin ke dashboard admin, customer ke daftar lapangan.
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('lapangan.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Halaman admin: wajib login DAN wajib admin.
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            Booking::bersihkanKedaluwarsa();

            $lunasHariIni = Booking::whereDate('tanggal', today())->where('status', 'dikonfirmasi');

            return view('admin.dashboard', [
                'jumlahLapangan' => Lapangan::where('status', 'aktif')->count(),
                'menungguPembayaran' => Booking::where('status', 'menunggu_pembayaran')->count(),
                'bookingHariIni' => (clone $lunasHariIni)->count(),
                'pendapatanHariIni' => (clone $lunasHariIni)->sum('total_harga'),
            ]);
        })->name('dashboard');

        Route::resource('lapangan', LapanganController::class)->except('show');

        Route::resource('jenis-lapangan', JenisLapanganController::class)
            ->parameters(['jenis-lapangan' => 'jenisLapangan'])
            ->except('show');

        Route::get('/booking', [AdminBookingController::class, 'index'])->name('booking.index');
        Route::patch('/booking/{booking}/batalkan', [AdminBookingController::class, 'batalkan'])->name('booking.batalkan');
    });

// Halaman customer: wajib login.
Route::middleware('auth')->group(function () {
    Route::get('/lapangan', [JadwalController::class, 'index'])->name('lapangan.index');
    Route::get('/lapangan/{lapangan}', [JadwalController::class, 'show'])->name('lapangan.show');

    Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
    Route::get('/booking/konfirmasi', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/{booking}/bayar', [BookingController::class, 'bayar'])->name('booking.bayar');
    Route::post('/booking/{booking}/bayar', [BookingController::class, 'prosesBayar'])->name('booking.prosesBayar');
    Route::patch('/booking/{booking}/batal', [BookingController::class, 'batal'])->name('booking.batal');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';