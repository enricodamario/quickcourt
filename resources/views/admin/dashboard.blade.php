<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Admin
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Jumlah lapangan</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $jumlahLapangan }}</p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Booking menunggu konfirmasi</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $bookingPending }}</p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Booking hari ini</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $bookingHariIni }}</p>
                </div>
            </div>

                        <div class="mt-6">
                <a href="{{ route('admin.lapangan.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Kelola Lapangan
                </a>
            </div>
            
        </div>
    </div>
</x-app-layout>