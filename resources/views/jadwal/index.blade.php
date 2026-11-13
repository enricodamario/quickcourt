<x-app-layout>
    @php
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $warnaCover = ['#15803D', '#0E3B22', '#126B33', '#1F4FA3', '#B5521B'];
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 space-y-5">
        {{-- Sapaan + pencarian --}}
        <div>
            <p class="text-sm text-muted">{{ __('Hi, :name', ['name' => Str::before(auth()->user()->name, ' ')]) }}</p>
            <h1 class="text-2xl font-extrabold tracking-tight">{{ __("Let's play") }}</h1>
        </div>

        <form method="GET" action="{{ route('lapangan.index') }}">
            @if ($jenisDipilih)
                <input type="hidden" name="jenis" value="{{ $jenisDipilih }}">
            @endif
            <label class="flex items-center gap-3 h-12 px-4 rounded-2xl bg-white border border-line focus-within:border-court">
                <svg class="w-5 h-5 text-muted shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path>
                </svg>
                <span class="sr-only">{{ __('Search venues') }}</span>
                <input type="search" name="cari" value="{{ $cari }}" placeholder="{{ __('Search venues') }}"
                       class="flex-1 border-0 p-0 bg-transparent focus:ring-0">
            </label>
        </form>

        {{-- Chip jenis olahraga --}}
        <nav aria-label="{{ __('Filter by sport') }}" class="flex gap-2 overflow-x-auto pb-1">
            <a href="{{ route('lapangan.index', array_filter(['cari' => $cari])) }}"
               @class([
                   'h-9 px-4 rounded-full text-sm whitespace-nowrap inline-flex items-center',
                   'bg-ink text-volt font-extrabold' => ! $jenisDipilih,
                   'bg-white border border-line font-semibold hover:border-ink' => $jenisDipilih,
               ])>{{ __('All') }}</a>
            @foreach ($daftarJenis as $jenis)
                <a href="{{ route('lapangan.index', array_filter(['jenis' => $jenis->id, 'cari' => $cari])) }}"
                   @class([
                       'h-9 px-4 rounded-full text-sm whitespace-nowrap inline-flex items-center',
                       'bg-ink text-volt font-extrabold' => $jenisDipilih === $jenis->id,
                       'bg-white border border-line font-semibold hover:border-ink' => $jenisDipilih !== $jenis->id,
                   ])>{{ $jenis->nama }}</a>
            @endforeach
        </nav>

        {{-- Kartu venue --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($daftarArena as $arena)
                @php
                    $jumlahCourt = $arena->lapangan->count();
                    $hargaMulai = $arena->lapangan->min('harga_per_jam');
                    $warna = $warnaCover[$arena->id % count($warnaCover)];
                    $jamHariIni = $jamKosong[$arena->id] ?? [];
                @endphp

                <article class="bg-white border border-line rounded-3xl overflow-hidden flex flex-col">
                    <a href="{{ route('lapangan.show', $arena) }}" class="block">
                        <div class="relative h-32 bg-court"
                             @unless ($arena->fotoUrl())
                                 style="background-color: {{ $warna }}; background-image: repeating-linear-gradient(90deg, rgba(255,255,255,.10) 0 2px, transparent 2px 64px);"
                             @endunless>
                            @if ($arena->fotoUrl())
                                <img src="{{ $arena->fotoUrl() }}" alt="{{ $arena->nama }}" loading="lazy"
                                     class="absolute inset-0 h-full w-full object-cover">
                            @endif
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-ink text-volt text-[11px] font-extrabold">
                                {{ $arena->jenis->nama }}
                            </span>
                            <span class="absolute top-3 right-3 px-3 py-1 rounded-full bg-white text-ink text-[11px] font-bold">
                                {{ $jumlahCourt === 1 ? __('1 court') : __(':count courts', ['count' => $jumlahCourt]) }}
                            </span>
                        </div>

                        <div class="px-4 pt-3 pb-1 space-y-1">
                            <h2 class="text-base font-extrabold">{{ $arena->nama }}</h2>
                            <p class="flex items-start gap-1 text-xs text-muted">
                                <svg class="w-3.5 h-3.5 shrink-0 mt-px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                                    <path d="M12 21s-7-6.5-7-12a7 7 0 0 1 14 0c0 5.5-7 12-7 12z"></path><circle cx="12" cy="9" r="2.5"></circle>
                                </svg>
                                {{ $arena->alamat }}
                            </p>
                            <p class="text-sm font-extrabold">
                                {{ __('from :price', ['price' => $rupiah($hargaMulai)]) }}<span class="text-xs font-medium text-muted"> / {{ __('hour') }}</span>
                            </p>
                        </div>
                    </a>

                    <div class="mt-auto px-4 pt-2 pb-4 flex flex-wrap items-center gap-2">
                        @if (count($jamHariIni) > 0)
                            <span class="text-xs font-semibold text-muted">{{ __('Available today') }}</span>
                            @foreach ($jamHariIni as $slot)
                                <a href="{{ route('lapangan.show', ['arena' => $arena, 'tanggal' => today()->toDateString(), 'lapangan' => $slot['lapangan_id'], 'jam' => $slot['jam']]) }}"
                                   class="h-8 px-3 rounded-lg bg-volt-soft text-volt-ink text-sm font-bold inline-flex items-center hover:brightness-95">
                                    {{ sprintf('%02d:00', $slot['jam']) }}
                                </a>
                            @endforeach
                        @else
                            <span class="text-xs font-semibold text-muted">{{ __('Unavailable today') }} ·</span>
                            <a href="{{ route('lapangan.show', ['arena' => $arena, 'tanggal' => today()->addDay()->toDateString()]) }}"
                               class="text-xs font-bold text-court hover:underline">{{ __('See other dates') }}</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 bg-white border border-line rounded-3xl p-8 text-center space-y-4">
                    <p class="font-semibold">{{ __('No venues match your search.') }}</p>
                    <a href="{{ route('lapangan.index') }}"
                       class="inline-flex h-11 px-5 rounded-xl bg-ink text-volt font-bold items-center">{{ __('Clear filters') }}</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>