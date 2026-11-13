<?php

namespace Database\Seeders;

use App\Models\Arena;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $budi = User::where('email', 'budi@example.com')->first();
        $lapangan = Arena::where('nama', 'Kick Zone')->first()
            ->lapangan()->where('nama', 'Court 1')->first();
        $besok = today()->addDay()->toDateString();

        $booking = Booking::create([
            'user_id' => $budi->id,
            'lapangan_id' => $lapangan->id,
            'tanggal' => $besok,
            'jam_mulai' => '19:00',
            'jam_selesai' => '21:00',
            'rentang_jam' => '19:00–21:00',
            'total_harga' => $lapangan->harga_per_jam * 2,
            'status' => 'dikonfirmasi',
            'metode_bayar' => 'qris',
            'dibayar_at' => now(),
        ]);

        foreach ([19, 20] as $jam) {
            BookingSlot::create([
                'booking_id' => $booking->id,
                'lapangan_id' => $lapangan->id,
                'tanggal' => $besok,
                'jam' => $jam,
            ]);
        }
    }
}