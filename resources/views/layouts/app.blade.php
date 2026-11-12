<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'QuickCourt') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-ground text-ink">
@php
    $isAdmin = Auth::user()->isAdmin();

    // [nama route, pola route untuk menandai menu aktif, label]
    $menu = $isAdmin
        ? [
            ['admin.dashboard', 'admin.dashboard', __('Dashboard')],
            ['admin.lapangan.index', 'admin.lapangan.*', __('Courts')],
            ['admin.jenis-lapangan.index', 'admin.jenis-lapangan.*', __('Court types')],
            ['admin.booking.index', 'admin.booking.*', __('Bookings')],
        ]
        : [
            ['lapangan.index', 'lapangan.*', __('Explore')],
            ['booking.index', 'booking.*', __('Bookings')],
            ['profile.edit', 'profile.*', __('Account')],
        ];
@endphp

<div class="min-h-screen flex flex-col">
    {{-- Navbar atas --}}
    <header class="sticky top-0 z-30 bg-white border-b border-line">
        <div class="max-w-6xl mx-auto h-16 px-4 sm:px-6 flex items-center gap-6">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0">
                <x-application-logo class="h-9 w-auto text-court" />
                <span class="text-lg font-extrabold tracking-tight">Quick<span class="text-court">Court</span></span>
                @if ($isAdmin)
                    <span class="ml-1 px-2 py-0.5 rounded-full bg-ink text-volt text-[11px] font-bold">{{ __('Admin') }}</span>
                @endif
            </a>

            <nav class="hidden md:flex items-center gap-1" aria-label="{{ __('Main menu') }}">
                @foreach ($menu as [$route, $pola, $label])
                    <a href="{{ route($route) }}"
                       @if (request()->routeIs($pola)) aria-current="page" @endif
                       @class([
                           'px-4 py-2 rounded-full text-sm font-semibold',
                           'bg-ink text-volt' => request()->routeIs($pola),
                           'text-muted hover:text-ink hover:bg-ground' => ! request()->routeIs($pola),
                       ])>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-3">
                <x-language-switch />

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="h-10 w-10 rounded-full bg-ink text-volt text-sm font-bold flex items-center justify-center"
                                aria-label="{{ Auth::user()->name }}">
                            {{ Auth::user()->initials() }}
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-line">
                            <p class="text-sm font-semibold text-ink">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-muted truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                             onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>

        {{-- Menu admin di HP: baris yang bisa digeser --}}
        @if ($isAdmin)
            <nav class="md:hidden flex gap-1 overflow-x-auto px-4 pb-3" aria-label="{{ __('Main menu') }}">
                @foreach ($menu as [$route, $pola, $label])
                    <a href="{{ route($route) }}"
                       @class([
                           'shrink-0 px-4 py-2 rounded-full text-sm font-semibold',
                           'bg-ink text-volt' => request()->routeIs($pola),
                           'bg-ground text-muted' => ! request()->routeIs($pola),
                       ])>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        @endif
    </header>

    @isset($header)
        <div class="bg-white border-b border-line">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-5">
                {{ $header }}
            </div>
        </div>
    @endisset

    <main @class(['flex-1', 'pb-24 md:pb-0' => ! $isAdmin])>
        {{ $slot }}
    </main>

    {{-- Navigasi bawah ala aplikasi HP (customer saja) --}}
    @unless ($isAdmin)
        <nav class="md:hidden fixed bottom-0 inset-x-0 z-30 h-20 bg-white border-t border-line flex justify-around items-center"
             aria-label="{{ __('Main menu') }}">
            @php
                $ikon = [
                    'lapangan.*' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
                    'booking.*' => '<path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4z"/>',
                    'profile.*' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
                ];
            @endphp
            @foreach ($menu as [$route, $pola, $label])
                @php $aktif = request()->routeIs($pola); @endphp
                <a href="{{ route($route) }}"
                   @if ($aktif) aria-current="page" @endif
                   class="flex flex-col items-center gap-1 min-w-20 py-1 text-xs {{ $aktif ? 'font-bold text-ink' : 'font-semibold text-muted' }}">
                    <span class="h-8 px-4 rounded-full flex items-center {{ $aktif ? 'bg-volt' : '' }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikon[$pola] !!}</svg>
                    </span>
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    @endunless
</div>
</body>
</html>