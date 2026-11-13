<x-app-layout>
    @php
        $daftarHarga = $grid->mapWithKeys(fn ($court) => [$court['id'] => $court['harga']]);
        $daftarNama = $grid->mapWithKeys(fn ($court) => [$court['id'] => $court['nama']]);
    @endphp

    <div x-data="pilihJadwal({
            court: @js($courtDipilih),
            dipilih: @js($jamDipilih),
            harga: @js($daftarHarga),
            nama: @js($daftarNama),
            teks: {
                kosong: @js(__('No hours selected')),
                satuJam: @js(__('1 hour')),
                banyakJam: @js(__(':count hours')),
            },
         })"
         class="pb-36 sm:pb-28">

        {{-- Header venue + pilihan tanggal --}}
        <section class="bg-court text-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-4 pb-5 space-y-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('lapangan.index') }}" aria-label="{{ __('Back') }}"
                       class="h-11 w-11 shrink-0 rounded-full bg-court-deep flex items-center justify-center hover:brightness-110">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m15 18-6-6 6-6"></path>
                        </svg>
                    </a>

                    @if ($arena->fotoUrl())
                        <img src="{{ $arena->fotoUrl() }}" alt="{{ $arena->nama }}" class="h-12 w-12 shrink-0 rounded-xl object-cover">
                    @endif

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-extrabold">{{ $arena->nama }}</h1>
                            <span class="px-2 py-0.5 rounded-full bg-volt text-ink text-[11px] font-extrabold">{{ $arena->jenis->nama }}</span>
                        </div>
                        <p class="text-xs text-court-soft truncate">{{ $arena->alamat }}</p>
                    </div>
                </div>

                <nav aria-label="{{ __('Date') }}" class="grid grid-cols-7 gap-1.5 max-w-xl">
                    @foreach ($daftarHari as $hari)
                        <a href="{{ route('lapangan.show', ['arena' => $arena, 'tanggal' => $hari['tanggal']]) }}"
                           @if ($hari['aktif']) aria-current="date" @endif
                           @class([
                               'h-14 rounded-2xl flex flex-col items-center justify-center',
                               'bg-volt text-ink' => $hari['aktif'],
                               'border border-white/30 hover:bg-court-deep' => ! $hari['aktif'],
                           ])>
                            <span class="text-[11px] font-semibold">{{ $hari['nama'] }}</span>
                            <span class="text-base font-extrabold">{{ $hari['angka'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </section>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-5 space-y-4">
            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-red-50 text-red-800 text-sm font-semibold">{{ $errors->first() }}</div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-extrabold">{{ __('Pick a court and hours') }}</h2>
                <div class="flex gap-3 text-xs text-muted">
                    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-white border border-gray-400"></span>{{ __('Available') }}</span>
                    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-ink"></span>{{ __('Selected') }}</span>
                    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-gray-200"></span>{{ __('Booked') }}</span>
                </div>
            </div>

            @if ($grid->isEmpty())
                <div class="bg-white border border-line rounded-3xl p-8 text-center font-semibold">
                    {{ __('This venue has no active courts yet.') }}
                </div>
            @else
                {{-- Grid court × jam. Kolom nama court tetap di kiri (sticky), jam digeser ke samping --}}
                <div class="bg-white border border-line rounded-3xl overflow-x-auto">
                    <div class="inline-block min-w-full p-3">
                        <div class="flex gap-1.5 mb-2">
                            <div class="sticky left-0 z-10 w-28 shrink-0 bg-white"></div>
                            @foreach ($daftarJam as $jam)
                                <span class="w-14 shrink-0 text-center text-[11px] font-bold text-muted">{{ sprintf('%02d:00', $jam) }}</span>
                            @endforeach
                        </div>

                        <div class="space-y-1.5">
                            @foreach ($grid as $court)
                                <div class="flex gap-1.5">
                                    <div class="sticky left-0 z-10 w-28 shrink-0 bg-white pr-1">
                                        <div class="h-14 rounded-xl border px-2 flex flex-col justify-center"
                                             :class="court === {{ $court['id'] }} && dipilih.length ? 'border-ink' : 'border-line'">
                                            <span class="text-sm font-extrabold">{{ $court['nama'] }}</span>
                                            <span class="text-[11px] text-muted">Rp {{ number_format($court['harga'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    @foreach ($court['slot'] as $slot)
                                        @php $label = $court['nama'] . ' ' . sprintf('%02d:00', $slot['jam']); @endphp

                                        @if ($slot['status'] === 'tersedia')
                                            <button type="button"
                                                    @click="pilih({{ $court['id'] }}, {{ $slot['jam'] }})"
                                                    :aria-pressed="terpilih({{ $court['id'] }}, {{ $slot['jam'] }}).toString()"
                                                    aria-label="{{ $label }}"
                                                    :class="terpilih({{ $court['id'] }}, {{ $slot['jam'] }})
                                                        ? 'bg-ink border-ink text-volt'
                                                        : 'bg-white border-gray-300 hover:border-ink'"
                                                    class="h-14 w-14 shrink-0 rounded-xl border text-sm font-extrabold">
                                                <span x-show="terpilih({{ $court['id'] }}, {{ $slot['jam'] }})">✓</span>
                                            </button>
                                        @else
                                            <span aria-label="{{ $label }} · {{ $slot['status'] === 'penuh' ? __('Booked') : __('Passed') }}"
                                                  @class([
                                                      'h-14 w-14 shrink-0 rounded-xl flex items-center justify-center text-sm font-bold',
                                                      'bg-gray-200 text-gray-500' => $slot['status'] === 'penuh',
                                                      'bg-ground text-gray-300' => $slot['status'] === 'lewat',
                                                  ])>–</span>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <p class="text-xs text-muted">
                    {{ __('Scroll sideways for more hours.') }}
                    {{ __('One booking is for one court.') }}
                    {{ __('Picking hours on another court will clear your current selection.') }}
                </p>
            @endif
        </div>

        {{-- Bar bawah: ringkasan + kirim --}}
        <form method="POST" action="{{ route('booking.store') }}"
              class="fixed inset-x-3 bottom-24 sm:bottom-4 z-20 max-w-3xl mx-auto">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
            <input type="hidden" name="lapangan_id" :value="court">
            <template x-for="jam in dipilih" :key="jam">
                <input type="hidden" name="jam[]" :value="jam">
            </template>

            <div class="rounded-3xl bg-ink text-white pl-5 pr-3 py-3 flex items-center justify-between gap-3 shadow-xl">
                <div class="min-w-0">
                    <p class="text-xs text-gray-300 truncate" x-text="ringkasan()"></p>
                    <p class="text-xl font-extrabold" x-text="rupiah(total())"></p>
                </div>
                <button type="submit" :disabled="dipilih.length === 0"
                        class="h-12 px-4 shrink-0 rounded-2xl bg-volt text-ink text-sm font-extrabold inline-flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
                        <rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V8a4 4 0 0 1 8 0v3"></path>
                    </svg>
                    {{ __('Lock it in') }}
                </button>
            </div>
        </form>
    </div>

    <script>
        function pilihJadwal(awal) {
            return {
                ...awal,

                terpilih(court, jam) {
                    return this.court === court && this.dipilih.includes(jam);
                },

                // Satu booking = satu court. Pilih jam di court lain → pilihan sebelumnya diganti
                pilih(court, jam) {
                    if (this.court !== court) {
                        this.court = court;
                        this.dipilih = [jam];
                        return;
                    }

                    this.dipilih = this.dipilih.includes(jam)
                        ? this.dipilih.filter((j) => j !== jam)
                        : [...this.dipilih, jam].sort((a, b) => a - b);
                },

                total() {
                    return this.court ? this.dipilih.length * this.harga[this.court] : 0;
                },

                // [10, 11, 19] → "10:00–12:00, 19:00–20:00"
                rentang() {
                    const jamTeks = (j) => String(j).padStart(2, '0') + ':00';
                    const hasil = [];
                    let mulai = null;
                    let akhir = null;

                    for (const jam of this.dipilih) {
                        if (mulai !== null && jam === akhir + 1) {
                            akhir = jam;
                            continue;
                        }
                        if (mulai !== null) hasil.push(jamTeks(mulai) + '–' + jamTeks(akhir + 1));
                        mulai = akhir = jam;
                    }
                    if (mulai !== null) hasil.push(jamTeks(mulai) + '–' + jamTeks(akhir + 1));

                    return hasil.join(', ');
                },

                ringkasan() {
                    if (this.dipilih.length === 0) return this.teks.kosong;

                    const jumlah = this.dipilih.length === 1
                        ? this.teks.satuJam
                        : this.teks.banyakJam.replace(':count', this.dipilih.length);

                    return this.nama[this.court] + ' · ' + jumlah + ' · ' + this.rentang();
                },

                rupiah(angka) {
                    return 'Rp ' + angka.toLocaleString('id-ID');
                },
            };
        }
    </script>
</x-app-layout>