<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSlot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'lapangan_id',
        'tanggal',
        'jam',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}