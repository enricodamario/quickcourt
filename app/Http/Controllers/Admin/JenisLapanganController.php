<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisLapangan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JenisLapanganController extends Controller
{
    public function index()
    {
        $daftarJenis = JenisLapangan::withCount('lapangan')->orderBy('nama')->get();

        return view('admin.jenis-lapangan.index', compact('daftarJenis'));
    }

    public function create()
    {
        return view('admin.jenis-lapangan.form', ['jenisLapangan' => new JenisLapangan()]);
    }

    public function store(Request $request)
    {
        JenisLapangan::create($this->validasi($request));

        return redirect()->route('admin.jenis-lapangan.index')
            ->with('success', 'Jenis lapangan berhasil ditambahkan.');
    }

    public function edit(JenisLapangan $jenisLapangan)
    {
        return view('admin.jenis-lapangan.form', compact('jenisLapangan'));
    }

    public function update(Request $request, JenisLapangan $jenisLapangan)
    {
        $jenisLapangan->update($this->validasi($request, $jenisLapangan));

        return redirect()->route('admin.jenis-lapangan.index')
            ->with('success', 'Jenis lapangan berhasil diperbarui.');
    }

    public function destroy(JenisLapangan $jenisLapangan)
    {
        if ($jenisLapangan->lapangan()->exists()) {
            return redirect()->route('admin.jenis-lapangan.index')
                ->with('error', 'Jenis ini masih dipakai oleh lapangan, jadi tidak bisa dihapus.');
        }

        $jenisLapangan->delete();

        return redirect()->route('admin.jenis-lapangan.index')
            ->with('success', 'Jenis lapangan berhasil dihapus.');
    }

    private function validasi(Request $request, ?JenisLapangan $jenisLapangan = null): array
    {
        return $request->validate([
            'nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('jenis_lapangan', 'nama')->ignore($jenisLapangan?->id),
            ],
        ], [
            'nama.unique' => 'Jenis lapangan ini sudah ada.',
        ]);
    }
}
