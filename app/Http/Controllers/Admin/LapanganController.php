<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisLapangan;
use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $data = $this->validasi($request);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('lapangan', 'public');
        }

        Lapangan::create($data);

        return redirect()->route('admin.lapangan.index')
            ->with('success', __('Court added.'));
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
        $data = $this->validasi($request);
        $fotoLama = $lapangan->foto;

        if ($request->hasFile('foto')) {
            // Foto baru diunggah: simpan, lalu foto lama dihapus di bawah
            $data['foto'] = $request->file('foto')->store('lapangan', 'public');
        } elseif ($request->boolean('hapus_foto')) {
            // Admin mencentang "hapus foto" tanpa mengunggah yang baru
            $data['foto'] = null;
        }

        $lapangan->update($data);

        // Hapus file lama SETELAH database berhasil diperbarui
        if ($fotoLama && $fotoLama !== $lapangan->foto) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()->route('admin.lapangan.index')
            ->with('success', __('Court updated.'));
    }

    public function destroy(Lapangan $lapangan)
    {
        if ($lapangan->bookings()->exists()) {
            return redirect()->route('admin.lapangan.index')
                ->with('error', __("This court already has bookings, so it can't be deleted. Set it to inactive instead."));
        }

        $foto = $lapangan->foto;
        $lapangan->delete();

        if ($foto) {
            Storage::disk('public')->delete($foto);
        }

        return redirect()->route('admin.lapangan.index')
            ->with('success', __('Court deleted.'));
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'jenis_lapangan_id' => ['required', 'integer', Rule::exists('jenis_lapangan', 'id')],
            'harga_per_jam' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_foto' => ['nullable', 'boolean'],
        ], [
            'jenis_lapangan_id.required' => __('Choose a court type.'),
        ]);

        // File dan centang "hapus foto" diurus terpisah, bukan disimpan apa adanya
        unset($data['foto'], $data['hapus_foto']);

        return $data;
    }
}