<x-app-layout>
    @php
        // Warna kotak dan ikon olahraga, ditentukan dari nama jenis lapangan
        $gaya = function (string $nama) {
            $nama = strtolower($nama);

            return match (true) {
                str_contains($nama, 'futsal'), str_contains($nama, 'soccer'), str_contains($nama, 'bola') => ['bg-court', 'text-volt', 'bola'],
                str_contains($nama, 'badminton') => ['bg-blue-800', 'text-white', 'kok'],
                str_contains($nama, 'basket') => ['bg-orange-700', 'text-white', 'basket'],
                default => ['bg-ink', 'text-volt', 'lapangan'],
            };
        };

        $ikon = [
            'bola' => '<circle cx="12" cy="12" r="9"/><path d="m12 7 4 3-1.5 4.5h-5L8 10z"/>',
            'kok' => '<path d="M5 19 15 9"/><path d="M14 4a6 6 0 0 1 6 6c0 2-2 3-4 3l-5-5c0-2 1-4 3-4z"/>',
            'basket' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3v18M5.5 5.5c3 3 3 10 0 13M18.5 5.5c-3 3-3 10 0 13"/>',
            'lapangan' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M12 5v14"/><circle cx="12" cy="12" r="2.5"/>',
        ];

        $jenisAktif = $filter['jenis'] ?? null;
        $cari = $filter['cari'] ?? '';
        $namaDepan = \Illuminate\Support\Str::before(Auth::user()->name . ' ', ' ');
    @endphp

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 space-y-5">

        {{-- Sapaan --}}
        <div>
            <p class="text-sm text-muted">{{ __('Hi, :name', ['name' => $namaDepan]) }}</p>
            <h1 class="text-2xl font-extrabold tracking-tight">{{ __("Let's play") }}</h1>
        </div>

        {{-- Pencarian --}}
        <form method="GET" action="{{ route('lapangan.index') }}" role="search">
            @if ($jenisAktif)
                <input type="hidden" name="jenis" value="{{ $jenisAktif }}">
            @endif
            <label for="cari" class="flex items-center gap-3 h-12 px-4 rounded-2xl bg-white border border-line focus-within:border-court">
                <svg class="h-5 w-5 text-muted shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <span class="sr-only">{{ __('Search courts') }}</span>
                <input id="cari" type="search" name="cari" value="{{ $cari }}" placeholder="{{ __('Search courts') }}"
                       class="flex-1 border-0 p-0 bg-transparent text-[15px] placeholder:text-muted focus:ring-0">
            </label>
        </form>

        {{-- Chip filter jenis --}}
        <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 sm:mx-0 sm:px-0" aria-label="{{ __('Filter by sport') }}">
            <a href="{{ route('lapangan.index', array_filter(['cari' => $cari])) }}"
               @if (! $jenisAktif) aria-current="true" @endif
               @class([
                   'shrink-0 h-10 px-4 rounded-full text-sm inline-flex items-center',
                   'bg-ink text-volt font-bold' => ! $jenisAktif,
                   'bg-white border border-line font-semibold hover:border-ink' => $jenisAktif,
               ])>
                {{ __('All') }}
            </a>
            @foreach ($daftarJenis as $jenis)
                <a href="{{ route('lapangan.index', array_filter(['jenis' => $jenis->id, 'cari' => $cari])) }}"
                   @if ($jenisAktif == $jenis->id) aria-current="true" @endif
                   @class([
                       'shrink-0 h-10 px-4 rounded-full text-sm inline-flex items-center',
                       'bg-ink text-volt font-bold' => $jenisAktif == $jenis->id,
                       'bg-white border border-line font-semibold hover:border-ink' => $jenisAktif != $jenis->id,
                   ])>
                    {{ $jenis->nama }}
                </a>
            @endforeach
        </div>

        {{-- Daftar lapangan --}}
        <div class="flex items-baseline justify-between gap-3">
            <h2 class="text-base font-bold">{{ __('Courts') }}</h2>
            <p class="text-sm text-muted">{{ __('Book now') }}</p>
        </div>

        <div class="space-y-3">
            @forelse ($daftarLapangan as $lapangan)
                @php
                    [$latar, $warnaIkon, $kunciIkon] = $gaya($lapangan->jenis->nama);
                    $kosong = $jamKosong[$lapangan->id] ?? [];
                @endphp

                <article class="bg-white rounded-2xl border border-line p-4 space-y-3">
                    <a href="{{ route('lapangan.show', $lapangan) }}" class="flex gap-3 group">
                        <div class="h-16 w-16 shrink-0 rounded-2xl {{ $latar }} flex items-center justify-center">
                            <svg class="h-8 w-8 {{ $warnaIkon }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikon[$kunciIkon] !!}</svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold group-hover:text-court">{{ $lapangan->nama }}</h3>
                            <p class="text-sm text-muted truncate">
                                {{ $lapangan->jenis->nama }}@if ($lapangan->deskripsi) · {{ $lapangan->deskripsi }}@endif
                            </p>
                            <p class="mt-1 font-extrabold">
                                Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}
                                <span class="text-xs font-medium text-muted">/ {{ __('hour') }}</span>
                            </p>
                        </div>
                    </a>

                    <div class="flex flex-wrap items-center gap-2">
                        @if (count($kosong) > 0)
                            <span class="text-xs font-semibold text-muted">{{ __('Available today') }}</span>
                            @foreach ($kosong as $jam)
                                <a href="{{ route('lapangan.show', $lapangan) }}"
                                   class="h-10 px-3 rounded-xl bg-volt-soft text-volt-ink text-sm font-bold inline-flex items-center hover:bg-volt">
                                    {{ sprintf('%02d:00', $jam) }}
                                </a>
                            @endforeach
                        @else
                            <span class="text-xs font-semibold text-muted">{{ __('Unavailable today') }}</span>
                            <a href="{{ route('lapangan.show', ['lapangan' => $lapangan, 'tanggal' => today()->addDay()->toDateString()]) }}"
                               class="text-sm font-bold text-court hover:underline">
                                {{ __('See other dates') }}
                            </a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-2xl border border-line p-8 text-center space-y-4">
                    <p class="font-semibold">{{ __('No courts match your search.') }}</p>
                    <a href="{{ route('lapangan.index') }}"
                       class="inline-flex h-11 px-5 rounded-xl bg-ink text-volt font-bold items-center">
                        {{ __('Clear filters') }}
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>