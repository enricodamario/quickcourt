<?php

namespace Tests\Feature;

use App\Models\Arena;
use App\Models\JenisLapangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArenaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private JenisLapangan $padel;
    private JenisLapangan $futsal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->padel = JenisLapangan::create(['nama' => 'Padel']);
        $this->futsal = JenisLapangan::create(['nama' => 'Futsal']);
    }

    private function dataVenue(array $ubah = []): array
    {
        return array_merge([
            'nama' => 'Vie Arena',
            'jenis_lapangan_id' => $this->padel->id,
            'alamat' => 'Jl. Kemang Raya No. 12',
            'status' => 'aktif',
            'jumlah_court' => 3,
            'harga_per_jam' => 250000,
        ], $ubah);
    }

    public function test_membuat_venue_sekaligus_membuat_court(): void
    {
        $this->actingAs($this->admin)->post(route('admin.arena.store'), $this->dataVenue());

        $arena = Arena::first();
        $this->assertSame(
            ['Court 1', 'Court 2', 'Court 3'],
            $arena->lapangan()->orderBy('id')->pluck('nama')->all()
        );
        $this->assertSame(3, $arena->lapangan()->where('harga_per_jam', 250000)->count());
    }

    public function test_nama_venue_sama_ditolak_untuk_jenis_yang_sama(): void
    {
        $this->actingAs($this->admin)->post(route('admin.arena.store'), $this->dataVenue());

        $this->actingAs($this->admin)
            ->post(route('admin.arena.store'), $this->dataVenue())
            ->assertSessionHasErrors('nama');

        $this->assertSame(1, Arena::count());
    }

    public function test_nama_venue_sama_boleh_untuk_jenis_berbeda(): void
    {
        $this->actingAs($this->admin)->post(route('admin.arena.store'), $this->dataVenue());

        $this->actingAs($this->admin)
            ->post(route('admin.arena.store'), $this->dataVenue(['jenis_lapangan_id' => $this->futsal->id]))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Arena::count());
    }

    public function test_court_baru_mengisi_nomor_yang_kosong(): void
    {
        $this->actingAs($this->admin)->post(route('admin.arena.store'), $this->dataVenue());
        $arena = Arena::first();

        // Hapus Court 2, lalu tambah court baru → harus jadi Court 2 lagi
        $court2 = $arena->lapangan()->where('nama', 'Court 2')->first();
        $this->actingAs($this->admin)->delete(route('admin.lapangan.destroy', $court2));
        $this->actingAs($this->admin)->post(route('admin.lapangan.store', $arena), ['harga_per_jam' => 250000]);

        $this->assertSame(
            ['Court 1', 'Court 2', 'Court 3'],
            $arena->lapangan()->orderBy('nama')->pluck('nama')->all()
        );
    }

    public function test_court_dan_venue_yang_punya_booking_tidak_bisa_dihapus(): void
    {
        $this->actingAs($this->admin)->post(route('admin.arena.store'), $this->dataVenue());
        $arena = Arena::first();
        $court = $arena->lapangan()->first();

        $customer = User::factory()->create();
        $this->actingAs($customer)->post(route('booking.store'), [
            'arena_id' => $arena->id,
            'tanggal' => today()->addDay()->toDateString(),
            'pilihan' => [$court->id => [19]],
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.lapangan.destroy', $court))
            ->assertSessionHas('error');

        $this->actingAs($this->admin)
            ->delete(route('admin.arena.destroy', $arena))
            ->assertSessionHas('error');

        $this->assertSame(1, Arena::count());
        $this->assertSame(3, $arena->lapangan()->count());
    }

    public function test_customer_tidak_bisa_mengelola_venue(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->post(route('admin.arena.store'), $this->dataVenue())
            ->assertForbidden();

        $this->assertSame(0, Arena::count());
    }
}