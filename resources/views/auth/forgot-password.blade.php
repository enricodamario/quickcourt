<x-guest-layout>
    <h2 class="text-2xl font-extrabold">{{ __('Reset your password') }}</h2>
    <p class="mt-1 text-sm text-muted">{{ __("Enter your email and we'll send you a link to set a new password.") }}</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        <div class="space-y-1.5">
            <label for="email" class="block text-sm font-semibold">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   placeholder="name@example.com" required autofocus
                   class="block w-full h-12 px-4 rounded-xl border-gray-300 focus:border-court focus:ring-court">
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <button type="submit"
                class="w-full py-3.5 rounded-2xl bg-ink text-volt text-base font-extrabold hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-court focus:ring-offset-2">
            {{ __('Send reset link') }}
        </button>
    </form>

    <p class="mt-5 text-center text-sm">
        <a href="{{ route('login') }}" class="font-bold text-court hover:underline">&larr; {{ __('Back to log in') }}</a>
    </p>
</x-guest-layout>