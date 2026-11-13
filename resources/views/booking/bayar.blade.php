<x-app-layout>
    @php
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $daftarMetode = [
            'qris' => ['QRIS', __('Scan with any banking or e-wallet app')],
            'transfer' => [__('Bank transfer'), __('Virtual Account: BCA, BNI, Mandiri')],
            'ewallet' => [__('E-wallet'), __('GoPay, OVO, DANA')],
        ];
        $sisaDetik = max(0, now()->diffInSeconds($booking->batas_bayar, false));
    @endphp

    <div class="max-w-xl mx-auto px-4 sm:px-6 py-6 space-y-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('lapangan.show', ['arena' => $booking->arena_id, 'tanggal' => $booking->tanggal->toDateString()]) }}"
               aria-label="{{ __('Back') }}"
               class="h-11 w-11 shrink-0 rounded-full bg-white border border-line flex items-center justify-center hover:border-ink">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"></path>
                </svg>
            </a>
            <h1 class="text-xl font-extrabold">{{ __('Payment') }}</h1>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-red-50 text-red-800 text-sm font-semibold">{{ $errors->first() }}</div>
        @endif

        {{-- Timer --}}
        <section x-data="{
                    sisa: {{ (int) $sisaDetik }},
                    total: {{ \App\Models\Booking::BATAS_BAYAR_MENIT * 60 }},
                    get jam() {
                        const m = String(Math.floor(this.sisa / 60)).padStart(2, '0');
                        const d = String(this.sisa % 60).padStart(2, '0');
                        return m + ':' + d;
                    },
                    get offset() {
                        return (439.8 * (1 - this.sisa / this.total)).toFixed(1);
                    },
                 }"
                 x-init="const t = setInterval(() => { sisa > 0 ? sisa-- : clearInterval(t) }, 1000)"
                 class="rounded-3xl bg-ink text-white p-5 flex items-center gap-5">
            <svg class="w-24 h-24 shrink-0" viewBox="0 0 160 160" role="img" aria-label="{{ __('Time left to pay') }}">
                <circle cx="80" cy="80" r="70" fill="none" stroke="#2A3A30" stroke-width="12"></circle>
                <circle cx="80" cy="80" r="70" fill="none" stroke="#D4F45A" stroke-width="12" stroke-linecap="round"
                        stroke-dasharray="439.8" :stroke-dashoffset="offset" transform="rotate(-90 80 80)"></circle>
                <text x="80" y="90" text-anchor="middle" font-size="30" font-weight="800" fill="#FFFFFF" x-text="jam"></text>
            </svg>

            <div class="space-y-1.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-volt text-ink text-xs font-extrabold">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true">
                        <rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V8a4 4 0 0 1 8 0v3"></path>
                    </svg>
                    {{ __('Slot locked') }}
                </span>
                <p class="font-bold leading-snug">{{ __('Your hours are held just for you.') }}</p>
                <p class="text-sm text-gray-300" x-show="sisa > 0">{{ __('Pay before :time', ['time' => $booking->batas_bayar->format('H:i')]) }}</p>
                <p class="text-sm text-volt" x-show="sisa === 0">{{ __('Time is up. Your slot will be released.') }}</p>
            </div>
        </section>

        {{-- Ringkasan --}}
        <section class="bg-white border border-line rounded-3xl p-5 space-y-3 text-sm">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-base font-extrabold">{{ $booking->arena->nama }}</h2>
                <span class="px-2 py-0.5 rounded-full bg-ink text-volt text-[11px] font-extrabold">{{ $booking->arena->jenis->nama }}</span>
            </div>

            <div class="flex justify-between gap-4">
                <span class="text-muted">{{ __('Date') }}</span>
                <span class="font-semibold text-right">{{ $booking->tanggal->translatedFormat('D, j M Y') }}</span>
            </div>

            <div class="flex justify-between gap-4">
                <span class="text-muted">{{ __('Schedule') }}</span>
                <span class="font-semibold text-right space-y-0.5">
                    @foreach ($booking->rincianJadwal() as $baris)
                        <span class="block">{{ $baris }}</span>
                    @endforeach
                </span>
            </div>

            <div class="h-px bg-line"></div>

            <div class="flex justify-between text-base font-extrabold">
                <span>{{ __('Total') }}</span>
                <span>{{ $rupiah($booking->total_harga) }}</span>
            </div>
        </section>

        {{-- Metode pembayaran --}}
        <form method="POST" action="{{ route('booking.prosesBayar', $booking) }}" class="space-y-4">
            @csrf

            <fieldset class="space-y-2">
                <legend class="font-bold mb-2">{{ __('Payment method') }}</legend>
                @foreach ($daftarMetode as $kode => $metode)
                    <label class="flex items-center gap-3 p-4 rounded-2xl bg-white border border-line cursor-pointer has-[:checked]:border-court has-[:checked]:bg-court-soft">
                        <input type="radio" name="metode_bayar" value="{{ $kode }}" class="text-court focus:ring-court"
                               @checked(old('metode_bayar', 'qris') === $kode)>
                        <span>
                            <span class="block text-sm font-bold">{{ $metode[0] }}</span>
                            <span class="block text-xs text-muted">{{ $metode[1] }}</span>
                        </span>
                    </label>
                @endforeach
            </fieldset>

            <p class="text-xs text-muted">{{ __('Simulated payment for this demo. No real money is charged.') }}</p>

            <button type="submit" class="w-full h-14 rounded-2xl bg-court text-white text-base font-extrabold hover:bg-court-deep">
                {{ __('Pay :amount', ['amount' => $rupiah($booking->total_harga)]) }}
            </button>
        </form>

        <form method="POST" action="{{ route('booking.batal', $booking) }}" class="text-center"
              onsubmit="return confirm(@js(__('Cancel this booking?')))">
            @csrf
            @method('PATCH')
            <button type="submit" class="text-sm font-semibold text-red-700 hover:underline">{{ __('Cancel booking') }}</button>
        </form>
    </div>
</x-app-layout>