@php $siteTheme = \App\Models\SiteSetting::get('theme', ['mode'=>'light','primary'=>'#FF0080','primaryLight'=>'#FFE6F0','preset'=>'boss-light']); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('brand.name') }}</title>

        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset(config('brand.favicon')) }}">

        @include('partials.google-tag-manager-head')
        @include('partials.google-analytics')

        @include('partials.theme-bootstrap', ['siteTheme' => $siteTheme])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="pd-premium-ui font-sans antialiased pd-dark-surface" data-pd-translation-root>
        @include('partials.google-tag-manager-body')

        <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
            <div class="pointer-events-none absolute inset-0 opacity-40" style="background-image: radial-gradient(rgba(255,255,255,0.018) 1px, transparent 1px); background-size: 32px 32px;"></div>

            <div class="absolute right-4 top-4 z-20 sm:right-6 sm:top-6">
                <x-language-selector tone="dark" />
            </div>

            <div class="relative z-10 w-full max-w-md">
                <div class="mb-8 text-center">
                    <a href="{{ route('home') }}" class="text-[0.65rem] uppercase tracking-[0.28em] text-boss-gold/70 transition-colors hover:text-boss-gold">
                        {{ __('Back to site') }}
                    </a>
                    <x-brand-logo :variant="($siteTheme['mode'] ?? 'light') === 'light' ? 'horizontal_pink' : 'horizontal_white'" class="mx-auto mt-6 w-[220px] max-w-full sm:w-[250px]" />
                    <p class="mt-2 text-[0.82rem] text-boss-ivory/35">{{ __('Member access and training portal') }}</p>
                </div>

                <div class="pd-panel p-6 sm:p-7">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
