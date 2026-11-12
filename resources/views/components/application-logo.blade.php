@props(['lines' => '#ffffff'])

<svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="QuickCourt" {{ $attributes }}>
    <rect x="2" y="6" width="44" height="36" rx="9" fill="currentColor"/>
    <g fill="none" stroke="{{ $lines }}" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
        <rect x="9" y="13" width="30" height="22" rx="2"/>
        <line x1="24" y1="13" x2="24" y2="35"/>
        <circle cx="24" cy="24" r="4.5"/>
    </g>
</svg>