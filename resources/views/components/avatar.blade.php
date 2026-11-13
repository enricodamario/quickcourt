@props(['user'])

@if ($user->fotoUrl())
    <img src="{{ $user->fotoUrl() }}" alt="{{ $user->name }}"
         {{ $attributes->merge(['class' => 'rounded-full object-cover shrink-0']) }}>
@else
    <span {{ $attributes->merge(['class' => 'rounded-full bg-ink text-volt text-sm font-bold flex items-center justify-center shrink-0']) }}>
        {{ $user->initials() }}
    </span>
@endif