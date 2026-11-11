<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lapangan extends Model
{
    protected $table = 'lapangan';

    protected $fillable = [
        'nama',
        'jenis',
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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}