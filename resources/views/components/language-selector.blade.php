@props([
    'tone' => 'light',
])

@php
    $toneClasses = match ($tone) {
        'dark' => 'border-white/12 bg-white/[0.06] text-boss-ivory/72 hover:border-boss-gold/35 hover:text-boss-gold',
        'plain' => '',
        default => 'border-boss-rose/25 bg-white/80 text-boss-dark hover:border-boss-rose/45',
    };

    $initialLanguages = [
        ['code' => 'en', 'name' => __('English'), 'flagCountry' => 'gb'],
        ['code' => 'th', 'name' => __('Thai'), 'flagCountry' => 'th'],
        ['code' => 'pt', 'name' => __('Portuguese'), 'flagCountry' => 'br'],
    ];
@endphp

@once
    <script>
        window.ParadiseTranslatorConfig = {
            languagesUrl: @json(route('translation.languages')),
            translateUrl: @json(route('translation.translate')),
            csrfToken: @json(csrf_token()),
            defaultLanguage: 'en',
            priority: ['en', 'th', 'pt']
        };
    </script>
@endonce

<div
    data-pd-language-selector
    data-translate-ignore
    {{ $attributes->class([
        'relative inline-flex h-9 w-[4.7rem] items-center rounded-md border transition-colors',
        $toneClasses,
    ]) }}
>
    <button
        type="button"
        data-pd-language-button
        class="flex h-full w-full items-center justify-center gap-1.5 rounded-md px-2 text-[0.62rem] font-semibold uppercase tracking-[0.1em] outline-none transition hover:bg-current/5 focus-visible:ring-2 focus-visible:ring-[#EEB4C3]/45"
        aria-haspopup="listbox"
        aria-expanded="false"
        aria-label="{{ __('Language') }}"
        title="{{ __('Language') }}"
    >
        <img
            data-pd-language-flag
            src="https://flagcdn.com/w40/gb.png"
            alt=""
            class="h-3.5 w-5 shrink-0 rounded-[2px] object-cover shadow-sm"
            aria-hidden="true"
        >
        <span data-pd-language-code>EN</span>
        <svg class="h-2.5 w-2.5 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M4 6.25 8 10l4-3.75" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <div
        data-pd-language-menu
        role="listbox"
        aria-label="{{ __('Language') }}"
        class="absolute right-0 top-full z-[80] mt-2 hidden max-h-72 w-60 overflow-y-auto rounded-md border border-boss-rose/20 bg-white py-2 text-boss-dark shadow-2xl shadow-black/15"
    >
        @foreach ($initialLanguages as $language)
            <button
                type="button"
                role="option"
                data-pd-language-option
                data-value="{{ $language['code'] }}"
                data-name="{{ $language['name'] }}"
                data-flag-country="{{ $language['flagCountry'] }}"
                data-flag-url="https://flagcdn.com/w40/{{ $language['flagCountry'] }}.png"
                class="flex w-full items-center gap-3 px-3 py-2 text-left text-[0.78rem] transition hover:bg-boss-muted"
            >
                <img
                    src="https://flagcdn.com/w40/{{ $language['flagCountry'] }}.png"
                    alt=""
                    class="h-3.5 w-5 shrink-0 rounded-[2px] object-cover shadow-sm"
                    aria-hidden="true"
                    loading="lazy"
                >
                <span class="min-w-0 flex-1 truncate">{{ $language['name'] }}</span>
                <span class="text-[0.62rem] font-semibold uppercase tracking-[0.12em] text-boss-dark/35">{{ strtoupper($language['code']) }}</span>
            </button>
        @endforeach
    </div>
</div>
