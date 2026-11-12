<x-app-layout>
    @php
        $totalDetik = \App\Models\Booking::BATAS_BAYAR_MENIT * 60;

        // Label dan keterangan metode bayar (diterjemahkan di sini)
        $metode = [
            'qris' => [__('QRIS'), __('Scan with any banking or e-wallet app')],
            'transfer' => [__('Bank transfer'), __('Virtual Account: BCA, BNI, Mandiri')],
            'ewallet' => [__('E-wallet'), __('GoPay, OVO, DANA')],
        ];
    @endphp

    <div class="max-w-xl mx-auto px-4 sm:px-6 py-6 space-y-4"
         x-data="{
             sisa: {{ $sisaDetik }},
             total: {{ $totalDetik }},
             init() { setInterval(() => { if (this.sisa > 0) this.sisa-- }, 1000) },
             get waktu() {
                 return String(Math.floor(this.sisa / 60)).padStart(2, '0') + ':' + String(this.sisa % 60).padStart(2, '0')
             },
             get offset() { return (439.8 * (1 - this.sisa / this.total)).toFixed(1) }
         }">

        {{-- Judul --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('booking.index') }}" aria-label="{{ __('Back') }}"
               class="h-11 w-11 shrink-0 rounded-full bg-white border border-line flex items-center justify-center hover:border-ink">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <h1 class="text-xl font-extrabold">{{ __('Payment') }}</h1>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-red-50 text-red-800 text-sm">{{ $errors->first() }}</div>
        @endif

        {{-- Kartu timer --}}
        <div class="rounded-3xl bg-ink text-white p-5 flex items-center gap-5">
            <svg class="h-28 w-28 shrink-0" viewBox="0 0 160 160" role="img" aria-label="{{ __('Time left to pay') }}">
                <circle cx="80" cy="80" r="70" fill="none" stroke="#2A3A30" stroke-width="12"/>
                <circle cx="80" cy="80" r="70" fill="none" stroke="#D4F45A" stroke-width="12" stroke-linecap="round"
                        stroke-dasharray="439.8" :stroke-dashoffset="offset" transform="rotate(-90 80 80)"/>
                <text x="80" y="90" text-anchor="middle" font-size="30" font-weight="800" fill="#FFFFFF" x-text="waktu">--:--</text>
            </svg>
            <div class="space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-volt text-ink text-xs font-extrabold">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                    {{ __('Slot locked') }}
                </span>
                <p class="font-bold leading-snug">{{ __('Your hours are held just for you.') }}</p>
                <p class="text-sm text-white/70" x-show="sisa > 0">
                    {{ __('Pay before :time', ['time' => $booking->batas_bayar->format('H:i')]) }}
                </p>
                <p class="text-sm font-semibold text-red-300" x-show="sisa === 0" x-cloak>
                    {{ __('Time is up. Your slot will be released.') }}
                </p>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="bg-white rounded-2xl border border-line p-5 space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <span class="text-muted">{{ __('Court') }}</span>
                <span class="font-semibold text-right">{{ $booking->lapangan->nama }}</span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-muted">{{ __('Schedule') }}</span>
                <span class="font-semibold text-right">
                    {{ $booking->tanggal->translatedFormat('D, d M') }}
                    · {{ $booking->rentangJam() }}
                </span>
            </div>
            <div class="flex justify-between gap-4 pt-3 border-t border-line text-base font-extrabold">
                <span>{{ __('Total') }}</span>
                <span>Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Metode bayar + tombol bayar --}}
        <form method="POST" action="{{ route('booking.prosesBayar', $booking) }}" class="space-y-4">
            @csrf
            <fieldset class="space-y-2">
                <legend class="text-base font-bold mb-2">{{ __('Payment method') }}</legend>
                @foreach ($metode as $kode => [$nama, $keterangan])
                    <label class="flex items-center gap-3 p-4 rounded-2xl bg-white border border-line cursor-pointer
                                  has-[:checked]:border-2 has-[:checked]:border-court has-[:checked]:bg-court-soft">
                        <input type="radio" name="metode_bayar" value="{{ $kode }}" @checked(old('metode_bayar', 'qris') === $kode)
                               class="text-court focus:ring-court">
                        <span>
                            <span class="block font-bold">{{ $nama }}</span>
                            <span class="block text-xs text-muted">{{ $keterangan }}</span>
                        </span>
                    </label>
                @endforeach
            </fieldset>

            <p class="text-xs text-muted">{{ __('Simulated payment for this demo. No real money is charged.') }}</p>

            <button type="submit" :disabled="sisa === 0"
                    class="w-full h-14 rounded-2xl bg-court text-white text-base font-extrabold hover:bg-court-deep disabled:opacity-40 disabled:cursor-not-allowed">
                {{ __('Pay :amount', ['amount' => 'Rp ' . number_format($booking->total_harga, 0, ',', '.')]) }}
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