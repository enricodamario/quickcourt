<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisLapangan extends Model
{
    protected $table = 'jenis_lapangan';

    protected $fillable = ['nama'];

    public function lapangan(): HasMany
    {
        return $this->hasMany(Lapangan::class);
    }
}