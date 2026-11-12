<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pilih Lapangan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($daftarLapangan as $lapangan)
                    <a href="{{ route('lapangan.show', $lapangan) }}"
                       class="block bg-white shadow-sm rounded-lg p-5 border border-transparent hover:border-green-600 transition">
                        <p class="text-xs font-semibold text-green-700 uppercase">{{ $lapangan->jenis }}</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $lapangan->nama }}</p>
                        @if ($lapangan->deskripsi)
                            <p class="mt-1 text-sm text-gray-600">{{ $lapangan->deskripsi }}</p>
                        @endif
                        <p class="mt-3 text-base font-bold text-gray-900">
                            Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}
                            <span class="text-sm font-normal text-gray-500">/ jam</span>
                        </p>
                    </a>
                @empty
                    <p class="text-gray-500">Belum ada lapangan yang tersedia.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>