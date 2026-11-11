<?php

namespace Database\Seeders;

use App\Models\Lapangan;
use Illuminate\Database\Seeder;

class LapanganSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            ['nama' => 'Lapangan Futsal A', 'jenis' => 'futsal', 'harga_per_jam' => 150000, 'deskripsi' => 'Indoor, rumput sintetis'],
            ['nama' => 'Lapangan Futsal B', 'jenis' => 'futsal', 'harga_per_jam' => 120000, 'deskripsi' => 'Outdoor, lantai vinyl'],
            ['nama' => 'Lapangan Badminton 1', 'jenis' => 'badminton', 'harga_per_jam' => 60000, 'deskripsi' => 'Indoor, lantai karpet'],
            ['nama' => 'Lapangan Basket', 'jenis' => 'basket', 'harga_per_jam' => 100000, 'deskripsi' => 'Outdoor, setengah lapangan'],
        ];

        foreach ($daftar as $data) {
            Lapangan::create($data);
        }
    }
}