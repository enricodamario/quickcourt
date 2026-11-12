<x-guest-layout>
    {{-- Chip "jam tersedia hari ini": hanya muncul di halaman depan --}}
    @isset($jamTersedia)
        <x-slot name="badge">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-court text-volt text-xs font-bold">
                <span class="h-2 w-2 rounded-full bg-volt"></span>
                {{ __(':count slots open today', ['count' => $jamTersedia]) }}
            </span>
        </x-slot>
    @endisset

    <h2 class="text-2xl font-extrabold">{{ __('Welcome back') }}</h2>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div class="space-y-1.5">
            <label for="email" class="block text-sm font-semibold">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   placeholder="name@example.com" required autofocus autocomplete="username"
                   class="block w-full h-12 px-4 rounded-xl border-gray-300 focus:border-court focus:ring-court">
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-1.5">
            <div class="flex items-baseline justify-between">
                <label for="password" class="block text-sm font-semibold">{{ __('Password') }}</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-court hover:underline">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="block w-full h-12 px-4 rounded-xl border-gray-300 focus:border-court focus:ring-court">
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <label for="remember_me" class="inline-flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded border-gray-300 text-court focus:ring-court">
            <span class="text-sm text-muted">{{ __('Remember me') }}</span>
        </label>

        <button type="submit"
                class="w-full h-13 py-3.5 rounded-2xl bg-ink text-volt text-base font-extrabold hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-court focus:ring-offset-2">
            {{ __('Log in') }}
        </button>
    </form>

    <p class="mt-5 text-center text-sm text-muted">
        {{ __('New here?') }}
        <a href="{{ route('register') }}" class="font-bold text-court hover:underline">{{ __('Create an account') }}</a>
    </p>

    {{-- Akun demo untuk perekrut / penguji portfolio --}}
    <div class="mt-6 rounded-2xl bg-ground p-4 text-xs text-muted space-y-0.5">
        <p class="font-bold text-ink">{{ __('Demo accounts') }}</p>
        <p>Admin · admin@example.com · password</p>
        <p>Customer · budi@example.com · password</p>
    </div>
</x-guest-layout>
