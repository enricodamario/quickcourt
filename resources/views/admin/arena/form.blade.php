<x-app-layout>
    @php
        $kelasInput = 'block w-full h-12 px-4 rounded-xl border-gray-300 focus:border-court focus:ring-court';
    @endphp

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 space-y-5">
        <a href="{{ $arena->exists ? route('admin.arena.show', $arena) : route('admin.arena.index') }}"
           class="text-sm font-bold text-court hover:underline">
            &larr; {{ $arena->exists ? $arena->nama : __('All venues') }}
        </a>

        <h1 class="text-2xl font-extrabold tracking-tight">
            {{ $arena->exists ? __('Edit venue') : __('New venue') }}
        </h1>

        <form method="POST" enctype="multipart/form-data"
              action="{{ $arena->exists ? route('admin.arena.update', $arena) : route('admin.arena.store') }}"
              x-data="{ jumlah: {{ (int) old('jumlah_court', 3) }} }"
              class="bg-white border border-line rounded-3xl p-6 space-y-5">
            @csrf
            @if ($arena->exists)
                @method('PUT')
            @endif

            <div class="space-y-1.5">
                <label for="nama" class="block text-sm font-semibold">{{ __('Venue name') }}</label>
                <input id="nama" name="nama" type="text" value="{{ old('nama', $arena->nama) }}"
                       placeholder="Vie Arena" required class="{{ $kelasInput }}">
                <x-input-error :messages="$errors->get('nama')" />
            </div>

            <div class="space-y-1.5">
                <label for="jenis_lapangan_id" class="block text-sm font-semibold">{{ __('Sport') }}</label>
                <select id="jenis_lapangan_id" name="jenis_lapangan_id" required class="{{ $kelasInput }}">
                    <option value="">{{ __('Choose a sport') }}</option>
                    @foreach ($daftarJenis as $jenis)
                        <option value="{{ $jenis->id }}" @selected(old('jenis_lapangan_id', $arena->jenis_lapangan_id) == $jenis->id)>
                            {{ $jenis->nama }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('jenis_lapangan_id')" />
            </div>

            <div class="space-y-1.5">
                <label for="alamat" class="block text-sm font-semibold">{{ __('Address') }}</label>
                <input id="alamat" name="alamat" type="text" value="{{ old('alamat', $arena->alamat) }}"
                       placeholder="Jl. Kemang Raya No. 12, Jakarta Selatan" required class="{{ $kelasInput }}">
                <x-input-error :messages="$errors->get('alamat')" />
            </div>

            <div class="space-y-1.5">
                <label for="deskripsi" class="block text-sm font-semibold">{{ __('Description (optional)') }}</label>
                <textarea id="deskripsi" name="deskripsi" rows="3"
                          class="block w-full px-4 py-3 rounded-xl border-gray-300 focus:border-court focus:ring-court">{{ old('deskripsi', $arena->deskripsi) }}</textarea>
                <x-input-error :messages="$errors->get('deskripsi')" />
            </div>

            {{-- Jumlah court + harga: hanya saat membuat venue baru --}}
            @unless ($arena->exists)
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label for="jumlah_court" class="block text-sm font-semibold">{{ __('Number of courts') }}</label>
                        <div class="flex h-12 rounded-xl border border-gray-300 overflow-hidden focus-within:border-court">
                            <button type="button" @click="jumlah = Math.max(1, jumlah - 1)" aria-label="{{ __('Fewer courts') }}"
                                    class="w-12 bg-ground text-lg font-extrabold hover:bg-gray-200">−</button>
                            <input id="jumlah_court" name="jumlah_court" type="number" min="1" max="20"
                                   x-model.number="jumlah"
                                   class="flex-1 border-0 text-center font-extrabold focus:ring-0">
                            <button type="button" @click="jumlah = Math.min(20, jumlah + 1)" aria-label="{{ __('More courts') }}"
                                    class="w-12 bg-ground text-lg font-extrabold hover:bg-gray-200">+</button>
                        </div>
                        <x-input-error :messages="$errors->get('jumlah_court')" />
                    </div>

                    <div class="space-y-1.5">
                        <label for="harga_per_jam" class="block text-sm font-semibold">{{ __('Price per court / hour') }}</label>
                        <input id="harga_per_jam" name="harga_per_jam" type="number" min="0" step="1000"
                               value="{{ old('harga_per_jam') }}" placeholder="250000" required class="{{ $kelasInput }}">
                        <x-input-error :messages="$errors->get('harga_per_jam')" />
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-semibold">{{ __('Courts that will be created') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="n in jumlah" :key="n">
                            <span class="px-3 py-1.5 rounded-full bg-volt-soft text-volt-ink text-xs font-extrabold" x-text="'Court ' + n"></span>
                        </template>
                    </div>
                    <p class="text-xs text-muted">{{ __("You can change each court's price or status later.") }}</p>
                </div>
            @endunless

            <div class="space-y-1.5">
                <label for="foto" class="block text-sm font-semibold">{{ __('Venue photo (optional)') }}</label>
                @if ($arena->fotoUrl())
                    <img src="{{ $arena->fotoUrl() }}" alt="{{ $arena->nama }}" class="h-40 w-full max-w-sm rounded-xl object-cover">
                    <label class="flex items-center gap-2 text-sm text-muted">
                        <input type="checkbox" name="hapus_foto" value="1" class="rounded border-gray-300 text-court focus:ring-court">
                        {{ __('Remove this photo') }}
                    </label>
                @endif
                <input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp"
                       class="block w-full text-sm text-muted file:mr-4 file:rounded-lg file:border-0 file:bg-ink file:px-4 file:py-2 file:text-sm file:font-semibold file:text-volt hover:file:brightness-110">
                <p class="text-xs text-muted">{{ __('JPG, PNG or WEBP, max. 2 MB.') }}</p>
                <x-input-error :messages="$errors->get('foto')" />
            </div>

            <div class="space-y-1.5">
                <label for="status" class="block text-sm font-semibold">{{ __('Status') }}</label>
                <select id="status" name="status" class="{{ $kelasInput }}">
                    <option value="aktif" @selected(old('status', $arena->status ?? 'aktif') === 'aktif')>{{ __('Active') }}</option>
                    <option value="nonaktif" @selected(old('status', $arena->status) === 'nonaktif')>{{ __('Inactive') }}</option>
                </select>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" class="h-12 px-6 rounded-2xl bg-ink text-volt font-extrabold hover:brightness-110">
                    {{ $arena->exists ? __('Save changes') : __('Create venue') }}
                </button>
                <a href="{{ $arena->exists ? route('admin.arena.show', $arena) : route('admin.arena.index') }}"
                   class="text-sm font-semibold text-muted hover:text-ink">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-app-layout>