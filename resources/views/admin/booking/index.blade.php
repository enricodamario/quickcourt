<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kelola Lapangan
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.jenis-lapangan.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Kelola Jenis
                </a>
                <a href="{{ route('admin.lapangan.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    + Tambah Lapangan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="p-4 rounded-md bg-green-50 text-green-800">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-md bg-red-50 text-red-800">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga / jam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($daftarLapangan as $lapangan)
                            <tr>
                                <td class="px-6 py-4 text-gray-900">{{ $lapangan->nama }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $lapangan->jenis->nama }}</td>
                                <td class="px-6 py-4 text-gray-700">Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    @if ($lapangan->status === 'aktif')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-700">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.lapangan.edit', $lapangan) }}" class="text-indigo-600 hover:underline">Edit</a>

                                    <form action="{{ route('admin.lapangan.destroy', $lapangan) }}" method="POST" class="inline ml-3"
                                          onsubmit="return confirm('Yakin ingin menghapus lapangan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada lapangan. Klik "Tambah Lapangan" untuk mulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>