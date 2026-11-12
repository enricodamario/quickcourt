<x-app-layout>
    @php
        $namaMetode = [
            'qris' => __('QRIS'),
            'transfer' => __('Bank transfer'),
            'ewallet' => __('E-wallet'),
        ];
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
    @endphp

    <div class="max-w-xl mx-auto px-4 sm:px-6 py-6 space-y-4">
        <h1 class="text-2xl font-extrabold tracking-tight">{{ __('My bookings') }}</h1>

        {{-- Tab Upcoming / Past --}}
        <div class="flex gap-1 p-1 rounded-2xl bg-gray-200/70">
            @foreach (['upcoming' => __('Upcoming'), 'past' => __('Past')] as $kunci => $label)
                <a href="{{ route('booking.index', ['tab' => $kunci]) }}"
                   @if ($tab === $kunci) aria-current="page" @endif
                   @class([
                       'flex-1 h-10 rounded-xl text-sm inline-flex items-center justify-center',
                       'bg-white font-bold text-ink shadow-sm' => $tab === $kunci,
                       'font-semibold text-muted hover:text-ink' => $tab !== $kunci,
                   ])>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-court-soft text-court-dark text-sm font-semibold">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-red-50 text-red-800 text-sm font-semibold">{{ session('error') }}</div>
        @endif

        @forelse ($bookings as $booking)
            @php
                $jam = $booking->rentangJam();
                $kode = 'QC-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
            @endphp

            @if ($booking->status === 'dikonfirmasi' && $tab === 'upcoming')
                {{-- Tiket: booking lunas yang akan datang --}}
                <article class="bg-white rounded-3xl border border-line overflow-hidden">
                    <div class="bg-court text-white px-5 py-4 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-court-soft">{{ $booking->lapangan->jenis->nama }}</p>
                            <h2 class="text-lg font-extrabold truncate">{{ $booking->lapangan->nama }}</h2>
                        </div>
                        <x-status-booking :status="$booking->status" />
                    </div>

                    <div class="px-5 py-4 grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-muted">{{ __('Date') }}</p>
                            <p class="font-bold">{{ $booking->tanggal->translatedFormat('D, d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted">{{ __('Time') }}</p>
                            <p class="font-bold">{{ $jam }}</p>
                        </div>
                    </div>

                    {{-- Garis sobekan tiket --}}
                    <div class="flex items-center" aria-hidden="true">
                        <span class="h-6 w-3 rounded-r-full bg-ground -ml-px"></span>
                        <span class="flex-1 mx-2 border-t-2 border-dashed border-line"></span>
                        <span class="h-6 w-3 rounded-l-full bg-ground -mr-px"></span>
                    </div>

                    <div class="px-5 py-4 flex items-end justify-between gap-3">
                        <div>
                            <p class="text-xs text-muted">{{ __('Booking code') }}</p>
                            <p class="text-base font-extrabold tracking-wider">{{ $kode }}</p>
                        </div>
                        <div class="text-right">
                            @if ($booking->metode_bayar)
                                <p class="text-xs text-muted">
                                    {{ __('Paid via :method', ['method' => $namaMetode[$booking->metode_bayar] ?? $booking->metode_bayar]) }}
                                </p>
                            @endif
                            <p class="text-base font-extrabold">{{ $rupiah($booking->total_harga) }}</p>
                        </div>
                    </div>
                </article>

            @elseif ($booking->status === 'menunggu_pembayaran')
                {{-- Belum dibayar: hitung mundur + tombol bayar --}}
                <article class="bg-white rounded-3xl border border-line p-5 space-y-3"
                         x-data="{
                             sisa: {{ max(0, (int) now()->diffInSeconds($booking->batas_bayar, false)) }},
                             teks: @js(__('Held for you · :time left')),
                             init() { setInterval(() => { if (this.sisa > 0) this.sisa-- }, 1000) },
                             get label() {
                                 const w = String(Math.floor(this.sisa / 60)).padStart(2, '0') + ':' + String(this.sisa % 60).padStart(2, '0');
                                 return this.teks.replace(':time', w);
                             }
                         }">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-bold truncate">{{ $booking->lapangan->nama }}</h2>
                            <p class="text-sm text-muted">{{ $booking->tanggal->translatedFormat('D, d M') }} · {{ $jam }}</p>
                        </div>
                        <x-status-booking :status="$booking->status" />
                    </div>

                    <div class="flex items-center gap-2 px-4 py-3 rounded-2xl bg-ink text-white text-sm font-semibold">
                        <svg class="h-4 w-4 text-volt shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                        <span x-text="label">{{ __('Held for you · :time left', ['time' => '--:--']) }}</span>
                    </div>

                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('booking.batal', $booking) }}" class="flex-1"
                              onsubmit="return confirm(@js(__('Cancel this booking?')))">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="w-full h-11 rounded-xl border border-red-200 bg-white text-red-700 text-sm font-bold hover:bg-red-50">
                                {{ __('Cancel') }}
                            </button>
                        </form>
                        <a href="{{ route('booking.bayar', $booking) }}"
                           class="flex-[2] h-11 rounded-xl bg-court text-white text-sm font-extrabold inline-flex items-center justify-center hover:bg-court-deep">
                            {{ __('Pay now · :amount', ['amount' => $rupiah($booking->total_harga)]) }}
                        </a>
                    </div>
                </article>

            @else
                {{-- Riwayat: kartu ringkas --}}
                <article class="bg-white rounded-3xl border border-line p-5 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="font-bold truncate">{{ $booking->lapangan->nama }}</h2>
                        <p class="text-sm text-muted">{{ $booking->tanggal->translatedFormat('D, d M Y') }} · {{ $jam }}</p>
                        <p @class([
                            'mt-1 text-sm font-bold',
                            'text-gray-400 line-through' => in_array($booking->status, ['dibatalkan', 'kedaluwarsa']),
                            'text-ink' => ! in_array($booking->status, ['dibatalkan', 'kedaluwarsa']),
                        ])>
                            {{ $rupiah($booking->total_harga) }}
                        </p>
                        @if ($booking->status === 'dikonfirmasi')
                            <p class="text-xs text-muted mt-1">{{ __('Booking code') }}: {{ $kode }}</p>
                        @endif
                    </div>
                    <x-status-booking :status="$booking->status" />
                </article>
            @endif
        @empty
            <div class="bg-white rounded-3xl border border-line p-8 text-center space-y-4">
                <p class="font-semibold">{{ $tab === 'upcoming' ? __('No upcoming bookings yet.') : __('No past bookings yet.') }}</p>
                <a href="{{ route('lapangan.index') }}"
                   class="inline-flex h-11 px-5 rounded-xl bg-ink text-volt font-bold items-center">
                    {{ __('Find a court') }}
                </a>
            </div>
        @endforelse
    </div>
</x-app-layout>