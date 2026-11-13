<x-app-layout>
    @php
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $namaMetode = ['qris' => 'QRIS', 'transfer' => __('Bank transfer'), 'ewallet' => __('E-wallet')];
        $labelStatus = [
            'menunggu_pembayaran' => [__('Awaiting payment'), 'bg-amber-100 text-amber-800'],
            'dikonfirmasi' => [__('Paid'), 'bg-volt text-ink'],
            'kedaluwarsa' => [__('Expired'), 'bg-gray-200 text-gray-700'],
            'dibatalkan' => [__('Cancelled'), 'bg-red-50 text-red-700'],
        ];
    @endphp

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 space-y-5">
        <h1 class="text-2xl font-extrabold tracking-tight">{{ __('My bookings') }}</h1>

        <nav class="flex p-1 gap-1 rounded-2xl bg-gray-200/70">
            <a href="{{ route('booking.index') }}"
               @class([
                   'flex-1 h-10 rounded-xl text-sm inline-flex items-center justify-center',
                   'bg-white font-bold' => $tab === 'upcoming',
                   'text-muted font-semibold' => $tab !== 'upcoming',
               ])>{{ __('Upcoming') }}</a>
            <a href="{{ route('booking.index', ['tab' => 'past']) }}"
               @class([
                   'flex-1 h-10 rounded-xl text-sm inline-flex items-center justify-center',
                   'bg-white font-bold' => $tab === 'past',
                   'text-muted font-semibold' => $tab !== 'past',
               ])>{{ __('Past') }}</a>
        </nav>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-court-soft text-court-dark text-sm font-semibold">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-red-50 text-red-800 text-sm font-semibold">{{ session('error') }}</div>
        @endif

        <div class="space-y-4">
            @forelse ($daftarBooking as $booking)
                @if ($tab === 'upcoming' && $booking->status === 'dikonfirmasi')
                    {{-- Tiket booking yang sudah dibayar --}}
                    <article class="bg-white border border-line rounded-3xl overflow-hidden">
                        <div class="bg-court text-white px-5 py-4 flex items-start justify-between gap-3">
                            <div class="min-w-0 space-y-1">
                                <p class="text-[11px] font-bold tracking-wide text-court-soft uppercase">{{ $booking->arena->jenis->nama }}</p>
                                <h2 class="text-lg font-extrabold">{{ $booking->arena->nama }}</h2>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-volt text-ink text-xs font-extrabold">{{ __('Paid') }}</span>
                        </div>

                        <div class="px-5 py-4 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-muted">{{ __('Date') }}</p>
                                    <p class="font-bold">{{ $booking->tanggal->translatedFormat('D, j M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted">{{ __('Schedule') }}</p>
                                    @foreach ($booking->rincianJadwal() as $baris)
                                        <p class="font-bold">{{ $baris }}</p>
                                    @endforeach
                                </div>
                            </div>
                            <p class="flex items-start gap-1.5 text-xs text-muted">
                                <svg class="w-3.5 h-3.5 shrink-0 mt-px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                                    <path d="M12 21s-7-6.5-7-12a7 7 0 0 1 14 0c0 5.5-7 12-7 12z"></path><circle cx="12" cy="9" r="2.5"></circle>
                                </svg>
                                {{ $booking->arena->alamat }}
                            </p>
                        </div>

                        <div class="flex items-center" aria-hidden="true">
                            <span class="w-3 h-6 rounded-r-full bg-ground -ml-px"></span>
                            <span class="flex-1 border-t-2 border-dashed border-line mx-1.5"></span>
                            <span class="w-3 h-6 rounded-l-full bg-ground -mr-px"></span>
                        </div>

                        <div class="px-5 pt-3 pb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs text-muted">{{ __('Booking code') }}</p>
                                <p class="font-extrabold tracking-wider">{{ $booking->kode() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-muted">{{ __('Paid via :method', ['method' => $namaMetode[$booking->metode_bayar] ?? '-']) }}</p>
                                <p class="font-extrabold">{{ $rupiah($booking->total_harga) }}</p>
                            </div>
                        </div>
                    </article>
                @elseif ($tab === 'upcoming')
                    {{-- Booking yang masih menunggu pembayaran --}}
                    @php $sisaDetik = max(0, (int) now()->diffInSeconds($booking->batas_bayar, false)); @endphp

                    <article class="bg-white border border-amber-300 rounded-3xl p-5 space-y-3"
                             x-data="{ sisa: {{ $sisaDetik }} }"
                             x-init="const t = setInterval(() => { sisa > 0 ? sisa-- : clearInterval(t) }, 1000)">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="font-extrabold">{{ $booking->arena->nama }} · {{ $booking->arena->jenis->nama }}</h2>
                                <p class="text-sm text-muted">{{ $booking->tanggal->translatedFormat('D, j M Y') }}</p>
                                @foreach ($booking->rincianJadwal() as $baris)
                                    <p class="text-sm font-semibold">{{ $baris }}</p>
                                @endforeach
                            </div>
                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold whitespace-nowrap">{{ __('Awaiting payment') }}</span>
                        </div>

                        <p class="text-sm font-semibold text-amber-800"
                           x-text="@js(__('Held for you · :time left', ['time' => '__WAKTU__'])).replace('__WAKTU__', String(Math.floor(sisa / 60)).padStart(2, '0') + ':' + String(sisa % 60).padStart(2, '0'))"></p>

                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('booking.bayar', $booking) }}"
                               class="h-11 px-5 rounded-xl bg-ink text-volt text-sm font-extrabold inline-flex items-center hover:brightness-110">
                                {{ __('Pay now · :amount', ['amount' => $rupiah($booking->total_harga)]) }}
                            </a>
                            <form method="POST" action="{{ route('booking.batal', $booking) }}"
                                  onsubmit="return confirm(@js(__('Cancel this booking?')))">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-sm font-semibold text-red-700 hover:underline">{{ __('Cancel') }}</button>
                            </form>
                        </div>
                    </article>
                @else
                    {{-- Riwayat --}}
                    @php [$teksStatus, $kelasStatus] = $labelStatus[$booking->status] ?? [$booking->status, 'bg-gray-200 text-gray-700']; @endphp

                    <article class="bg-white border border-line rounded-3xl p-5 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-bold">{{ $booking->arena->nama }} · {{ $booking->arena->jenis->nama }}</h2>
                            <p class="text-sm text-muted">{{ $booking->tanggal->translatedFormat('D, j M Y') }} · {{ $booking->kode() }}</p>
                            @foreach ($booking->rincianJadwal() as $baris)
                                <p class="text-sm">{{ $baris }}</p>
                            @endforeach
                            <p class="mt-1 text-sm font-bold">{{ $rupiah($booking->total_harga) }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap {{ $kelasStatus }}">{{ $teksStatus }}</span>
                    </article>
                @endif
            @empty
                <div class="bg-white border border-line rounded-3xl p-8 text-center space-y-4">
                    <p class="font-semibold">{{ $tab === 'upcoming' ? __('No upcoming bookings yet.') : __('No past bookings yet.') }}</p>
                    <a href="{{ route('lapangan.index') }}"
                       class="inline-flex h-11 px-5 rounded-xl bg-ink text-volt font-bold items-center">{{ __('Find a court') }}</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>