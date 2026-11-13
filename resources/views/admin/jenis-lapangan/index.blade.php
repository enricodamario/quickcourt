<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h1 class="text-2xl font-extrabold tracking-tight">{{ __('Court types') }}</h1>
            <a href="{{ route('admin.jenis-lapangan.create') }}"
               class="h-11 px-5 rounded-xl bg-ink text-volt font-extrabold inline-flex items-center hover:brightness-110">
                + {{ __('New court type') }}
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-court-soft text-court-dark text-sm font-semibold">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-red-50 text-red-800 text-sm font-semibold">{{ session('error') }}</div>
        @endif

        <div class="bg-white border border-line rounded-3xl overflow-hidden">
            @forelse ($daftarJenis as $jenis)
                <div class="px-5 py-4 flex flex-wrap items-center gap-4 border-t border-line first:border-t-0">
                    <div class="flex-1 min-w-[160px]">
                        <p class="font-extrabold">{{ $jenis->nama }}</p>
                        <p class="text-sm text-muted">
                            {{ $jenis->arena_count === 1 ? __('1 venue') : __(':count venues', ['count' => $jenis->arena_count]) }}
                        </p>
                    </div>

                    <a href="{{ route('admin.jenis-lapangan.edit', $jenis) }}"
                       class="h-10 px-4 rounded-xl border border-gray-300 text-sm font-bold inline-flex items-center hover:border-ink">
                        {{ __('Edit') }}
                    </a>

                    @if ($jenis->arena_count === 0)
                        <form method="POST" action="{{ route('admin.jenis-lapangan.destroy', $jenis) }}"
                              onsubmit="return confirm(@js(__('Delete this court type?')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="h-10 px-4 rounded-xl border border-red-200 text-red-700 text-sm font-bold hover:bg-red-50">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="p-8 text-center font-semibold">{{ __('No court types yet. Add your first one.') }}</p>
            @endforelse
        </div>
    </div>
</x-app-layout>