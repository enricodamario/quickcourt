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
            $table->enum('status', ['menunggu_pembayaran', 'dikonfirmasi', 'dibatalkan', 'kedaluwarsa'])
                ->default('menunggu_pembayaran');
            $table->timestamp('batas_bayar')->nullable();
            $table->string('metode_bayar', 20)->nullable();
            $table->timestamp('dibayar_at')->nullable();
            $table->timestamps();

            $table->index(['lapangan_id', 'tanggal']);
            $table->index(['status', 'batas_bayar']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};