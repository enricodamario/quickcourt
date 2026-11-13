<x-app-layout>
    @php
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $warnaStatus = [
            'menunggu_pembayaran' => 'bg-amber-100 text-amber-800',
            'dikonfirmasi' => 'bg-court-soft text-court-dark',
            'dibatalkan' => 'bg-red-50 text-red-700',
            'kedaluwarsa' => 'bg-gray-200 text-gray-700',
        ];
        $kelasInput = 'h-11 px-3 rounded-xl border-gray-300 focus:border-court focus:ring-court text-sm';
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 space-y-5">
        <h1 class="text-2xl font-extrabold tracking-tight">Booking</h1>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-court-soft text-court-dark text-sm font-semibold">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-red-50 text-red-800 text-sm font-semibold">{{ session('error') }}</div>
        @endif

        {{-- Filter --}}
        <form method="GET" action="{{ route('admin.booking.index') }}"
              class="bg-white border border-line rounded-3xl p-4 flex flex-wrap items-end gap-3">
            <div class="space-y-1">
                <label for="tanggal" class="block text-xs font-semibold text-muted">Tanggal</label>
                <input id="tanggal" type="date" name="tanggal" value="{{ $filter['tanggal'] ?? '' }}" class="{{ $kelasInput }}">
            </div>
            <div class="space-y-1">
                <label for="arena_id" class="block text-xs font-semibold text-muted">Venue</label>
                <select id="arena_id" name="arena_id" class="{{ $kelasInput }}">
                    <option value="">Semua venue</option>
                    @foreach ($daftarArena as $arena)
                        <option value="{{ $arena->id }}" @selected(($filter['arena_id'] ?? null) == $arena->id)>
                            {{ $arena->nama }} ({{ $arena->jenis->nama }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label for="status" class="block text-xs font-semibold text-muted">Status</label>
                <select id="status" name="status" class="{{ $kelasInput }}">
                    <option value="">Semua status</option>
                    @foreach ($daftarStatus as $kode => $nama)
                        <option value="{{ $kode }}" @selected(($filter['status'] ?? null) === $kode)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="h-11 px-5 rounded-xl bg-ink text-volt text-sm font-extrabold">Filter</button>
            <a href="{{ route('admin.booking.index') }}" class="h-11 px-2 inline-flex items-center text-sm font-semibold text-muted hover:text-ink">Reset</a>
        </form>

        {{-- Daftar booking --}}
        <div class="bg-white border border-line rounded-3xl overflow-x-auto">
            <table class="min-w-[880px] w-full text-sm">
                <thead class="bg-ground text-xs text-muted">
                    <tr class="text-left">
                        <th class="px-4 py-3 font-bold">Kode</th>
                        <th class="px-4 py-3 font-bold">Customer</th>
                        <th class="px-4 py-3 font-bold">Venue</th>
                        <th class="px-4 py-3 font-bold">Tanggal</th>
                        <th class="px-4 py-3 font-bold">Jadwal</th>
                        <th class="px-4 py-3 font-bold">Total</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarBooking as $booking)
                        <tr class="border-t border-line align-top">
                            <td class="px-4 py-3 font-bold whitespace-nowrap">{{ $booking->kode() }}</td>
                            <td class="px-4 py-3">{{ $booking->user->name }}</td>
                            <td class="px-4 py-3">
                                <span class="font-semibold">{{ $booking->arena->nama }}</span>
                                <span class="block text-xs text-muted">{{ $booking->arena->jenis->nama }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $booking->tanggal->translatedFormat('D, j M Y') }}</td>
                            <td class="px-4 py-3">
                                @foreach ($booking->rincianJadwal() as $baris)
                                    <span class="block whitespace-nowrap">{{ $baris }}</span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ $rupiah($booking->total_harga) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap {{ $warnaStatus[$booking->status] ?? '' }}">
                                    {{ $daftarStatus[$booking->status] ?? $booking->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if (in_array($booking->status, ['menunggu_pembayaran', 'dikonfirmasi']))
                                    <form method="POST" action="{{ route('admin.booking.batalkan', $booking) }}"
                                          onsubmit="return confirm('Batalkan booking {{ $booking->kode() }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-sm font-bold text-red-700 hover:underline">Batalkan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-muted">Belum ada booking yang cocok dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $daftarBooking->links() }}
    </div>
</x-app-layout>