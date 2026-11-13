<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kolom baru, sementara boleh kosong
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('arena_id')->nullable()->after('user_id')->constrained('arena')->restrictOnDelete();
        });

        Schema::table('booking_slots', function (Blueprint $table) {
            $table->unsignedInteger('harga')->nullable()->after('jam');
        });

        // 2. Isi data lama: venue diambil dari court-nya, harga dibagi rata ke setiap slot
        DB::table('bookings')->orderBy('id')->each(function ($booking) {
            $court = DB::table('lapangan')->where('id', $booking->lapangan_id)->first();
            $rentang = $booking->rentang_jam
                ?? substr($booking->jam_mulai, 0, 5) . '–' . substr($booking->jam_selesai, 0, 5);

            DB::table('bookings')->where('id', $booking->id)->update([
                'arena_id' => $court->arena_id,
                'rentang_jam' => $court->nama . ' · ' . $rentang,
            ]);

            $jumlahSlot = DB::table('booking_slots')->where('booking_id', $booking->id)->count();

            DB::table('booking_slots')->where('booking_id', $booking->id)->update([
                'harga' => intdiv($booking->total_harga, max(1, $jumlahSlot)),
            ]);
        });

        // 3. Setelah terisi semua, kolom baru wajib diisi
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('arena_id')->nullable(false)->change();
        });

        Schema::table('booking_slots', function (Blueprint $table) {
            $table->unsignedInteger('harga')->nullable(false)->change();
        });

        // 4. Booking tidak lagi terikat ke satu court. Detail court ada di booking_slots
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['lapangan_id']);
            $table->dropIndex(['lapangan_id', 'tanggal']);
            $table->dropColumn('lapangan_id');
            $table->index(['arena_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('lapangan_id')->nullable()->after('user_id')->constrained('lapangan')->restrictOnDelete();
        });

        // Kembalikan ke satu court: ambil court dari slot pertama, atau court pertama di venue-nya
        DB::table('bookings')->orderBy('id')->each(function ($booking) {
            $lapanganId = DB::table('booking_slots')->where('booking_id', $booking->id)->value('lapangan_id')
                ?? DB::table('lapangan')->where('arena_id', $booking->arena_id)->orderBy('id')->value('id');

            DB::table('bookings')->where('id', $booking->id)->update(['lapangan_id' => $lapanganId]);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('lapangan_id')->nullable(false)->change();
            $table->index(['lapangan_id', 'tanggal']);
            $table->dropIndex(['arena_id', 'tanggal']);
            $table->dropForeign(['arena_id']);
            $table->dropColumn('arena_id');
        });

        Schema::table('booking_slots', function (Blueprint $table) {
            $table->dropColumn('harga');
        });
    }
};