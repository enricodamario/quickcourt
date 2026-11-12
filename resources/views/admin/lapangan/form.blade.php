<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $lapangan->exists ? 'Edit Lapangan' : 'Tambah Lapangan' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" enctype="multipart/form-data"
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
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-court focus:ring-court">
                            <option value="">Pilih jenis</option>
                            @foreach ($daftarJenis as $jenis)
                                <option value="{{ $jenis->id }}" @selected(old('jenis_lapangan_id', $lapangan->jenis_lapangan_id) == $jenis->id)>
                                    {{ $jenis->nama }}
                                </option>
                            @endforeach
                        </select>
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
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-court focus:ring-court">{{ old('deskripsi', $lapangan->deskripsi) }}</textarea>
                        <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="foto" value="Foto lapangan (opsional)" />
                        @if ($lapangan->fotoUrl())
                            <img src="{{ $lapangan->fotoUrl() }}" alt="Foto {{ $lapangan->nama }}"
                                 class="mt-2 h-40 w-full max-w-sm rounded-xl object-cover">
                            <label class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="hapus_foto" value="1"
                                       class="rounded border-gray-300 text-court focus:ring-court">
                                Hapus foto ini
                            </label>
                        @endif
                        <input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp"
                               class="mt-2 block w-full text-sm text-gray-600
                                      file:mr-4 file:rounded-lg file:border-0 file:bg-ink file:px-4 file:py-2
                                      file:text-sm file:font-semibold file:text-volt hover:file:brightness-110">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                        <x-input-error :messages="$errors->get('foto')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-court focus:ring-court">
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