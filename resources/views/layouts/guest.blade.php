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
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
        <a href="{{ route('home') }}" class="mb-8 flex flex-col items-center gap-2">
            <x-application-logo class="h-14 w-auto text-court" />
            <span class="text-2xl font-extrabold tracking-tight">Quick<span class="text-court">Court</span></span>
        </a>

        <div class="w-full max-w-md bg-white rounded-3xl border border-line p-6 sm:p-8">
            {{ $slot }}
        </div>

        <x-language-switch class="mt-6" />
    </div>
</body>
</html>