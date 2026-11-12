<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Konfirmasi Booking
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if ($errors->any())
                <div class="p-4 rounded-md bg-red-50 text-red-800 space-y-1">
                    @foreach ($errors->all() as $pesan)
                        <p>{{ $pesan }}</p>
                    @endforeach
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                <div>
                    <p class="text-xs font-semibold text-green-700 uppercase">{{ $lapangan->jenis }}</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $lapangan->nama }}</p>
                </div>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tanggal</dt>
                        <dd class="text-gray-900">{{ $tanggal->locale('id')->translatedFormat('l, d F Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Jam</dt>
                        <dd class="text-gray-900">
                            {{ sprintf('%02d.00', $jamList[0]) }} – {{ sprintf('%02d.00', $jamList[count($jamList) - 1] + 1) }}
                            ({{ count($jamList) }} jam)
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Harga</dt>
                        <dd class="text-gray-900">
                            Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }} × {{ count($jamList) }} jam
                        </dd>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-200 text-base font-bold">
                        <dt>Total</dt>
                        <dd>Rp {{ number_format($totalHarga, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="p-4 rounded-md bg-yellow-50 text-yellow-900 text-sm">
                Setelah lanjut, jadwal ini <strong>ditahan selama {{ \App\Models\Booking::BATAS_BAYAR_MENIT }} menit</strong>
                untuk pembayaran. Booking langsung terkonfirmasi setelah dibayar.
            </div>

            <form method="POST" action="{{ route('booking.store') }}" class="flex items-center justify-between gap-4">
                @csrf
                <input type="hidden" name="lapangan_id" value="{{ $lapangan->id }}">
                <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                @foreach ($jamList as $jam)
                    <input type="hidden" name="jam[]" value="{{ $jam }}">
                @endforeach

                <a href="{{ route('lapangan.show', ['lapangan' => $lapangan, 'tanggal' => $tanggal->toDateString()]) }}"
                   class="text-gray-600 hover:underline">Ubah jam</a>

                <button type="submit" class="px-6 py-3 rounded-lg bg-green-700 text-white font-semibold hover:bg-green-800">
                    Lanjut ke Pembayaran
                </button>
            </form>
        </div>
    </div>
</x-app-layout>