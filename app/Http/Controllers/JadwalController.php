<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\JenisLapangan;
use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class JadwalController extends Controller
{
    public const HARI_KE_DEPAN = 7;

    public function index(Request $request)
    {
        Booking::bersihkanKedaluwarsa();

        $filter = $request->validate([
            'jenis' => ['nullable', 'integer'],
            'cari' => ['nullable', 'string', 'max:100'],
        ]);

        // Chip filter: hanya jenis yang punya lapangan aktif
        $daftarJenis = JenisLapangan::whereHas('lapangan', fn ($query) => $query->where('status', 'aktif'))
            ->orderBy('nama')
            ->get();

        $daftarLapangan = Lapangan::with('jenis')
            ->where('status', 'aktif')
            ->when($filter['jenis'] ?? null, fn ($query, $jenis) => $query->where('jenis_lapangan_id', $jenis))
            ->when($filter['cari'] ?? null, fn ($query, $cari) => $query->where('nama', 'like', '%' . $cari . '%'))
            ->orderBy('jenis_lapangan_id')
            ->orderBy('nama')
            ->get();

        // Jam terisi hari ini untuk SEMUA lapangan yang tampil, diambil dalam satu query
        $terisiHariIni = BookingSlot::whereIn('lapangan_id', $daftarLapangan->pluck('id'))
            ->where('tanggal', today()->toDateString())
            ->get(['lapangan_id', 'jam'])
            ->groupBy('lapangan_id')
            ->map(fn ($slots) => $slots->pluck('jam')->all());

        // "Available today": 3 jam kosong terdekat hari ini untuk setiap lapangan
        $jamMulai = max(Lapangan::JAM_BUKA, now()->hour + 1);
        $jamKosong = [];

        foreach ($daftarLapangan as $lapangan) {
            $terisi = $terisiHariIni[$lapangan->id] ?? [];
            $jamKosong[$lapangan->id] = [];

            for ($jam = $jamMulai; $jam < Lapangan::JAM_TUTUP && count($jamKosong[$lapangan->id]) < 3; $jam++) {
                if (! in_array($jam, $terisi)) {
                    $jamKosong[$lapangan->id][] = $jam;
                }
            }
        }

        return view('jadwal.index', compact('daftarLapangan', 'daftarJenis', 'filter', 'jamKosong'));
    }

    public function show(Request $request, Lapangan $lapangan)
    {
        Booking::bersihkanKedaluwarsa();

        abort_if($lapangan->status !== 'aktif', 404);

        $hariIni = today();
        $hariTerakhir = today()->addDays(self::HARI_KE_DEPAN - 1);

        $request->validate([
            'tanggal' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:' . $hariIni->toDateString(),
                'before_or_equal:' . $hariTerakhir->toDateString(),
            ],
            'jam' => ['nullable', 'integer'],
        ]);

        $tanggal = $request->filled('tanggal')
            ? Carbon::parse($request->query('tanggal'))
            : $hariIni;

        $jamTerisi = BookingSlot::where('lapangan_id', $lapangan->id)
            ->where('tanggal', $tanggal->toDateString())
            ->pluck('jam')
            ->all();

        $slots = [];
        for ($jam = Lapangan::JAM_BUKA; $jam < Lapangan::JAM_TUTUP; $jam++) {
            if (in_array($jam, $jamTerisi)) {
                $status = 'penuh';
            } elseif ($tanggal->isToday() && $jam <= now()->hour) {
                $status = 'lewat';
            } else {
                $status = 'tersedia';
            }

            $slots[] = [
                'jam' => $jam,
                'label' => sprintf('%02d:00', $jam),
                'status' => $status,
            ];
        }

        // Jam yang langsung terpilih kalau datang dari chip "Available today" di Explore
        $jamDipilih = [];
        if ($request->filled('jam')) {
            $jamDiminta = (int) $request->query('jam');

            foreach ($slots as $slot) {
                if ($slot['jam'] === $jamDiminta && $slot['status'] === 'tersedia') {
                    $jamDipilih = [(string) $jamDiminta];
                }
            }
        }

        $daftarHari = [];
        for ($i = 0; $i < self::HARI_KE_DEPAN; $i++) {
            $hari = $hariIni->copy()->addDays($i);
            $daftarHari[] = [
                'tanggal' => $hari->toDateString(),
                'nama' => $hari->translatedFormat('D'),
                'angka' => $hari->format('d'),
                'aktif' => $hari->isSameDay($tanggal),
            ];
        }

        return view('jadwal.show', compact('lapangan', 'tanggal', 'slots', 'daftarHari', 'jamDipilih'));
    }
}