@props([
    'transparentNav' => false,
    'title' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' - '.config('app.name') : config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="font-sans antialiased text-boss-dark bg-white overflow-x-hidden"
        x-data="{ scrolled: false, navOpen: false, transparent: {{ $transparentNav ? 'true' : 'false' }} }"
        @scroll.window="scrolled = (window.pageYOffset || document.documentElement.scrollTop) > 60"
    >
        <x-marketing-navbar />

        <main>
            {{ $slot }}
        </main>

        <x-marketing-footer />
    </body>
</html>
