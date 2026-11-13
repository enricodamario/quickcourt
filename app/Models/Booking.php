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
        'arena_id',
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
            'batas_bayar' => 'datetime',
            'dibayar_at' => 'datetime',
            'total_harga' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function arena(): BelongsTo
    {
        return $this->belongsTo(Arena::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }

    public function bisaDibatalkan(): bool
    {
        return $this->status === 'menunggu_pembayaran';
    }

    public function kode(): string
    {
        return 'QC-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    // Ringkasan satu baris, contoh: "Court 1 · 10:00–12:00; Court 3 · 19:00–20:00"
    public function rentangJam(): string
    {
        return (string) $this->rentang_jam;
    }

    // Ringkasan per court, satu court per baris
    public function rincianJadwal(): array
    {
        return array_values(array_filter(explode('; ', (string) $this->rentang_jam)));
    }

    // [10, 11, 19] → "10:00–12:00, 19:00–20:00"
    public static function susunRentang(array $daftarJam): string
    {
        $daftarJam = array_map('intval', $daftarJam);
        sort($daftarJam);

        $hasil = [];
        $mulai = $akhir = null;

        foreach ($daftarJam as $jam) {
            if ($mulai !== null && $jam === $akhir + 1) {
                $akhir = $jam;
                continue;
            }

            if ($mulai !== null) {
                $hasil[] = sprintf('%02d:00–%02d:00', $mulai, $akhir + 1);
            }

            $mulai = $akhir = $jam;
        }

        if ($mulai !== null) {
            $hasil[] = sprintf('%02d:00–%02d:00', $mulai, $akhir + 1);
        }

        return implode(', ', $hasil);
    }

    // [['nama' => 'Court 1', 'jam' => [10, 11]], ...] → "Court 1 · 10:00–12:00; Court 3 · 19:00–20:00"
    public static function susunRincian(array $daftarCourt): string
    {
        return collect($daftarCourt)
            ->map(fn ($court) => $court['nama'] . ' · ' . self::susunRentang($court['jam']))
            ->implode('; ');
    }

    // Booking yang lewat batas bayar: status jadi kedaluwarsa dan jadwalnya dilepas
    public static function bersihkanKedaluwarsa(): void
    {
        DB::transaction(function () {
            $idKedaluwarsa = static::where('status', 'menunggu_pembayaran')
                ->where('batas_bayar', '<', now())
                ->lockForUpdate()
                ->pluck('id');

            if ($idKedaluwarsa->isEmpty()) {
                return;
            }

            static::whereIn('id', $idKedaluwarsa)->update(['status' => 'kedaluwarsa']);
            BookingSlot::whereIn('booking_id', $idKedaluwarsa)->delete();
        });
    }
}