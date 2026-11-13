<x-app-layout>
    @php
        $kelasInput = 'block w-full h-12 px-4 rounded-xl border-gray-300 focus:border-court focus:ring-court';
    @endphp

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 space-y-5">
        <h1 class="text-2xl font-extrabold tracking-tight">{{ __('My account') }}</h1>

        {{-- Foto + detail profil --}}
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
              x-data="{ pratinjau: null, hapus: false }"
              class="bg-white border border-line rounded-3xl p-6 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <h2 class="text-lg font-extrabold">{{ __('Profile details') }}</h2>
                <p class="text-sm text-muted">{{ __('Update your name and email.') }}</p>
            </div>

            @if (session('status') === 'profile-updated')
                <p class="p-3 rounded-xl bg-court-soft text-court-dark text-sm font-semibold">{{ __('Saved.') }}</p>
            @endif

            {{-- Foto profil --}}
            <div class="flex flex-wrap items-center gap-4">
                <div class="h-20 w-20 shrink-0">
                    {{-- Pratinjau foto yang baru dipilih (belum disimpan) --}}
                    <img x-show="pratinjau" :src="pratinjau" alt="" class="h-20 w-20 rounded-full object-cover" style="display: none">

                    {{-- Foto saat ini, atau inisial kalau belum ada / akan dihapus --}}
                    <div x-show="! pratinjau">
                        @if ($user->fotoUrl())
                            <img x-show="! hapus" src="{{ $user->fotoUrl() }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-full object-cover">
                        @endif
                        <span @if ($user->fotoUrl()) x-show="hapus" style="display: none" @endif
                              class="h-20 w-20 rounded-full bg-ink text-volt text-2xl font-extrabold flex items-center justify-center">
                            {{ $user->initials() }}
                        </span>
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-semibold">{{ __('Profile photo') }}</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <label for="foto"
                               class="h-10 px-4 rounded-xl bg-ink text-volt text-sm font-bold inline-flex items-center cursor-pointer hover:brightness-110">
                            {{ __('Choose photo') }}
                        </label>
                        <input id="foto" x-ref="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                               @change="const file = $event.target.files[0]; pratinjau = file ? URL.createObjectURL(file) : null; hapus = false">

                        @if ($user->fotoUrl())
                            <button type="button" x-show="! hapus || pratinjau"
                                    @click="hapus = true; pratinjau = null; $refs.foto.value = ''"
                                    class="h-10 px-4 rounded-xl border border-red-200 text-red-700 text-sm font-bold hover:bg-red-50">
                                {{ __('Remove photo') }}
                            </button>
                        @endif
                    </div>
                    <p class="text-xs text-muted">{{ __('JPG, PNG or WEBP, max. 2 MB.') }}</p>
                    <input type="hidden" name="hapus_foto" :value="hapus ? 1 : 0">
                    <x-input-error :messages="$errors->get('foto')" />
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="name" class="block text-sm font-semibold">{{ __('Name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name"
                       class="{{ $kelasInput }}">
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-semibold">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                       class="{{ $kelasInput }}">
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <button type="submit" class="h-12 px-6 rounded-2xl bg-ink text-volt font-extrabold hover:brightness-110">
                {{ __('Save') }}
            </button>
        </form>

        {{-- Ganti kata sandi --}}
        <form method="POST" action="{{ route('password.update') }}"
              class="bg-white border border-line rounded-3xl p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-lg font-extrabold">{{ __('Change password') }}</h2>
                <p class="text-sm text-muted">{{ __("Use a long password that's hard to guess.") }}</p>
            </div>

            @if (session('status') === 'password-updated')
                <p class="p-3 rounded-xl bg-court-soft text-court-dark text-sm font-semibold">{{ __('Saved.') }}</p>
            @endif

            <div class="space-y-1.5">
                <label for="current_password" class="block text-sm font-semibold">{{ __('Current password') }}</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                       class="{{ $kelasInput }}">
                <x-input-error :messages="$errors->updatePassword->get('current_password')" />
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-semibold">{{ __('New password') }}</label>
                <input id="password" name="password" type="password" autocomplete="new-password"
                       class="{{ $kelasInput }}">
                <x-input-error :messages="$errors->updatePassword->get('password')" />
            </div>

            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-sm font-semibold">{{ __('Confirm password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                       class="{{ $kelasInput }}">
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
            </div>

            <button type="submit" class="h-12 px-6 rounded-2xl bg-ink text-volt font-extrabold hover:brightness-110">
                {{ __('Save') }}
            </button>
        </form>

        {{-- Keluar (penting di HP, karena menu akun ada di tab bawah) --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full h-12 rounded-2xl border border-red-200 bg-white text-red-700 font-bold hover:bg-red-50">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-app-layout>