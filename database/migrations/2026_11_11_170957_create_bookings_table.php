<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('lapangan_id')->constrained('lapangan')->restrictOnDelete();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->unsignedInteger('total_harga');
            $table->enum('status', ['pending', 'dikonfirmasi', 'dibatalkan'])->default('pending');
            $table->timestamps();

            $table->index(['lapangan_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
