@props(['status'])

@php
    [$label, $warna] = match ($status) {
        'menunggu_pembayaran' => ['Menunggu pembayaran', 'bg-yellow-100 text-yellow-900'],
        'dikonfirmasi' => ['Lunas', 'bg-green-100 text-green-800'],
        'kedaluwarsa' => ['Kedaluwarsa', 'bg-gray-200 text-gray-700'],
        default => ['Dibatalkan', 'bg-red-100 text-red-800'],
    };
@endphp

<span {{ $attributes->merge(['class' => "px-3 py-1 text-xs font-semibold rounded-full whitespace-nowrap $warna"]) }}>
    {{ $label }}
</span>