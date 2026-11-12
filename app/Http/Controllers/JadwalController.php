<?php

namespace App\Http\Controllers;

use App\Models\BookingSlot;
use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class JadwalController extends Controller
{
    public const HARI_KE_DEPAN = 7;

    public function index()
    {
        $daftarLapangan = Lapangan::where('status', 'aktif')
            ->orderBy('jenis')
            ->orderBy('nama')
            ->get();

        return view('jadwal.index', compact('daftarLapangan'));
    }

    public function show(Request $request, Lapangan $lapangan)
    {
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
        ]);

        $tanggal = $request->filled('tanggal')
            ? Carbon::parse($request->query('tanggal'))
            : $hariIni;

        // Ambil semua jam yang sudah terisi untuk lapangan & tanggal ini: satu query.
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
                'label' => sprintf('%02d.00', $jam),
                'status' => $status,
            ];
        }

        $daftarHari = [];
        for ($i = 0; $i < self::HARI_KE_DEPAN; $i++) {
            $hari = $hariIni->copy()->addDays($i);
            $daftarHari[] = [
                'tanggal' => $hari->toDateString(),
                'nama' => $hari->locale('id')->translatedFormat('D'),
                'angka' => $hari->format('d'),
                'aktif' => $hari->isSameDay($tanggal),
            ];
        }

        return view('jadwal.show', compact('lapangan', 'tanggal', 'slots', 'daftarHari'));
    }
}