<x-app-layout>
    @php
        // Teks untuk ringkasan di bar bawah (dipakai oleh JavaScript, jadi diterjemahkan di sini)
        $teks = [
            'none' => __('No hours selected'),
            'one' => __('1 hour'),
            'many' => __(':count hours'),
        ];
    @endphp

    {{-- Header hijau: nama lapangan + foto + pilihan tanggal --}}
    <section class="bg-court text-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-5 pb-6 space-y-5">
            <div class="flex items-center gap-3">
                <a href="{{ route('lapangan.index') }}" aria-label="{{ __('Back') }}"
                   class="h-11 w-11 shrink-0 rounded-full bg-court-deep flex items-center justify-center hover:bg-court-dark">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-xl font-extrabold truncate">{{ $lapangan->nama }}</h1>
                    <p class="text-sm text-court-soft truncate">
                        {{ $lapangan->jenis->nama }}@if ($lapangan->deskripsi) · {{ $lapangan->deskripsi }}@endif
                        · Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }} / {{ __('hour') }}
                    </p>
                </div>
            </div>

            @if ($lapangan->fotoUrl())
                <img src="{{ $lapangan->fotoUrl() }}" alt="{{ $lapangan->nama }}"
                     class="w-full h-44 sm:h-56 rounded-2xl object-cover">
            @endif

            <div>
                <p class="text-sm font-semibold text-court-soft mb-2">{{ $tanggal->translatedFormat('l, d F Y') }}</p>
                <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                    @foreach ($daftarHari as $hari)
                        <a href="{{ route('lapangan.show', ['lapangan' => $lapangan, 'tanggal' => $hari['tanggal']]) }}"
                           @if ($hari['aktif']) aria-current="date" @endif
                           @class([
                               'h-16 rounded-2xl flex flex-col items-center justify-center',
                               'bg-volt text-ink' => $hari['aktif'],
                               'border border-white/40 text-white hover:bg-court-deep' => ! $hari['aktif'],
                           ])>
                            <span class="text-[11px] font-semibold">{{ $hari['nama'] }}</span>
                            <span class="text-lg font-extrabold">{{ $hari['angka'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Pilih jam --}}
    <form method="POST" action="{{ route('booking.store') }}"
          x-data="{
              dipilih: @js($jamDipilih),
              harga: {{ $lapangan->harga_per_jam }},
              teks: @js($teks),
              jam(n) { return String(n).padStart(2, '0') + ':00' },
              get ringkasan() {
                  if (this.dipilih.length === 0) return this.teks.none;
                  const j = this.dipilih.map(Number).sort((a, b) => a - b);
                  const durasi = j.length === 1 ? this.teks.one : this.teks.many.replace(':count', j.length);
                  return durasi + ' · ' + this.jam(j[0]) + '–' + this.jam(j[j.length - 1] + 1);
              },
              get total() { return 'Rp ' + (this.dipilih.length * this.harga).toLocaleString('id-ID') }
          }"
          class="max-w-3xl mx-auto px-4 sm:px-6 py-6 space-y-4">
        @csrf
        <input type="hidden" name="lapangan_id" value="{{ $lapangan->id }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-red-50 text-red-800 text-sm space-y-1">
                @foreach ($errors->all() as $pesan)
                    <p>{{ $pesan }}</p>
                @endforeach
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold">{{ __('Pick your hours') }}</h2>
                <p class="text-xs text-muted">{{ __('Max. 4 hours in a row') }}</p>
            </div>
            <div class="flex gap-3 text-xs text-muted">
                <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-white border border-gray-400"></span>{{ __('Available') }}</span>
                <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-ink"></span>{{ __('Selected') }}</span>
                <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-gray-200"></span>{{ __('Booked') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2.5">
            @foreach ($slots as $slot)
                @if ($slot['status'] === 'tersedia')
                    <label class="cursor-pointer">
                        <input type="checkbox" name="jam[]" value="{{ $slot['jam'] }}" x-model="dipilih" class="peer sr-only">
                        <span class="h-12 rounded-xl border border-gray-300 bg-white font-semibold flex items-center justify-center transition
                                     hover:border-ink
                                     peer-checked:bg-ink peer-checked:border-ink peer-checked:text-volt peer-checked:font-extrabold
                                     peer-focus-visible:ring-2 peer-focus-visible:ring-court peer-focus-visible:ring-offset-2">
                            {{ $slot['label'] }}
                        </span>
                    </label>
                @else
                    <div class="h-12 rounded-xl bg-gray-200 text-gray-600 flex flex-col items-center justify-center">
                        <span class="text-sm line-through">{{ $slot['label'] }}</span>
                        <span class="text-[10px] font-semibold">{{ $slot['status'] === 'penuh' ? __('Booked') : __('Passed') }}</span>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Bar bawah: ringkasan + tombol Lock it in --}}
        <div class="sticky bottom-24 md:bottom-4 z-20 rounded-3xl bg-ink text-white p-3 pl-5 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs text-white/70 truncate" x-text="ringkasan">{{ $teks['none'] }}</p>
                <p class="text-xl font-extrabold" x-text="total">Rp 0</p>
            </div>
            <button type="submit" :disabled="dipilih.length === 0"
                    class="h-12 shrink-0 px-5 rounded-2xl bg-volt text-ink font-extrabold inline-flex items-center gap-2 hover:brightness-95 disabled:opacity-40 disabled:cursor-not-allowed">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                {{ __('Lock it in') }}
            </button>
        </div>
    </form>
</x-app-layout>