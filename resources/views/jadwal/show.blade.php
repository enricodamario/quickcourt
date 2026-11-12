<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('lapangan.index') }}" class="text-gray-500 hover:text-gray-800">&larr; Kembali</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $lapangan->nama }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <p class="text-gray-600">
                Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }} / jam
                · {{ ucfirst($lapangan->jenis) }}
            </p>

            @if ($errors->any())
                <div class="p-4 rounded-md bg-red-50 text-red-800 space-y-1">
                    @foreach ($errors->all() as $pesan)
                        <p>{{ $pesan }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Pilih tanggal --}}
            <div>
                <p class="text-sm font-semibold text-gray-800 mb-2">
                    {{ $tanggal->locale('id')->translatedFormat('l, d F Y') }}
                </p>
                <div class="grid grid-cols-7 gap-2">
                    @foreach ($daftarHari as $hari)
                        <a href="{{ route('lapangan.show', ['lapangan' => $lapangan, 'tanggal' => $hari['tanggal']]) }}"
                           class="flex flex-col items-center justify-center h-16 rounded-lg border text-sm
                                  {{ $hari['aktif'] ? 'bg-green-700 border-green-700 text-white' : 'bg-white border-gray-300 text-gray-800 hover:border-green-600' }}">
                            <span class="text-xs">{{ $hari['nama'] }}</span>
                            <span class="text-lg font-bold">{{ $hari['angka'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Grid jam: form untuk memilih jam --}}
            <form method="GET" action="{{ route('booking.create') }}" x-data="{ dipilih: [] }" class="space-y-4">
                <input type="hidden" name="lapangan_id" value="{{ $lapangan->id }}">
                <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-gray-800">Pilih jam (berurutan, maks. 4 jam)</p>
                    <div class="flex gap-4 text-xs text-gray-600">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-white border border-gray-400"></span>Tersedia</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-700"></span>Dipilih</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-200"></span>Penuh / lewat</span>
                    </div>
                </div>

                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                    @foreach ($slots as $slot)
                        @if ($slot['status'] === 'tersedia')
                            <label class="cursor-pointer">
                                <input type="checkbox" name="jam[]" value="{{ $slot['jam'] }}" x-model="dipilih" class="peer sr-only">
                                <span class="h-12 flex items-center justify-center rounded-lg border border-gray-400 bg-white text-gray-900 font-medium
                                             peer-checked:bg-blue-700 peer-checked:border-blue-700 peer-checked:text-white
                                             peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500">
                                    {{ $slot['label'] }}
                                </span>
                            </label>
                        @else
                            <div class="h-12 flex flex-col items-center justify-center rounded-lg bg-gray-200 text-gray-500">
                                <span class="line-through">{{ $slot['label'] }}</span>
                                <span class="text-[10px]">{{ $slot['status'] === 'penuh' ? 'Penuh' : 'Lewat' }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Ringkasan + tombol lanjut --}}
                <div class="flex items-center justify-between gap-4 bg-white rounded-lg shadow-sm p-4">
                    <div>
                        <p class="text-sm text-gray-500"
                           x-text="dipilih.length ? dipilih.length + ' jam dipilih' : 'Belum ada jam dipilih'">Belum ada jam dipilih</p>
                        <p class="text-xl font-bold text-gray-900"
                           x-text="'Rp ' + (dipilih.length * {{ $lapangan->harga_per_jam }}).toLocaleString('id-ID')">Rp 0</p>
                    </div>
                    <button type="submit" :disabled="dipilih.length === 0"
                            class="px-6 py-3 rounded-lg bg-green-700 text-white font-semibold hover:bg-green-800 disabled:opacity-50 disabled:cursor-not-allowed">
                        Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>