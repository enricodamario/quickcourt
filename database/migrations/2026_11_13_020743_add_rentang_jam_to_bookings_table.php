<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('rentang_jam')->nullable()->after('jam_selesai');
        });

        // Isi untuk booking lama: semuanya masih berturut-turut, jadi cukup dari jam_mulai dan jam_selesai
        DB::table('bookings')->orderBy('id')->each(function ($booking) {
            DB::table('bookings')->where('id', $booking->id)->update([
                'rentang_jam' => substr($booking->jam_mulai, 0, 5) . '–' . substr($booking->jam_selesai, 0, 5),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('rentang_jam');
        });
    }
};