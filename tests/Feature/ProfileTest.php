<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_profile_bisa_dibuka(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_nama_dan_email_bisa_diubah(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', ['name' => 'Test User', 'email' => 'test@example.com'])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_status_verifikasi_tetap_kalau_email_tidak_diganti(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', ['name' => 'Test User', 'email' => $user->email])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_foto_profil_bisa_diunggah_dan_foto_lama_dihapus(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'foto' => UploadedFile::fake()->create('lama.jpg', 100, 'image/jpeg'),
        ]);
        $fotoLama = $user->refresh()->foto;

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'foto' => UploadedFile::fake()->create('baru.png', 100, 'image/png'),
        ]);
        $fotoBaru = $user->refresh()->foto;

        $this->assertNotSame($fotoLama, $fotoBaru);
        Storage::disk('public')->assertMissing($fotoLama);
        Storage::disk('public')->assertExists($fotoBaru);
    }

    public function test_foto_profil_bisa_dihapus(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'foto' => UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg'),
        ]);
        $foto = $user->refresh()->foto;

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'hapus_foto' => 1,
        ]);

        $this->assertNull($user->refresh()->foto);
        Storage::disk('public')->assertMissing($foto);
    }

    public function test_file_selain_gambar_ditolak(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'foto' => UploadedFile::fake()->create('virus.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('foto');

        $this->assertNull($user->refresh()->foto);
    }
}