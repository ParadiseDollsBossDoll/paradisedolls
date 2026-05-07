<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Paradise Dolls') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased pd-dark-surface">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
            <div class="pointer-events-none absolute inset-0 opacity-40" style="background-image: radial-gradient(rgba(255,255,255,0.018) 1px, transparent 1px); background-size: 32px 32px;"></div>

            <div class="relative z-10 w-full max-w-md">
                <div class="mb-8 text-center">
                    <a href="{{ route('home') }}" class="text-[0.65rem] uppercase tracking-[0.28em] text-boss-gold/70 transition-colors hover:text-boss-gold">
                        {{ __('Back to site') }}
                    </a>
                    <h1 class="pd-heading pd-text-gradient mt-6 text-[2.1rem]">{{ config('app.name') }}</h1>
                    <p class="mt-2 text-[0.82rem] text-boss-ivory/35">{{ __('Member access and training portal') }}</p>
                </div>

                <div class="pd-panel p-6 sm:p-7">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
