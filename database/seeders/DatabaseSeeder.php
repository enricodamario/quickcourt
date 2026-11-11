<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::forceCreate([
            'name' => 'Admin Lapangan',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::forceCreate([
            'name' => 'Budi Customer',
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $this->call(LapanganSeeder::class);
    }
}
