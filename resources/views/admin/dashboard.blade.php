<x-app-layout>
    @php
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $labelStatus = [
            'menunggu_pembayaran' => [__('Awaiting payment'), 'bg-amber-100 text-amber-800'],
            'dikonfirmasi' => [__('Paid'), 'bg-volt text-ink'],
        ];
        $ringkasan = [
            [__('Active courts'), $jumlahLapangan, route('admin.arena.index')],
            [__('Awaiting payment'), $menungguPembayaran, route('admin.booking.index', ['status' => 'menunggu_pembayaran'])],
            [__('Paid bookings today'), $bookingHariIni, route('admin.booking.index', ['tanggal' => today()->toDateString(), 'status' => 'dikonfirmasi'])],
            [__('Revenue today'), $rupiah($pendapatanHariIni), null],
        ];
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 space-y-6">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight">{{ __('Dashboard') }}</h1>
            <p class="mt-1 text-sm text-muted">{{ __('Today, :date', ['date' => today()->translatedFormat('l, j F Y')]) }}</p>
        </div>

        {{-- Angka ringkasan --}}
        <div class="grid gap-3 grid-cols-2 lg:grid-cols-4">
            @foreach ($ringkasan as $item)
                @if ($item[2])
                    <a href="{{ $item[2] }}" class="bg-white border border-line rounded-3xl p-5 hover:border-ink">
                        <p class="text-sm text-muted">{{ $item[0] }}</p>
                        <p class="mt-1 text-2xl font-extrabold">{{ $item[1] }}</p>
                    </a>
                @else
                    <div class="bg-ink text-white rounded-3xl p-5">
                        <p class="text-sm text-gray-300">{{ $item[0] }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-volt">{{ $item[1] }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Jadwal hari ini --}}
        <section class="bg-white border border-line rounded-3xl overflow-hidden">
            <div class="p-5 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-extrabold">{{ __("Today's schedule") }}</h2>
                <a href="{{ route('admin.booking.index', ['tanggal' => today()->toDateString()]) }}"
                   class="text-sm font-bold text-court hover:underline">{{ __('See all bookings') }} &rarr;</a>
            </div>

            @forelse ($jadwalHariIni as $booking)
                @php $status = $labelStatus[$booking->status]; @endphp

                <div class="px-5 py-4 flex flex-wrap items-start gap-4 border-t border-line">
                    <div class="w-28 shrink-0">
                        <p class="font-extrabold">{{ substr($booking->jam_mulai, 0, 5) }}</p>
                        <p class="text-xs text-muted">{{ $booking->kode() }}</p>
                    </div>

                    <div class="flex-1 min-w-[200px]">
                        <p class="font-bold">{{ $booking->arena->nama }} · {{ $booking->arena->jenis->nama }}</p>
                        @foreach ($booking->rincianJadwal() as $baris)
                            <p class="text-sm">{{ $baris }}</p>
                        @endforeach
                        <p class="text-sm text-muted">{{ $booking->user->name }}</p>
                    </div>

                    <div class="text-right space-y-1">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold {{ $status[1] }}">{{ $status[0] }}</span>
                        <p class="text-sm font-bold">{{ $rupiah($booking->total_harga) }}</p>
                    </div>
                </div>
            @empty
                <p class="p-8 text-center text-muted border-t border-line">{{ __('No bookings today.') }}</p>
            @endforelse
        </section>
    </div>
</x-app-layout>