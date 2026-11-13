<?php

namespace Tests\Feature;

use App\Models\Arena;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\JenisLapangan;
use App\Models\Lapangan;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Arena $arena;
    private Lapangan $lapangan;   // Court 1, Rp 100.000 / jam
    private Lapangan $lapangan2;  // Court 2, Rp 150.000 / jam
    private string $besok;

    protected function setUp(): void
    {
        parent::setUp();

        $jenis = JenisLapangan::create(['nama' => 'Futsal']);

        $this->arena = Arena::create([
            'jenis_lapangan_id' => $jenis->id,
            'nama' => 'Arena Tes',
            'alamat' => 'Jl. Tes No. 1',
        ]);

        $this->lapangan = $this->arena->lapangan()->create(['nama' => 'Court 1', 'harga_per_jam' => 100000]);
        $this->lapangan2 = $this->arena->lapangan()->create(['nama' => 'Court 2', 'harga_per_jam' => 150000]);

        $this->besok = today()->addDay()->toDateString();
    }

    // Data form booking. $pilihan: [court_id => [jam, ...]]. Kalau berupa daftar jam saja, dipakai untuk Court 1
    private function dataBooking(array $pilihan): array
    {
        if (array_is_list($pilihan)) {
            $pilihan = [$this->lapangan->id => $pilihan];
        }

        return [
            'arena_id' => $this->arena->id,
            'tanggal' => $this->besok,
            'pilihan' => $pilihan,
        ];
    }

    // ---------- Membuat booking ----------

    public function test_customer_bisa_membuat_booking_dan_jadwal_ditahan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('booking.store'), $this->dataBooking([19, 20]));

        $booking = Booking::first();
        $response->assertRedirect(route('booking.bayar', $booking));
        $this->assertSame('menunggu_pembayaran', $booking->status);
        $this->assertSame(200000, $booking->total_harga);
        $this->assertSame(2, BookingSlot::count());
    }

    public function test_harga_dihitung_di_server_bukan_dari_form(): void
    {
        $user = User::factory()->create();

        // Penyerang mencoba menyisipkan total_harga = 1
        $this->actingAs($user)->post(route('booking.store'), $this->dataBooking([19]) + ['total_harga' => 1]);

        $this->assertSame(100000, Booking::first()->total_harga);
    }

    public function test_jam_boleh_tidak_berurutan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('booking.store'), $this->dataBooking([10, 11, 19]));

        $booking = Booking::first();
        $this->assertSame('Court 1 · 10:00–12:00, 19:00–20:00', $booking->rentang_jam);
        $this->assertSame(300000, $booking->total_harga);
        $this->assertSame(3, BookingSlot::count());
    }

    public function test_satu_booking_bisa_beberapa_court(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('booking.store'), $this->dataBooking([
            $this->lapangan->id => [10, 11],
            $this->lapangan2->id => [19],
        ]));

        $booking = Booking::first();
        $this->assertSame($this->arena->id, $booking->arena_id);
        $this->assertSame('Court 1 · 10:00–12:00; Court 2 · 19:00–20:00', $booking->rentang_jam);
        $this->assertSame(2 * 100000 + 150000, $booking->total_harga);
        $this->assertSame(3, BookingSlot::count());
        $this->assertSame(150000, BookingSlot::where('lapangan_id', $this->lapangan2->id)->first()->harga);
    }

    public function test_harga_booking_tidak_berubah_saat_admin_mengubah_harga_court(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('booking.store'), $this->dataBooking([19]));

        $this->lapangan->update(['harga_per_jam' => 999000]);

        $this->assertSame(100000, Booking::first()->total_harga);
        $this->assertSame(100000, BookingSlot::first()->harga);
    }

    public function test_court_dari_venue_lain_ditolak(): void
    {
        $user = User::factory()->create();

        $venueLain = Arena::create([
            'jenis_lapangan_id' => $this->arena->jenis_lapangan_id,
            'nama' => 'Venue Lain',
            'alamat' => 'Jl. Lain No. 2',
        ]);
        $courtLain = $venueLain->lapangan()->create(['nama' => 'Court 1', 'harga_per_jam' => 1000]);

        // Venue di form: Arena Tes, tapi court-nya milik Venue Lain
        $this->actingAs($user)
            ->post(route('booking.store'), $this->dataBooking([$courtLain->id => [19]]))
            ->assertStatus(422);

        $this->assertSame(0, Booking::count());
    }

    public function test_maksimal_dua_booking_belum_dibayar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('booking.store'), $this->dataBooking([10]));
        $this->actingAs($user)->post(route('booking.store'), $this->dataBooking([12]));

        $this->actingAs($user)
            ->post(route('booking.store'), $this->dataBooking([14]))
            ->assertSessionHasErrors('jam');

        $this->assertSame(2, Booking::count());
    }

    // ---------- Anti double booking ----------

    public function test_jam_yang_sudah_dibooking_tidak_bisa_dibooking_lagi(): void
    {
        $budi = User::factory()->create();
        $andi = User::factory()->create();

        $this->actingAs($budi)->post(route('booking.store'), $this->dataBooking([19, 20]));

        // Andi mencoba jam 20-21: jam 20 bentrok dengan Budi
        $response = $this->actingAs($andi)->post(route('booking.store'), $this->dataBooking([20, 21]));

        $response->assertRedirect(route('lapangan.show', ['arena' => $this->arena->id, 'tanggal' => $this->besok]));
        $response->assertSessionHasErrors('jam');

        // Transaction di-rollback: booking Andi tidak tersimpan, termasuk slot jam 21 yang sebenarnya kosong
        $this->assertSame(1, Booking::count());
        $this->assertSame(2, BookingSlot::count());
    }

    public function test_jam_sama_di_court_berbeda_boleh(): void
    {
        $budi = User::factory()->create();
        $andi = User::factory()->create();

        $this->actingAs($budi)->post(route('booking.store'), $this->dataBooking([$this->lapangan->id => [19]]));
        $this->actingAs($andi)->post(route('booking.store'), $this->dataBooking([$this->lapangan2->id => [19]]));

        $this->assertSame(2, Booking::count());
    }

    public function test_database_menolak_slot_ganda(): void
    {
        $user = User::factory()->create();

        $booking = Booking::create([
            'user_id' => $user->id,
            'arena_id' => $this->arena->id,
            'tanggal' => $this->besok,
            'jam_mulai' => '19:00',
            'jam_selesai' => '20:00',
            'rentang_jam' => 'Court 1 · 19:00–20:00',
            'total_harga' => 100000,
            'status' => 'dikonfirmasi',
        ]);

        $slot = [
            'booking_id' => $booking->id,
            'lapangan_id' => $this->lapangan->id,
            'tanggal' => $this->besok,
            'jam' => 19,
            'harga' => 100000,
        ];

        BookingSlot::create($slot);

        $this->expectException(UniqueConstraintViolationException::class);
        BookingSlot::create($slot);
    }

    // ---------- Pembayaran ----------

    public function test_pembayaran_mengonfirmasi_booking(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('booking.store'), $this->dataBooking([19]));
        $booking = Booking::first();

        $this->actingAs($user)
            ->post(route('booking.prosesBayar', $booking), ['metode_bayar' => 'qris'])
            ->assertRedirect(route('booking.index'));

        $booking->refresh();
        $this->assertSame('dikonfirmasi', $booking->status);
        $this->assertSame('qris', $booking->metode_bayar);
        $this->assertNotNull($booking->dibayar_at);
    }

    public function test_booking_kedaluwarsa_tidak_bisa_dibayar_dan_jadwal_dibuka_lagi(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('booking.store'), $this->dataBooking([19]));
        $booking = Booking::first();

        // "Lompat" ke masa depan, melewati batas waktu pembayaran
        $this->travel(Booking::BATAS_BAYAR_MENIT + 1)->minutes();

        $this->actingAs($user)
            ->post(route('booking.prosesBayar', $booking), ['metode_bayar' => 'qris'])
            ->assertSessionHas('error');

        $booking->refresh();
        $this->assertSame('kedaluwarsa', $booking->status);
        $this->assertSame(0, BookingSlot::count());
    }

    // ---------- Keamanan ----------

    public function test_user_tidak_bisa_membatalkan_booking_orang_lain(): void
    {
        $budi = User::factory()->create();
        $andi = User::factory()->create();

        $this->actingAs($budi)->post(route('booking.store'), $this->dataBooking([19]));
        $booking = Booking::first();

        $this->actingAs($andi)
            ->patch(route('booking.batal', $booking))
            ->assertForbidden();

        $this->assertSame('menunggu_pembayaran', $booking->fresh()->status);
    }

    public function test_customer_tidak_bisa_membuka_halaman_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_bisa_membuka_halaman_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }
}