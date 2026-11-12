<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Admin
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Lapangan aktif</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $jumlahLapangan }}</p>
                </div>

                <a href="{{ route('admin.booking.index', ['status' => 'menunggu_pembayaran']) }}"
                   class="block bg-white shadow-sm rounded-lg p-6 border border-transparent hover:border-yellow-400">
                    <p class="text-sm text-gray-500">Menunggu pembayaran</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $menungguPembayaran }}</p>
                    <p class="mt-2 text-sm text-yellow-800">Lihat &rarr;</p>
                </a>

                <a href="{{ route('admin.booking.index', ['tanggal' => today()->toDateString(), 'status' => 'dikonfirmasi']) }}"
                   class="block bg-white shadow-sm rounded-lg p-6 border border-transparent hover:border-green-500">
                    <p class="text-sm text-gray-500">Booking lunas hari ini</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $bookingHariIni }}</p>
                    <p class="mt-2 text-sm text-green-800">Lihat &rarr;</p>
                </a>

                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">Pendapatan hari ini</p>
                    <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.lapangan.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Kelola Lapangan
                </a>
                <a href="{{ route('admin.booking.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Kelola Booking
                </a>
            </div>
        </div>
    </div>
</x-app-layout>