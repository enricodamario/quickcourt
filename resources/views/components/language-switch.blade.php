@props(['dark' => false])

<div {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full p-1 text-xs font-bold ' . ($dark ? 'bg-court-dark/60 border border-white/20' : 'bg-ground border border-line')]) }}
     role="group" aria-label="{{ __('Language') }}">
    @foreach (['en' => 'EN', 'id' => 'ID'] as $kode => $label)
        <a href="{{ route('language.switch', $kode) }}"
           @if (app()->getLocale() === $kode) aria-current="true" @endif
           @class([
               'h-8 min-w-10 px-3 rounded-full inline-flex items-center justify-center',
               'bg-volt text-ink' => app()->getLocale() === $kode,
               'text-white/80 hover:text-white' => $dark && app()->getLocale() !== $kode,
               'text-muted hover:text-ink' => ! $dark && app()->getLocale() !== $kode,
           ])>
            {{ $label }}
        </a>
    @endforeach
</div>