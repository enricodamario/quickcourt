<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'lapangan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'total_harga',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'total_harga' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lapangan(): BelongsTo
    {
        return $this->belongsTo(Lapangan::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }

        public function bisaDibatalkan(): bool
    {
        return $this->status !== 'dibatalkan' && $this->tanggal->gte(today());
    }
}