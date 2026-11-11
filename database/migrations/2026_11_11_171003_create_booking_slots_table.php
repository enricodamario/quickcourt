<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lapangan_id')->constrained('lapangan')->restrictOnDelete();
            $table->date('tanggal');
            $table->unsignedTinyInteger('jam');

            $table->unique(['lapangan_id', 'tanggal', 'jam'], 'uniq_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_slots');
    }
};
