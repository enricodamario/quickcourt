<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arena extends Model
{
    protected $table = 'arena';

    protected $fillable = ['jenis_lapangan_id', 'nama', 'alamat', 'deskripsi', 'foto', 'status'];

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(JenisLapangan::class, 'jenis_lapangan_id');
    }

    public function lapangan(): HasMany
    {
        return $this->hasMany(Lapangan::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function fotoUrl(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }
}