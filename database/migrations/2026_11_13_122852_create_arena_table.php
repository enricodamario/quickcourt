<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel arena: satu arena = satu jenis olahraga
        Schema::create('arena', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_lapangan_id')->constrained('jenis_lapangan')->restrictOnDelete();
            $table->string('nama', 100);
            $table->string('alamat', 255);
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            // Nama arena boleh sama asal jenisnya beda (Vie Arena Padel & Vie Arena Mini Soccer)
            $table->unique(['nama', 'jenis_lapangan_id']);
        });

        // 2. Lapangan (court) mendapat kolom arena_id, sementara boleh kosong
        Schema::table('lapangan', function (Blueprint $table) {
            $table->foreignId('arena_id')->nullable()->after('id')->constrained('arena')->restrictOnDelete();
        });

        // 3. Pindahkan data: setiap lapangan lama menjadi arena sendiri, dan lapangannya menjadi "Court 1"
        foreach (DB::table('lapangan')->orderBy('id')->get() as $lapangan) {
            $nama = $lapangan->nama;

            $sudahAda = DB::table('arena')
                ->where('nama', $nama)
                ->where('jenis_lapangan_id', $lapangan->jenis_lapangan_id)
                ->exists();

            if ($sudahAda) {
                $nama .= ' #' . $lapangan->id;
            }

            $arenaId = DB::table('arena')->insertGetId([
                'jenis_lapangan_id' => $lapangan->jenis_lapangan_id,
                'nama' => $nama,
                'alamat' => 'Alamat belum diisi',
                'deskripsi' => $lapangan->deskripsi,
                'foto' => $lapangan->foto,
                'status' => $lapangan->status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lapangan')->where('id', $lapangan->id)->update([
                'arena_id' => $arenaId,
                'nama' => 'Court 1',
            ]);
        }

        // 4. Kunci arena_id, lalu buang kolom yang sudah pindah ke arena
        Schema::table('lapangan', function (Blueprint $table) {
            $table->unsignedBigInteger('arena_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('jenis_lapangan_id');
            $table->dropColumn(['deskripsi', 'foto']);

            // Nama court tidak boleh kembar dalam satu arena
            $table->unique(['arena_id', 'nama']);
        });
    }

    public function down(): void
    {
        // Kebalikan dari up(): kembalikan kolom ke lapangan, isi dari arena, lalu hapus tabel arena
        Schema::table('lapangan', function (Blueprint $table) {
            $table->dropUnique(['arena_id', 'nama']);
            $table->foreignId('jenis_lapangan_id')->nullable()->after('id')->constrained('jenis_lapangan')->restrictOnDelete();
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
        });

        foreach (DB::table('lapangan')->get() as $lapangan) {
            $arena = DB::table('arena')->find($lapangan->arena_id);

            DB::table('lapangan')->where('id', $lapangan->id)->update([
                'jenis_lapangan_id' => $arena->jenis_lapangan_id,
                'nama' => $arena->nama . ' - ' . $lapangan->nama,
                'deskripsi' => $arena->deskripsi,
                'foto' => $arena->foto,
            ]);
        }

        Schema::table('lapangan', function (Blueprint $table) {
            $table->unsignedBigInteger('jenis_lapangan_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('arena_id');
        });

        Schema::dropIfExists('arena');
    }
};