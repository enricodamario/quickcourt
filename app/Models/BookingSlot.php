<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSlot extends Model
{
    public $timestamps = false;

    protected $fillable = ['booking_id', 'lapangan_id', 'tanggal', 'jam', 'harga'];

    protected function casts(): array
    {
        return [
            'jam' => 'integer',
            'harga' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function lapangan(): BelongsTo
    {
        return $this->belongsTo(Lapangan::class);
    }
}