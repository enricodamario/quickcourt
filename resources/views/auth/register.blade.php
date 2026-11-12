<x-guest-layout>
    <h2 class="text-2xl font-extrabold">{{ __('Create your account') }}</h2>
    <p class="mt-1 text-sm text-muted">{{ __('Start booking courts in minutes.') }}</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div class="space-y-1.5">
            <label for="name" class="block text-sm font-semibold">{{ __('Name') }}</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   placeholder="Budi Santoso" required autofocus autocomplete="name"
                   class="block w-full h-12 px-4 rounded-xl border-gray-300 focus:border-court focus:ring-court">
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="space-y-1.5">
            <label for="email" class="block text-sm font-semibold">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   placeholder="name@example.com" required autocomplete="username"
                   class="block w-full h-12 px-4 rounded-xl border-gray-300 focus:border-court focus:ring-court">
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-1.5">
            <label for="password" class="block text-sm font-semibold">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="block w-full h-12 px-4 rounded-xl border-gray-300 focus:border-court focus:ring-court">
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="space-y-1.5">
            <label for="password_confirmation" class="block text-sm font-semibold">{{ __('Confirm password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="block w-full h-12 px-4 rounded-xl border-gray-300 focus:border-court focus:ring-court">
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <button type="submit"
                class="w-full py-3.5 rounded-2xl bg-ink text-volt text-base font-extrabold hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-court focus:ring-offset-2">
            {{ __('Sign up') }}
        </button>
    </form>

    <p class="mt-5 text-center text-sm text-muted">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" class="font-bold text-court hover:underline">{{ __('Log in') }}</a>
    </p>
</x-guest-layout>