<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $lapangan->exists ? 'Edit Lapangan' : 'Tambah Lapangan' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST"
                      action="{{ $lapangan->exists ? route('admin.lapangan.update', $lapangan) : route('admin.lapangan.store') }}"
                      class="space-y-6">
                    @csrf
                    @if ($lapangan->exists)
                        @method('PUT')
                    @endif

                    <div>
                        <x-input-label for="nama" value="Nama lapangan" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                                      :value="old('nama', $lapangan->nama)" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="jenis_lapangan_id" value="Jenis" />
                        <select id="jenis_lapangan_id" name="jenis_lapangan_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Pilih jenis</option>
                            @foreach ($daftarJenis as $jenis)
                                <option value="{{ $jenis->id }}" @selected(old('jenis_lapangan_id', $lapangan->jenis_lapangan_id) == $jenis->id)>
                                    {{ $jenis->nama }}
                                </option>
                            @endforeach
                        </select>
                        @if ($daftarJenis->isEmpty())
                            <p class="mt-2 text-sm text-gray-600">
                                Belum ada jenis lapangan.
                                <a href="{{ route('admin.jenis-lapangan.create') }}" class="text-indigo-600 hover:underline">Tambah jenis dulu</a>.
                            </p>
                        @endif
                        <x-input-error :messages="$errors->get('jenis_lapangan_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="harga_per_jam" value="Harga per jam (Rp)" />
                        <x-text-input id="harga_per_jam" name="harga_per_jam" type="number" min="0" step="1000"
                                      class="mt-1 block w-full"
                                      :value="old('harga_per_jam', $lapangan->harga_per_jam)" required />
                        <x-input-error :messages="$errors->get('harga_per_jam')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="deskripsi" value="Deskripsi (opsional)" />
                        <textarea id="deskripsi" name="deskripsi" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('deskripsi', $lapangan->deskripsi) }}</textarea>
                        <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="aktif" @selected(old('status', $lapangan->status ?? 'aktif') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(old('status', $lapangan->status) === 'nonaktif')>Nonaktif</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Simpan</x-primary-button>
                        <a href="{{ route('admin.lapangan.index') }}" class="text-gray-600 hover:underline">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>