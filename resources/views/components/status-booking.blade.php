@props(['status'])

@php
    [$label, $warna] = match ($status) {
        'menunggu_pembayaran' => [__('Awaiting payment'), 'bg-amber-100 text-amber-900'],
        'dikonfirmasi' => [__('Paid'), 'bg-volt text-ink'],
        'kedaluwarsa' => [__('Expired'), 'bg-gray-200 text-gray-700'],
        default => [__('Cancelled'), 'bg-red-100 text-red-800'],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap $warna"]) }}>
    {{ $label }}
</span>