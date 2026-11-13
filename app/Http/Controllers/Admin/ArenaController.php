<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Booking;
use App\Models\JenisLapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ArenaController extends Controller
{
    public const MAKS_COURT = 20;

    public function index()
    {
        $daftarArena = Arena::with('jenis')
            ->withCount('lapangan')
            ->withCount(['lapangan as lapangan_nonaktif_count' => fn ($q) => $q->where('status', 'nonaktif')])
            ->withMin('lapangan', 'harga_per_jam')
            ->withMax('lapangan', 'harga_per_jam')
            ->orderBy('nama')
            ->get();

        return view('admin.arena.index', compact('daftarArena'));
    }

    public function create()
    {
        return view('admin.arena.form', [
            'arena' => new Arena(),
            'daftarJenis' => JenisLapangan::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request, null, [
            'jumlah_court' => ['required', 'integer', 'min:1', 'max:' . self::MAKS_COURT],
            'harga_per_jam' => ['required', 'integer', 'min:0'],
        ]);

        $jumlahCourt = (int) $data['jumlah_court'];
        $harga = (int) $data['harga_per_jam'];
        unset($data['jumlah_court'], $data['harga_per_jam']);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('arena', 'public');
        }

        // Venue dan semua court-nya dibuat sekaligus: berhasil semua atau tidak sama sekali
        $arena = DB::transaction(function () use ($data, $jumlahCourt, $harga) {
            $arena = Arena::create($data);

            for ($i = 1; $i <= $jumlahCourt; $i++) {
                $arena->lapangan()->create([
                    'nama' => 'Court ' . $i,
                    'harga_per_jam' => $harga,
                ]);
            }

            return $arena;
        });

        return redirect()->route('admin.arena.show', $arena)
            ->with('success', __('Venue created with :count courts.', ['count' => $jumlahCourt]));
    }

    public function show(Arena $arena)
    {
        $arena->load('jenis');

        $daftarCourt = $arena->lapangan()
            ->withCount('bookings')
            ->orderBy('id')
            ->get();

        return view('admin.arena.show', compact('arena', 'daftarCourt'));
    }

    public function edit(Arena $arena)
    {
        return view('admin.arena.form', [
            'arena' => $arena,
            'daftarJenis' => JenisLapangan::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Arena $arena)
    {
        $data = $this->validasi($request, $arena);
        $fotoLama = $arena->foto;

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('arena', 'public');
        } elseif ($request->boolean('hapus_foto')) {
            $data['foto'] = null;
        }

        $arena->update($data);

        if ($fotoLama && $fotoLama !== $arena->foto) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()->route('admin.arena.show', $arena)
            ->with('success', __('Venue updated.'));
    }

    public function destroy(Arena $arena)
    {
        $punyaBooking = Booking::whereIn('lapangan_id', $arena->lapangan()->pluck('id'))->exists();

        if ($punyaBooking) {
            return redirect()->route('admin.arena.show', $arena)
                ->with('error', __("This venue already has bookings, so it can't be deleted. Set it to inactive instead."));
        }

        $foto = $arena->foto;

        DB::transaction(function () use ($arena) {
            $arena->lapangan()->delete();
            $arena->delete();
        });

        if ($foto) {
            Storage::disk('public')->delete($foto);
        }

        return redirect()->route('admin.arena.index')
            ->with('success', __('Venue deleted.'));
    }

    private function validasi(Request $request, ?Arena $arena = null, array $aturanTambahan = []): array
    {
        $data = $request->validate(array_merge([
            'nama' => [
                'required', 'string', 'max:100',
                // Nama boleh sama asal jenisnya beda
                Rule::unique('arena', 'nama')
                    ->where('jenis_lapangan_id', $request->input('jenis_lapangan_id'))
                    ->ignore($arena?->id),
            ],
            'jenis_lapangan_id' => ['required', 'integer', Rule::exists('jenis_lapangan', 'id')],
            'alamat' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_foto' => ['nullable', 'boolean'],
        ], $aturanTambahan), [
            'nama.unique' => __('This venue already exists for that sport.'),
            'jenis_lapangan_id.required' => __('Choose a sport.'),
        ]);

        unset($data['foto'], $data['hapus_foto']);

        return $data;
    }
}