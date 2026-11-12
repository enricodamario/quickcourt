<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pembayaran
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4"
             x-data="{
                 sisa: {{ $sisaDetik }},
                 init() { setInterval(() => { if (this.sisa > 0) this.sisa-- }, 1000) },
                 get waktu() {
                     return String(Math.floor(this.sisa / 60)).padStart(2, '0') + ':' + String(this.sisa % 60).padStart(2, '0')
                 }
             }">

            @if ($errors->any())
                <div class="p-4 rounded-md bg-red-50 text-red-800">{{ $errors->first() }}</div>
            @endif

            {{-- Hitung mundur --}}
            <div class="bg-white shadow-sm rounded-lg p-6 text-center space-y-1">
                <p class="text-sm text-gray-500">Selesaikan pembayaran dalam</p>
                <p class="text-4xl font-bold text-gray-900 tabular-nums" x-text="waktu">--:--</p>
                <p class="text-sm text-gray-500">
                    Batas: {{ $booking->batas_bayar->format('H:i') }} WIB
                </p>
                <p x-show="sisa === 0" x-cloak class="text-sm font-semibold text-red-700">
                    Waktu pembayaran habis. Jadwal akan dibuka kembali.
                </p>
            </div>

            {{-- Ringkasan --}}
            <div class="bg-white shadow-sm rounded-lg p-6 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Lapangan</span>
                    <span class="text-gray-900">{{ $booking->lapangan->nama }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Jadwal</span>
                    <span class="text-gray-900">
                        {{ $booking->tanggal->locale('id')->translatedFormat('D, d M Y') }}
                        · {{ substr($booking->jam_mulai, 0, 5) }}–{{ substr($booking->jam_selesai, 0, 5) }}
                    </span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-200 text-base font-bold">
                    <span>Total bayar</span>
                    <span>Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Pilih metode & bayar --}}
            <form method="POST" action="{{ route('booking.prosesBayar', $booking) }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                @csrf
                <fieldset class="space-y-2">
                    <legend class="text-sm font-semibold text-gray-800 mb-2">Metode pembayaran</legend>
                    @foreach (\App\Models\Booking::METODE_BAYAR as $kode => $nama)
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-300 cursor-pointer has-[:checked]:border-green-700 has-[:checked]:bg-green-50">
                            <input type="radio" name="metode_bayar" value="{{ $kode }}" @checked(old('metode_bayar') === $kode)
                                   class="text-green-700 focus:ring-green-600">
                            <span class="text-gray-900">{{ $nama }}</span>
                        </label>
                    @endforeach
                </fieldset>

                <p class="text-xs text-gray-500">
                    Ini simulasi pembayaran untuk keperluan demo. Tidak ada uang sungguhan yang ditarik.
                </p>

                <button type="submit" :disabled="sisa === 0"
                        class="w-full px-6 py-3 rounded-lg bg-green-700 text-white font-semibold hover:bg-green-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    Bayar Sekarang
                </button>
            </form>

            <form method="POST" action="{{ route('booking.batal', $booking) }}" class="text-center"
                  onsubmit="return confirm('Yakin ingin membatalkan booking ini?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="text-sm text-red-700 hover:underline">Batalkan booking</button>
            </form>
        </div>
    </div>
</x-app-layout>