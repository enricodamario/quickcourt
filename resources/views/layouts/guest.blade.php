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
<body class="font-sans antialiased bg-court-dark text-ink">
    <div class="min-h-screen flex flex-col lg:grid lg:grid-cols-2">

        {{-- Hero hijau tua --}}
        <div class="text-white px-6 pt-7 pb-9 sm:px-10 lg:px-14 lg:py-12 flex flex-col gap-7 lg:justify-between">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <x-application-logo class="h-9 w-auto text-volt" lines="#0E3B22" />
                    <span class="text-xl font-extrabold tracking-tight">QuickCourt</span>
                </a>
                <x-language-switch dark />
            </div>

            <div class="space-y-5 lg:max-w-lg">
                @isset($badge)
                    {{ $badge }}
                @endisset

                <h1 class="text-4xl lg:text-5xl font-extrabold leading-[1.15] tracking-tight">
                    {{ __('Book a court in') }}
                    <span class="inline-block mt-2 bg-volt text-court-dark px-3 pb-1 rounded-xl whitespace-nowrap">{{ __('seconds.') }}</span>
                </h1>

                <p class="text-base lg:text-lg text-white/75 leading-relaxed">
                    {{ __('See live availability, lock your hours, and pay before anyone else grabs them.') }}
                </p>
            </div>

            <p class="hidden lg:block text-sm text-white/60">&copy; {{ date('Y') }} QuickCourt</p>
        </div>

        {{-- Lembar putih berisi form --}}
        <div class="flex-1 bg-white rounded-t-[2rem] lg:rounded-none px-6 py-8 sm:px-10 lg:px-16 flex flex-col justify-center">
            <div class="w-full max-w-md mx-auto">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>