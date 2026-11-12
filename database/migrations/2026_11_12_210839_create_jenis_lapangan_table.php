<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel master jenis lapangan
        Schema::create('jenis_lapangan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->timestamps();
        });

        // 2. Tambahkan kolom foreign key di tabel lapangan (sementara boleh kosong)
        Schema::table('lapangan', function (Blueprint $table) {
            $table->foreignId('jenis_lapangan_id')
                ->nullable()
                ->after('nama')
                ->constrained('jenis_lapangan')
                ->restrictOnDelete();
        });

        // 3. Pindahkan data lama: setiap jenis unik jadi satu baris di jenis_lapangan
        $daftarJenis = DB::table('lapangan')->distinct()->pluck('jenis');

        foreach ($daftarJenis as $nama) {
            $id = DB::table('jenis_lapangan')->insertGetId([
                'nama' => ucfirst($nama),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lapangan')->where('jenis', $nama)->update(['jenis_lapangan_id' => $id]);
        }

        // 4. Setelah semua terisi: kolom FK jadi wajib, kolom lama dihapus
        Schema::table('lapangan', function (Blueprint $table) {
            $table->unsignedBigInteger('jenis_lapangan_id')->nullable(false)->change();
            $table->dropColumn('jenis');
        });
    }

    public function down(): void
    {
        // Kebalikan dari up(): kembalikan kolom jenis berisi teks, lalu hapus tabel master
        Schema::table('lapangan', function (Blueprint $table) {
            $table->string('jenis', 50)->nullable()->after('nama');
        });

        $daftarJenis = DB::table('jenis_lapangan')->pluck('nama', 'id');

        foreach ($daftarJenis as $id => $nama) {
            DB::table('lapangan')->where('jenis_lapangan_id', $id)->update(['jenis' => strtolower($nama)]);
        }

        Schema::table('lapangan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jenis_lapangan_id');
        });

        Schema::dropIfExists('jenis_lapangan');
    }
};