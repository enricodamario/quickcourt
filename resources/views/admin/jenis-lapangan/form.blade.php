<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $jenisLapangan->exists ? 'Edit Jenis Lapangan' : 'Tambah Jenis Lapangan' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST"
                      action="{{ $jenisLapangan->exists ? route('admin.jenis-lapangan.update', $jenisLapangan) : route('admin.jenis-lapangan.store') }}"
                      class="space-y-6">
                    @csrf
                    @if ($jenisLapangan->exists)
                        @method('PUT')
                    @endif

                    <div>
                        <x-input-label for="nama" value="Nama jenis" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                                      :value="old('nama', $jenisLapangan->nama)" placeholder="Contoh: Tenis" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Simpan</x-primary-button>
                        <a href="{{ route('admin.jenis-lapangan.index') }}" class="text-gray-600 hover:underline">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>