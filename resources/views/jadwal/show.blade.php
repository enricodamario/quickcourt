<x-app-layout>
    @php
        $daftarHarga = $grid->mapWithKeys(fn ($court) => [$court['id'] => $court['harga']]);
        $pilihanAwal = $courtDipilih && $jamDipilih ? [$courtDipilih => $jamDipilih] : new \stdClass();
    @endphp

    <div x-data="pilihJadwal({
            pilihan: @js($pilihanAwal),
            harga: @js($daftarHarga),
            teks: {
                kosong: @js(__('No hours selected')),
                satuJam: @js(__('1 hour')),
                banyakJam: @js(__(':count hours')),
                satuCourt: @js(__('1 court')),
                banyakCourt: @js(__(':count courts')),
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
                                             :class="jumlahJamDi({{ $court['id'] }}) ? 'border-ink' : 'border-line'">
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
                    {{ __('Tap any available hours.') }}
                    {{ __('Scroll sideways for more hours.') }}
                </p>
            @endif
        </div>

        {{-- Bar bawah: ringkasan + kirim --}}
        <form method="POST" action="{{ route('booking.store') }}"
              class="fixed inset-x-3 bottom-24 sm:bottom-4 z-20 max-w-3xl mx-auto">
            @csrf
            <input type="hidden" name="arena_id" value="{{ $arena->id }}">
            <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
            <template x-for="slot in daftarSlot()" :key="slot.kunci">
                <input type="hidden" :name="'pilihan[' + slot.court + '][]'" :value="slot.jam">
            </template>

            <div class="rounded-3xl bg-ink text-white pl-5 pr-3 py-3 flex items-center justify-between gap-3 shadow-xl">
                <div class="min-w-0">
                    <p class="text-xs text-gray-300 truncate" x-text="ringkasan()"></p>
                    <p class="text-xl font-extrabold" x-text="rupiah(total())"></p>
                </div>
                <button type="submit" :disabled="jumlahJam() === 0"
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

                // pilihan = { "court_id": [jam, jam, ...] }
                terpilih(court, jam) {
                    return (this.pilihan[court] || []).includes(jam);
                },

                // Klik jam yang tersedia: centang atau batal centang. Court lain tidak terpengaruh
                pilih(court, jam) {
                    const daftar = this.pilihan[court] || [];
                    const baru = daftar.includes(jam)
                        ? daftar.filter((j) => j !== jam)
                        : [...daftar, jam].sort((a, b) => a - b);

                    const pilihan = { ...this.pilihan };
                    if (baru.length) {
                        pilihan[court] = baru;
                    } else {
                        delete pilihan[court];
                    }
                    this.pilihan = pilihan;
                },

                jumlahJamDi(court) {
                    return (this.pilihan[court] || []).length;
                },

                jumlahJam() {
                    return Object.values(this.pilihan).reduce((jumlah, daftar) => jumlah + daftar.length, 0);
                },

                jumlahCourt() {
                    return Object.keys(this.pilihan).length;
                },

                total() {
                    return Object.entries(this.pilihan)
                        .reduce((jumlah, [court, daftar]) => jumlah + daftar.length * this.harga[court], 0);
                },

                // Untuk input tersembunyi: pilihan[court_id][] = jam
                daftarSlot() {
                    return Object.entries(this.pilihan).flatMap(([court, daftar]) =>
                        daftar.map((jam) => ({ court, jam, kunci: court + '-' + jam }))
                    );
                },

                ringkasan() {
                    const jam = this.jumlahJam();
                    if (jam === 0) return this.teks.kosong;

                    const court = this.jumlahCourt();
                    const teksJam = jam === 1 ? this.teks.satuJam : this.teks.banyakJam.replace(':count', jam);
                    const teksCourt = court === 1 ? this.teks.satuCourt : this.teks.banyakCourt.replace(':count', court);

                    return teksJam + ' · ' + teksCourt;
                },

                rupiah(angka) {
                    return 'Rp ' + angka.toLocaleString('id-ID');
                },
            };
        }
    </script>
</x-app-layout>