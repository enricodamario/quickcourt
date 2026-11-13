<x-app-layout>
    @php
        $rupiah = fn ($angka) => 'Rp ' . number_format($angka, 0, ',', '.');
        $venuePunyaBooking = $daftarCourt->sum('bookings_count') > 0;
        $hargaAwal = old('harga_per_jam', optional($daftarCourt->first())->harga_per_jam);
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 space-y-5">
        <a href="{{ route('admin.arena.index') }}" class="text-sm font-bold text-court hover:underline">&larr; {{ __('All venues') }}</a>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-court-soft text-court-dark text-sm font-semibold">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-red-50 text-red-800 text-sm font-semibold">{{ session('error') }}</div>
        @endif

        {{-- Info venue --}}
        <section class="bg-white border border-line rounded-3xl p-4 sm:p-5 flex flex-wrap items-center gap-5">
            @if ($arena->fotoUrl())
                <img src="{{ $arena->fotoUrl() }}" alt="{{ $arena->nama }}" class="h-28 w-44 shrink-0 rounded-2xl object-cover">
            @else
                <div class="h-28 w-44 shrink-0 rounded-2xl bg-court flex items-center justify-center">
                    <x-application-logo class="h-12 w-auto text-volt" lines="#15803D" />
                </div>
            @endif

            <div class="flex-1 min-w-[240px] space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-extrabold">{{ $arena->nama }}</h1>
                    <span class="px-3 py-0.5 rounded-full bg-ink text-volt text-xs font-extrabold">{{ $arena->jenis->nama }}</span>
                    <span @class([
                        'px-3 py-0.5 rounded-full text-xs font-bold',
                        'bg-court-soft text-court-dark' => $arena->status === 'aktif',
                        'bg-gray-200 text-gray-700' => $arena->status !== 'aktif',
                    ])>
                        {{ $arena->status === 'aktif' ? __('Active') : __('Inactive') }}
                    </span>
                </div>
                <p class="text-sm text-muted">{{ $arena->alamat }}</p>
                @if ($arena->deskripsi)
                    <p class="text-sm text-muted">{{ $arena->deskripsi }}</p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.arena.edit', $arena) }}"
                   class="h-11 px-4 rounded-xl border border-gray-300 text-sm font-bold inline-flex items-center hover:border-ink">
                    {{ __('Edit venue') }}
                </a>
                @unless ($venuePunyaBooking)
                    <form method="POST" action="{{ route('admin.arena.destroy', $arena) }}"
                          onsubmit="return confirm(@js(__('Delete this venue?')))">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="h-11 px-4 rounded-xl border border-red-200 text-red-700 text-sm font-bold hover:bg-red-50">
                            {{ __('Delete venue') }}
                        </button>
                    </form>
                @endunless
            </div>
        </section>

        {{-- Daftar court --}}
        <section class="bg-white border border-line rounded-3xl overflow-hidden">
            <div class="p-5 flex flex-wrap items-end justify-between gap-4 border-b border-line">
                <div>
                    <h2 class="text-lg font-extrabold">{{ __('Courts') }}</h2>
                    <p class="text-sm text-muted">{{ __('Each court has its own price and schedule.') }}</p>
                </div>

                <form method="POST" action="{{ route('admin.lapangan.store', $arena) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <div class="space-y-1">
                        <label for="harga_baru" class="block text-xs font-semibold text-muted">{{ __('Price / hour') }}</label>
                        <input id="harga_baru" name="harga_per_jam" type="number" min="0" step="1000" value="{{ $hargaAwal }}" required
                               class="h-11 w-36 px-3 rounded-xl border-gray-300 focus:border-court focus:ring-court">
                    </div>
                    <button type="submit" class="h-11 px-4 rounded-xl bg-ink text-volt text-sm font-extrabold hover:brightness-110">
                        + {{ __('Add court') }}
                    </button>
                </form>
            </div>

            @if ($errors->any())
                <div class="mx-5 mt-4 p-3 rounded-xl bg-red-50 text-red-800 text-sm">{{ $errors->first() }}</div>
            @endif

            <div class="overflow-x-auto">
                <div class="min-w-[680px]">
                    <div class="grid grid-cols-5 gap-3 px-5 py-3 bg-ground text-xs font-bold text-muted">
                        <span>{{ __('Court') }}</span>
                        <span>{{ __('Price / hour') }}</span>
                        <span>{{ __('Bookings') }}</span>
                        <span>{{ __('Status') }}</span>
                        <span class="text-right">&nbsp;</span>
                    </div>

                    @foreach ($daftarCourt as $court)
                        <form id="ubah-{{ $court->id }}" method="POST" action="{{ route('admin.lapangan.update', $court) }}"
                              class="grid grid-cols-5 gap-3 px-5 py-3 items-center border-t border-line">
                            @csrf
                            @method('PATCH')

                            <span class="font-extrabold">{{ $court->nama }}</span>

                            <input name="harga_per_jam" type="number" min="0" step="1000" value="{{ $court->harga_per_jam }}" required
                                   aria-label="{{ __('Price / hour') }} {{ $court->nama }}"
                                   class="h-10 w-full px-3 rounded-xl border-gray-300 focus:border-court focus:ring-court">

                            <span class="text-sm text-muted">
                                {{ $court->bookings_count === 1 ? __('1 booking') : __(':count bookings', ['count' => $court->bookings_count]) }}
                            </span>

                            <select name="status" aria-label="{{ __('Status') }} {{ $court->nama }}"
                                    class="h-10 w-full px-3 rounded-xl border-gray-300 focus:border-court focus:ring-court">
                                <option value="aktif" @selected($court->status === 'aktif')>{{ __('Active') }}</option>
                                <option value="nonaktif" @selected($court->status === 'nonaktif')>{{ __('Inactive') }}</option>
                            </select>

                            <div class="flex justify-end gap-2">
                                <button type="submit"
                                        class="h-10 px-3 rounded-xl border border-gray-300 text-sm font-bold hover:border-ink">
                                    {{ __('Save') }}
                                </button>
                                @if ($court->bookings_count === 0)
                                    <button type="submit" form="hapus-{{ $court->id }}"
                                            class="h-10 px-3 rounded-xl border border-red-200 text-red-700 text-sm font-bold hover:bg-red-50">
                                        {{ __('Delete') }}
                                    </button>
                                @endif
                            </div>
                        </form>

                        @if ($court->bookings_count === 0)
                            <form id="hapus-{{ $court->id }}" method="POST" action="{{ route('admin.lapangan.destroy', $court) }}"
                                  class="hidden" onsubmit="return confirm(@js(__('Delete this court?')))">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>

            <p class="px-5 py-3 text-xs text-muted border-t border-line">
                {{ __("Courts with bookings can't be deleted. Set them to inactive instead.") }}
            </p>
        </section>
    </div>
</x-app-layout>