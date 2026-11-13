<?php

namespace Database\Seeders;

use App\Models\Arena;
use App\Models\JenisLapangan;
use Illuminate\Database\Seeder;

class LapanganSeeder extends Seeder
{
    public function run(): void
    {
        $jenis = [];
        foreach (['Futsal', 'Badminton', 'Basket', 'Padel', 'Mini Soccer'] as $nama) {
            $jenis[$nama] = JenisLapangan::create(['nama' => $nama]);
        }

        $daftarArena = [
            [
                'nama' => 'Vie Arena', 'jenis' => 'Padel',
                'alamat' => 'Jl. Kemang Raya No. 12, Jakarta Selatan',
                'deskripsi' => 'Indoor, dinding kaca panoramik',
                'harga_court' => [250000, 250000, 300000],
            ],
            [
                'nama' => 'Vie Arena', 'jenis' => 'Mini Soccer',
                'alamat' => 'Jl. Kemang Raya No. 12, Jakarta Selatan',
                'deskripsi' => 'Outdoor, rumput sintetis',
                'harga_court' => [400000, 400000],
            ],
            [
                'nama' => 'Smash Hall', 'jenis' => 'Badminton',
                'alamat' => 'Jl. Tebet Raya No. 5, Jakarta Selatan',
                'deskripsi' => 'Indoor, lantai karpet',
                'harga_court' => [60000, 60000, 60000, 60000],
            ],
            [
                'nama' => 'Kick Zone', 'jenis' => 'Futsal',
                'alamat' => 'Jl. Pemuda No. 88, Jakarta Timur',
                'deskripsi' => 'Indoor, rumput sintetis',
                'harga_court' => [150000, 120000],
            ],
            [
                'nama' => 'Hoop Spot', 'jenis' => 'Basket',
                'alamat' => 'Jl. Margonda Raya No. 20, Depok',
                'deskripsi' => 'Outdoor, setengah lapangan',
                'harga_court' => [100000],
            ],
        ];

        foreach ($daftarArena as $data) {
            $arena = Arena::create([
                'jenis_lapangan_id' => $jenis[$data['jenis']]->id,
                'nama' => $data['nama'],
                'alamat' => $data['alamat'],
                'deskripsi' => $data['deskripsi'],
            ]);

            foreach ($data['harga_court'] as $i => $harga) {
                $arena->lapangan()->create([
                    'nama' => 'Court ' . ($i + 1),
                    'harga_per_jam' => $harga,
                ]);
            }
        }
    }
}