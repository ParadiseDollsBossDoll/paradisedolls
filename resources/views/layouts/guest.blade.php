@php $siteTheme = \App\Models\SiteSetting::get('theme', ['mode'=>'dark','primary'=>'#EEB4C3','primaryLight'=>'#F3C3CF']); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Paradise Dolls') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <script>(function(){function hRgb(h){return parseInt(h.slice(1,3),16)+' '+parseInt(h.slice(3,5),16)+' '+parseInt(h.slice(5,7),16);}function lum(h){var r=parseInt(h.slice(1,3),16)/255,g=parseInt(h.slice(3,5),16)/255,b=parseInt(h.slice(5,7),16)/255;return 0.2126*r+0.7152*g+0.0722*b;}function applyVars(s){var h=document.documentElement;h.classList.toggle('light-mode',s.mode==='light');if(s.primary){var p=s.primary,pl=s.primaryLight||p;h.style.setProperty('--pd-primary',p);h.style.setProperty('--pd-gold',p);h.style.setProperty('--pd-gold-rgb',hRgb(p));h.style.setProperty('--pd-gold-light-rgb',hRgb(pl));h.style.setProperty('--pd-gold-hover-rgb',hRgb(pl));h.style.setProperty('--pd-primary-hover',pl);h.style.setProperty('--pd-gold-light',pl);h.style.setProperty('--pd-primary-on',lum(p)>0.35?'#09070A':'#FFF8F6');}}try{applyVars(@json($siteTheme));}catch(e){applyVars({mode:'dark',primary:'#EEB4C3',primaryLight:'#F3C3CF'});}window.pdApplyTheme=function(s){applyVars(s);};}());</script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased pd-dark-surface" data-pd-translation-root>
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
                    <img
                        src="{{ asset('images/brand/get-rich-with-paradise-dolls-logo.png') }}"
                        alt="{{ config('app.name') }}"
                        class="mx-auto mt-6 h-auto w-[210px] object-contain sm:w-[240px]"
                    >
                    <p class="mt-2 text-[0.82rem] text-boss-ivory/35">{{ __('Member access and training portal') }}</p>
                </div>

                <div class="pd-panel p-6 sm:p-7">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
