<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email']));

        // Email diganti → status verifikasi direset
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $fotoLama = $user->foto;

        if ($request->hasFile('foto')) {
            $user->foto = $request->file('foto')->store('avatar', 'public');
        } elseif ($request->boolean('hapus_foto')) {
            $user->foto = null;
        }

        $user->save();

        // File lama dihapus SETELAH data tersimpan, supaya tidak ada user yang menunjuk ke file yang sudah hilang
        if ($fotoLama && $fotoLama !== $user->foto) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }
}