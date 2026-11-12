<?php

namespace Database\Seeders;

use App\Models\JenisLapangan;
use App\Models\Lapangan;
use Illuminate\Database\Seeder;

class LapanganSeeder extends Seeder
{
    public function run(): void
    {
        $jenis = [];
        foreach (['Futsal', 'Badminton', 'Basket'] as $nama) {
            $jenis[$nama] = JenisLapangan::create(['nama' => $nama]);
        }

        $daftar = [
            ['nama' => 'Lapangan Futsal A', 'jenis' => 'Futsal', 'harga_per_jam' => 150000, 'deskripsi' => 'Indoor, rumput sintetis'],
            ['nama' => 'Lapangan Futsal B', 'jenis' => 'Futsal', 'harga_per_jam' => 120000, 'deskripsi' => 'Outdoor, lantai vinyl'],
            ['nama' => 'Lapangan Badminton 1', 'jenis' => 'Badminton', 'harga_per_jam' => 60000, 'deskripsi' => 'Indoor, lantai karpet'],
            ['nama' => 'Lapangan Basket', 'jenis' => 'Basket', 'harga_per_jam' => 100000, 'deskripsi' => 'Outdoor, setengah lapangan'],
        ];

        foreach ($daftar as $data) {
            Lapangan::create([
                'nama' => $data['nama'],
                'jenis_lapangan_id' => $jenis[$data['jenis']]->id,
                'harga_per_jam' => $data['harga_per_jam'],
                'deskripsi' => $data['deskripsi'],
            ]);
        }
    }
}