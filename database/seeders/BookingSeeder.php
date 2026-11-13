<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Lapangan;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    // Contoh booking lunas: Budi main di Kick Zone, Court 1, besok jam 19:00–21:00
    public function run(): void
    {
        $budi = User::where('email', 'budi@example.com')->firstOrFail();

        $court = Lapangan::where('nama', 'Court 1')
            ->whereHas('arena', fn ($q) => $q->where('nama', 'Kick Zone'))
            ->firstOrFail();

        $besok = today()->addDay()->toDateString();
        $daftarJam = [19, 20];

        $booking = $budi->bookings()->create([
            'arena_id' => $court->arena_id,
            'tanggal' => $besok,
            'jam_mulai' => '19:00',
            'jam_selesai' => '21:00',
            'rentang_jam' => Booking::susunRincian([['nama' => $court->nama, 'jam' => $daftarJam]]),
            'total_harga' => count($daftarJam) * $court->harga_per_jam,
            'status' => 'dikonfirmasi',
            'metode_bayar' => 'qris',
            'dibayar_at' => now(),
        ]);

        foreach ($daftarJam as $jam) {
            $booking->slots()->create([
                'lapangan_id' => $court->id,
                'tanggal' => $besok,
                'jam' => $jam,
                'harga' => $court->harga_per_jam,
            ]);
        }
    }
}