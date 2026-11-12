<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lapangan extends Model
{
    protected $table = 'lapangan';

    public const JAM_BUKA = 8;   // slot pertama 08.00
    public const JAM_TUTUP = 23; // tutup jam 23.00, jadi slot terakhir 22.00

    protected $fillable = [
        'nama',
        'jenis_lapangan_id',
        'harga_per_jam',
        'deskripsi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'harga_per_jam' => 'integer',
        ];
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(JenisLapangan::class, 'jenis_lapangan_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}