<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Booking Saya
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="p-4 rounded-md bg-green-50 text-green-800">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-md bg-red-50 text-red-800">{{ session('error') }}</div>
            @endif

            @forelse ($bookings as $booking)
                <div class="bg-white shadow-sm rounded-lg p-5 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $booking->lapangan->nama }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $booking->tanggal->locale('id')->translatedFormat('D, d M Y') }}
                                · {{ substr($booking->jam_mulai, 0, 5) }}–{{ substr($booking->jam_selesai, 0, 5) }}
                            </p>
                        </div>

                        @if ($booking->status === 'pending')
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-900">Menunggu</span>
                        @elseif ($booking->status === 'dikonfirmasi')
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Dikonfirmasi</span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between">
                        <p class="font-bold {{ $booking->status === 'dibatalkan' ? 'text-gray-400 line-through' : 'text-gray-900' }}">
                            Rp {{ number_format($booking->total_harga, 0, ',', '.') }}
                        </p>

                        @if ($booking->bisaDibatalkan())
                            <form method="POST" action="{{ route('booking.batal', $booking) }}"
                                  onsubmit="return confirm('Yakin ingin membatalkan booking ini?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="px-4 py-2 text-sm font-semibold rounded-lg border border-red-300 text-red-700 hover:bg-red-50">
                                    Batalkan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-8 text-center space-y-3">
                    <p class="text-gray-600">Kamu belum punya booking.</p>
                    <a href="{{ route('lapangan.index') }}"
                       class="inline-block px-4 py-2 rounded-lg bg-green-700 text-white font-semibold hover:bg-green-800">
                        Cari lapangan
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>