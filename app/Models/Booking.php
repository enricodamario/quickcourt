<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Booking extends Model
{
    public const BATAS_BAYAR_MENIT = 10;

    public const METODE_BAYAR = [
        'qris' => 'QRIS',
        'transfer' => 'Transfer bank (Virtual Account)',
        'ewallet' => 'E-wallet',
    ];

    protected $fillable = [
        'user_id',
        'lapangan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'total_harga',
        'status',
        'batas_bayar',
        'metode_bayar',
        'dibayar_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'total_harga' => 'integer',
            'batas_bayar' => 'datetime',
            'dibayar_at' => 'datetime',
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
        return $this->status === 'menunggu_pembayaran';
    }

    // Ubah booking yang lewat batas bayar menjadi kedaluwarsa, lalu buka kembali jadwalnya.
    public static function bersihkanKedaluwarsa(): void
    {
        DB::transaction(function () {
            $ids = static::where('status', 'menunggu_pembayaran')
                ->where('batas_bayar', '<', now())
                ->lockForUpdate()
                ->pluck('id');

            if ($ids->isEmpty()) {
                return;
            }

            static::whereIn('id', $ids)->update(['status' => 'kedaluwarsa']);
            BookingSlot::whereIn('booking_id', $ids)->delete();
        });
    }
}