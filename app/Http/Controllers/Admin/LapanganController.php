<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LapanganController extends Controller
{
    // Tambah court baru ke sebuah venue
    public function store(Request $request, Arena $arena)
    {
        $data = $request->validate([
            'harga_per_jam' => ['required', 'integer', 'min:0'],
        ]);

        // Cari nomor court yang masih kosong. Contoh: ada Court 1 dan Court 3 → court baru jadi Court 2
        $nomor = 1;
        while ($arena->lapangan()->where('nama', 'Court ' . $nomor)->exists()) {
            $nomor++;
        }

        $arena->lapangan()->create([
            'nama' => 'Court ' . $nomor,
            'harga_per_jam' => $data['harga_per_jam'],
        ]);

        return redirect()->route('admin.arena.show', $arena)
            ->with('success', __('Court added.'));
    }

    // Ubah harga dan status satu court
    public function update(Request $request, Lapangan $lapangan)
    {
        $data = $request->validate([
            'harga_per_jam' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);

        $lapangan->update($data);

        return redirect()->route('admin.arena.show', $lapangan->arena_id)
            ->with('success', __('Court updated.'));
    }

    // Hapus court yang belum pernah dibooking
    public function destroy(Lapangan $lapangan)
    {
        if ($lapangan->bookings()->exists()) {
            return redirect()->route('admin.arena.show', $lapangan->arena_id)
                ->with('error', __("This court already has bookings, so it can't be deleted. Set it to inactive instead."));
        }

        $arenaId = $lapangan->arena_id;
        $lapangan->delete();

        return redirect()->route('admin.arena.show', $arenaId)
            ->with('success', __('Court deleted.'));
    }
}