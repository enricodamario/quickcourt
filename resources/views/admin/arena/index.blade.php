<x-app-layout>
    @php
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight">{{ __('Venues') }}</h1>
                <p class="mt-1 text-sm text-muted max-w-xl">
                    {{ __('Each venue holds one sport. Same place, different sport? Add it as a separate venue.') }}
                </p>
            </div>
            <a href="{{ route('admin.arena.create') }}"
               class="h-11 px-5 rounded-xl bg-ink text-volt font-extrabold inline-flex items-center hover:brightness-110">
                + {{ __('New venue') }}
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-court-soft text-court-dark text-sm font-semibold">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-red-50 text-red-800 text-sm font-semibold">{{ session('error') }}</div>
        @endif

        <div class="space-y-3">
            @forelse ($daftarArena as $arena)
                @php
                    $jumlah = $arena->lapangan_count === 1
                        ? __('1 court')
                        : __(':count courts', ['count' => $arena->lapangan_count]);

                    if ($arena->lapangan_nonaktif_count > 0) {
                        $jumlah .= ' · ' . __(':count inactive', ['count' => $arena->lapangan_nonaktif_count]);
                    }

                    $min = $arena->lapangan_min_harga_per_jam;
                    $max = $arena->lapangan_max_harga_per_jam;
                    $harga = $min === null ? '–' : ($min == $max ? $rupiah($min) : $rupiah($min) . ' – ' . $rupiah($max));
                @endphp

                <article class="bg-white border border-line rounded-2xl p-4 flex flex-wrap items-center gap-4">
                    @if ($arena->fotoUrl())
                        <img src="{{ $arena->fotoUrl() }}" alt="{{ $arena->nama }}" loading="lazy"
                             class="h-[72px] w-[72px] shrink-0 rounded-xl object-cover">
                    @else
                        <div class="h-[72px] w-[72px] shrink-0 rounded-xl bg-court flex items-center justify-center">
                            <x-application-logo class="h-8 w-auto text-volt" lines="#15803D" />
                        </div>
                    @endif

                    <div class="flex-1 min-w-[200px]">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-extrabold">{{ $arena->nama }}</h2>
                            <span class="px-2 py-0.5 rounded-full bg-ink text-volt text-[11px] font-extrabold">{{ $arena->jenis->nama }}</span>
                        </div>
                        <p class="text-sm text-muted">{{ $arena->alamat }}</p>
                        <p class="text-sm font-semibold">{{ $jumlah }} · {{ $harga }}</p>
                    </div>

                    <span @class([
                        'px-3 py-1 rounded-full text-xs font-bold',
                        'bg-court-soft text-court-dark' => $arena->status === 'aktif',
                        'bg-gray-200 text-gray-700' => $arena->status !== 'aktif',
                    ])>
                        {{ $arena->status === 'aktif' ? __('Active') : __('Inactive') }}
                    </span>

                    <a href="{{ route('admin.arena.show', $arena) }}"
                       class="h-10 px-4 rounded-xl border border-gray-300 text-sm font-bold inline-flex items-center hover:border-ink">
                        {{ __('Manage') }}
                    </a>
                </article>
            @empty
                <div class="bg-white border border-line rounded-2xl p-8 text-center space-y-4">
                    <p class="font-semibold">{{ __('No venues yet. Add your first one.') }}</p>
                    <a href="{{ route('admin.arena.create') }}"
                       class="inline-flex h-11 px-5 rounded-xl bg-ink text-volt font-bold items-center">
                        + {{ __('New venue') }}
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>