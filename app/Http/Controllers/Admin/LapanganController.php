<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisLapangan;
use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LapanganController extends Controller
{
    public function index()
    {
        $daftarLapangan = Lapangan::with('jenis')->orderBy('nama')->get();

        return view('admin.lapangan.index', compact('daftarLapangan'));
    }

    public function create()
    {
        return view('admin.lapangan.form', [
            'lapangan' => new Lapangan(),
            'daftarJenis' => JenisLapangan::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Lapangan::create($this->validasi($request));

        return redirect()->route('admin.lapangan.index')
            ->with('success', 'Lapangan berhasil ditambahkan.');
    }

    public function edit(Lapangan $lapangan)
    {
        return view('admin.lapangan.form', [
            'lapangan' => $lapangan,
            'daftarJenis' => JenisLapangan::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Lapangan $lapangan)
    {
        $lapangan->update($this->validasi($request));

        return redirect()->route('admin.lapangan.index')
            ->with('success', 'Lapangan berhasil diperbarui.');
    }

    public function destroy(Lapangan $lapangan)
    {
        if ($lapangan->bookings()->exists()) {
            return redirect()->route('admin.lapangan.index')
                ->with('error', 'Lapangan ini sudah punya riwayat booking, jadi tidak bisa dihapus. Ubah statusnya menjadi nonaktif.');
        }

        $lapangan->delete();

        return redirect()->route('admin.lapangan.index')
            ->with('success', 'Lapangan berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'jenis_lapangan_id' => ['required', 'integer', Rule::exists('jenis_lapangan', 'id')],
            'harga_per_jam' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'jenis_lapangan_id.required' => 'Pilih jenis lapangan.',
        ]);
    }
}