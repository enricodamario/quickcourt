<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kelola Booking
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="p-4 rounded-md bg-green-50 text-green-800">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-md bg-red-50 text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Filter --}}
            <form method="GET" action="{{ route('admin.booking.index') }}"
                  class="bg-white shadow-sm rounded-lg p-4 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <div>
                    <label for="tanggal" class="block text-sm text-gray-600">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ $filter['tanggal'] ?? '' }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="lapangan_id" class="block text-sm text-gray-600">Lapangan</label>
                    <select id="lapangan_id" name="lapangan_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Semua lapangan</option>
                        @foreach ($daftarLapangan as $lapangan)
                            <option value="{{ $lapangan->id }}" @selected(($filter['lapangan_id'] ?? null) == $lapangan->id)>
                                {{ $lapangan->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm text-gray-600">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Semua status</option>
                        <option value="menunggu_pembayaran" @selected(($filter['status'] ?? null) === 'menunggu_pembayaran')>Menunggu pembayaran</option>
                        <option value="dikonfirmasi" @selected(($filter['status'] ?? null) === 'dikonfirmasi')>Lunas</option>
                        <option value="dibatalkan" @selected(($filter['status'] ?? null) === 'dibatalkan')>Dibatalkan</option>
                        <option value="kedaluwarsa" @selected(($filter['status'] ?? null) === 'kedaluwarsa')>Kedaluwarsa</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 rounded-md bg-gray-800 text-white font-semibold hover:bg-gray-700">Filter</button>
                    <a href="{{ route('admin.booking.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </form>

            {{-- Tabel booking --}}
            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal & jam</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lapangan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($bookings as $booking)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    {{ $booking->tanggal->translatedFormat('D, d M Y') }}<br>
                                    <span class="text-gray-500">{{ substr($booking->jam_mulai, 0, 5) }}–{{ substr($booking->jam_selesai, 0, 5) }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $booking->lapangan->nama }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $booking->user->name }}<br>
                                    <span class="text-gray-500">{{ $booking->user->email }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    Rp {{ number_format($booking->total_harga, 0, ',', '.') }}
                                    @if ($booking->metode_bayar)
                                        <br><span class="text-gray-500">{{ \App\Models\Booking::METODE_BAYAR[$booking->metode_bayar] ?? $booking->metode_bayar }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <x-status-booking :status="$booking->status" />
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if (in_array($booking->status, ['menunggu_pembayaran', 'dikonfirmasi']))
                                        <form method="POST" action="{{ route('admin.booking.batalkan', $booking) }}" class="inline"
                                              onsubmit="return confirm('Yakin ingin membatalkan booking ini? Jadwalnya akan dibuka kembali.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-sm font-semibold text-red-700 hover:underline">Batalkan</button>
                                        </form>
                                    @else
                                        <span class="text-sm text-gray-400">–</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    Tidak ada booking yang cocok dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $bookings->links() }}
            </div>
        </div>
    </div>
</x-app-layout>