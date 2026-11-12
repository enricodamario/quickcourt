<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Jenis Lapangan
            </h2>
            <a href="{{ route('admin.jenis-lapangan.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                + Tambah Jenis
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="p-4 rounded-md bg-green-50 text-green-800">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-md bg-red-50 text-red-800">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah lapangan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($daftarJenis as $jenis)
                            <tr>
                                <td class="px-6 py-4 text-gray-900">{{ $jenis->nama }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $jenis->lapangan_count }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.jenis-lapangan.edit', $jenis) }}" class="text-indigo-600 hover:underline">Edit</a>

                                    <form action="{{ route('admin.jenis-lapangan.destroy', $jenis) }}" method="POST" class="inline ml-3"
                                          onsubmit="return confirm('Yakin ingin menghapus jenis ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada jenis lapangan. Klik "Tambah Jenis" untuk mulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <a href="{{ route('admin.lapangan.index') }}" class="inline-block text-gray-600 hover:underline">&larr; Kembali ke Kelola Lapangan</a>
        </div>
    </div>
</x-app-layout>