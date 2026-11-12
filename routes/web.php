<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\LapanganController;
use App\Models\Booking;
use App\Models\Lapangan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard customer. Kalau yang login admin, langsung dialihkan ke dashboard admin.
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Semua halaman admin: wajib login DAN wajib admin.
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard', [
                'jumlahLapangan' => Lapangan::count(),
                'bookingPending' => Booking::where('status', 'pending')->count(),
                'bookingHariIni' => Booking::whereDate('tanggal', today())->count(),
            ]);
        })->name('dashboard');

        Route::resource('lapangan', LapanganController::class)->except('show');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
