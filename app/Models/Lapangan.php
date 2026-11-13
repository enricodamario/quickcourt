<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

// Satu baris di tabel ini = satu court di dalam sebuah arena
class Lapangan extends Model
{
    protected $table = 'lapangan';

    public const JAM_BUKA = 8;   // slot pertama 08.00
    public const JAM_TUTUP = 23; // tutup jam 23.00, jadi slot terakhir 22.00

    protected $fillable = [
        'arena_id',
        'nama',
        'harga_per_jam',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'harga_per_jam' => 'integer',
        ];
    }

    public function arena(): BelongsTo
    {
        return $this->belongsTo(Arena::class);
    }

    // Jenis olahraga court ini, diambil "melewati" arenanya: lapangan → arena → jenis_lapangan
    public function jenis(): HasOneThrough
    {
        return $this->hasOneThrough(
            JenisLapangan::class,
            Arena::class,
            'id',                 // kolom di arena yang dicocokkan dengan lapangan.arena_id
            'id',                 // kolom di jenis_lapangan yang dicocokkan dengan arena.jenis_lapangan_id
            'arena_id',           // kolom di lapangan
            'jenis_lapangan_id'   // kolom di arena
        );
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Foto court = foto arenanya
    public function fotoUrl(): ?string
    {
        return $this->arena?->fotoUrl();
    }

    // Contoh: "Vie Arena · Court 2"
    public function namaLengkap(): string
    {
        return $this->arena->nama . ' · ' . $this->nama;
    }
}