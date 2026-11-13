<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class JenisLapangan extends Model
{
    protected $table = 'jenis_lapangan';

    protected $fillable = ['nama'];

    public function arena(): HasMany
    {
        return $this->hasMany(Arena::class, 'jenis_lapangan_id');
    }

    // Semua court dengan jenis ini, melewati arenanya
    public function lapangan(): HasManyThrough
    {
        return $this->hasManyThrough(Lapangan::class, Arena::class, 'jenis_lapangan_id', 'arena_id');
    }
}