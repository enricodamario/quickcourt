<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Lapangan extends Model
{
    public const JAM_BUKA = 8;
    public const JAM_TUTUP = 23;

    protected $table = 'lapangan';

    protected $fillable = ['arena_id', 'nama', 'harga_per_jam', 'status'];

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

    public function jenis(): HasOneThrough
    {
        return $this->hasOneThrough(JenisLapangan::class, Arena::class, 'id', 'id', 'arena_id', 'jenis_lapangan_id');
    }

    // Jam-jam yang sudah dibooking di court ini
    public function slots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }

    public function fotoUrl(): ?string
    {
        return $this->arena?->fotoUrl();
    }

    public function namaLengkap(): string
    {
        return $this->arena?->nama . ' · ' . $this->nama;
    }
}