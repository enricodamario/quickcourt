<?php

namespace App\Http\Controllers;

use App\Models\Arena;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\JenisLapangan;
use App\Models\Lapangan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public const HARI_KE_DEPAN = 7;
    public const JAM_DITAMPILKAN = 3;

    // Halaman Explore: daftar venue
    public function index(Request $request)
    {
        Booking::bersihkanKedaluwarsa();

        $jenisDipilih = $request->integer('jenis') ?: null;
        $cari = trim((string) $request->query('cari', ''));

        // Chip jenis olahraga: hanya jenis yang punya venue aktif
        $daftarJenis = JenisLapangan::whereHas('arena', fn ($q) => $q->where('arena.status', 'aktif'))
            ->orderBy('nama')
            ->get();

        $daftarArena = Arena::query()
            ->where('arena.status', 'aktif')
            ->whereHas('lapangan', fn ($q) => $q->where('lapangan.status', 'aktif'))
            ->when($jenisDipilih, fn ($q, $id) => $q->where('jenis_lapangan_id', $id))
            ->when($cari !== '', fn ($q) => $q->where(function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                    ->orWhere('alamat', 'like', "%{$cari}%")
                    ->orWhereHas('jenis', fn ($q) => $q->where('nama', 'like', "%{$cari}%"));
            }))
            ->with([
                'jenis',
                'lapangan' => fn ($q) => $q->where('lapangan.status', 'aktif')->orderBy('id'),
            ])
            ->orderBy('nama')
            ->get();

        // Jam yang masih kosong hari ini, dihitung mulai jam berikutnya
        $jamMulai = max(Lapangan::JAM_BUKA, now()->hour + 1);
        $idCourt = $daftarArena->flatMap(fn ($arena) => $arena->lapangan->pluck('id'));

        // Satu query untuk semua court: [court_id => [19, 20, ...]]
        $terisi = BookingSlot::whereIn('lapangan_id', $idCourt)
            ->where('tanggal', today()->toDateString())
            ->where('jam', '>=', $jamMulai)
            ->get(['lapangan_id', 'jam'])
            ->groupBy('lapangan_id')
            ->map(fn ($slot) => $slot->pluck('jam')->map(fn ($jam) => (int) $jam)->all());

        // Sebuah jam dianggap tersedia kalau minimal SATU court di venue itu masih kosong
        $jamKosong = [];
        foreach ($daftarArena as $arena) {
            $jamKosong[$arena->id] = [];

            for ($jam = $jamMulai; $jam < Lapangan::JAM_TUTUP; $jam++) {
                $court = $arena->lapangan->first(fn ($c) => ! in_array($jam, $terisi[$c->id] ?? [], true));

                if ($court) {
                    $jamKosong[$arena->id][] = ['jam' => $jam, 'lapangan_id' => $court->id];
                }

                if (count($jamKosong[$arena->id]) === self::JAM_DITAMPILKAN) {
                    break;
                }
            }
        }

        return view('jadwal.index', compact('daftarArena', 'daftarJenis', 'jenisDipilih', 'cari', 'jamKosong'));
    }

    // Halaman venue: grid court × jam
    public function show(Request $request, Arena $arena)
    {
        abort_if($arena->status !== 'aktif', 404);

        Booking::bersihkanKedaluwarsa();

        $arena->load('jenis');
        $hariIni = today();
        $tanggal = $this->tanggalDariRequest($request, $hariIni);

        $daftarCourt = $arena->lapangan()
            ->where('lapangan.status', 'aktif')
            ->orderBy('id')
            ->get();

        $terisi = BookingSlot::whereIn('lapangan_id', $daftarCourt->pluck('id'))
            ->where('tanggal', $tanggal->toDateString())
            ->get(['lapangan_id', 'jam'])
            ->groupBy('lapangan_id')
            ->map(fn ($slot) => $slot->pluck('jam')->map(fn ($jam) => (int) $jam)->all());

        $daftarJam = range(Lapangan::JAM_BUKA, Lapangan::JAM_TUTUP - 1);
        $jamSekarang = now()->hour;

        // Isi grid: setiap court punya status untuk setiap jam
        $grid = $daftarCourt->map(fn ($court) => [
            'id' => $court->id,
            'nama' => $court->nama,
            'harga' => $court->harga_per_jam,
            'slot' => collect($daftarJam)->map(function ($jam) use ($court, $terisi, $tanggal, $jamSekarang) {
                if ($tanggal->isToday() && $jam <= $jamSekarang) {
                    $status = 'lewat';
                } elseif (in_array($jam, $terisi[$court->id] ?? [], true)) {
                    $status = 'penuh';
                } else {
                    $status = 'tersedia';
                }

                return ['jam' => $jam, 'status' => $status];
            })->all(),
        ])->values();

        // Pilihan awal dari link "Available today" di Explore (?lapangan=..&jam=..)
        $courtDipilih = null;
        $jamDipilih = [];
        $court = $grid->firstWhere('id', $request->integer('lapangan'));

        if ($court) {
            $courtDipilih = $court['id'];
            $slot = collect($court['slot'])->firstWhere('jam', $request->integer('jam'));

            if ($slot && $slot['status'] === 'tersedia') {
                $jamDipilih = [$slot['jam']];
            }
        }

        $daftarHari = collect(range(0, self::HARI_KE_DEPAN - 1))->map(function ($i) use ($hariIni, $tanggal) {
            $hari = $hariIni->copy()->addDays($i);

            return [
                'tanggal' => $hari->toDateString(),
                'nama' => $hari->translatedFormat('D'),
                'angka' => $hari->format('j'),
                'aktif' => $hari->isSameDay($tanggal),
            ];
        });

        return view('jadwal.show', compact(
            'arena', 'tanggal', 'daftarHari', 'daftarJam', 'grid', 'courtDipilih', 'jamDipilih'
        ));
    }

    // Tanggal dari URL hanya dipakai kalau formatnya benar dan masih dalam 7 hari ke depan
    private function tanggalDariRequest(Request $request, Carbon $hariIni): Carbon
    {
        $input = (string) $request->query('tanggal', '');

        try {
            $tanggal = Carbon::createFromFormat('!Y-m-d', $input);
        } catch (\Throwable) {
            return $hariIni->copy();
        }

        $terakhir = $hariIni->copy()->addDays(self::HARI_KE_DEPAN - 1);

        if ($tanggal->format('Y-m-d') !== $input || $tanggal->lt($hariIni) || $tanggal->gt($terakhir)) {
            return $hariIni->copy();
        }

        return $tanggal;
    }
}