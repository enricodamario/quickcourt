<x-app-layout>
    <div class="max-w-xl mx-auto px-4 sm:px-6 py-6 space-y-5">
        <a href="{{ route('admin.jenis-lapangan.index') }}" class="text-sm font-bold text-court hover:underline">
            &larr; {{ __('Court types') }}
        </a>

        <h1 class="text-2xl font-extrabold tracking-tight">
            {{ $jenisLapangan->exists ? __('Edit court type') : __('New court type') }}
        </h1>

        <form method="POST"
              action="{{ $jenisLapangan->exists ? route('admin.jenis-lapangan.update', $jenisLapangan) : route('admin.jenis-lapangan.store') }}"
              class="bg-white border border-line rounded-3xl p-6 space-y-5">
            @csrf
            @if ($jenisLapangan->exists)
                @method('PUT')
            @endif

            <div class="space-y-1.5">
                <label for="nama" class="block text-sm font-semibold">{{ __('Court type name') }}</label>
                <input id="nama" name="nama" type="text" value="{{ old('nama', $jenisLapangan->nama) }}"
                       placeholder="Padel" required autofocus
                       class="block w-full h-12 px-4 rounded-xl border-gray-300 focus:border-court focus:ring-court">
                <x-input-error :messages="$errors->get('nama')" />
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="h-12 px-6 rounded-2xl bg-ink text-volt font-extrabold hover:brightness-110">
                    {{ __('Save') }}
                </button>
                <a href="{{ route('admin.jenis-lapangan.index') }}"
                   class="text-sm font-semibold text-muted hover:text-ink">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-app-layout>