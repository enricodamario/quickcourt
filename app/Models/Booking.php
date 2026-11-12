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
        'rentang_jam',
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

    // Teks rentang jam untuk ditampilkan, misalnya "10:00–12:00, 19:00–20:00"
    public function rentangJam(): string
    {
        return $this->rentang_jam
            ?? substr($this->jam_mulai, 0, 5) . '–' . substr($this->jam_selesai, 0, 5);
    }

    // Menyusun daftar jam menjadi teks rentang. Contoh: [10, 11, 19] → "10:00–12:00, 19:00–20:00"
    public static function susunRentang(array $daftarJam): string
    {
        sort($daftarJam);

        $rentang = [];
        $mulai = $akhir = $daftarJam[0];

        foreach (array_slice($daftarJam, 1) as $jam) {
            if ($jam === $akhir + 1) {
                $akhir = $jam;
            } else {
                $rentang[] = sprintf('%02d:00–%02d:00', $mulai, $akhir + 1);
                $mulai = $akhir = $jam;
            }
        }

        $rentang[] = sprintf('%02d:00–%02d:00', $mulai, $akhir + 1);

        return implode(', ', $rentang);
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