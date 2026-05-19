@props([
    'transparentNav' => false,
    'title' => null,
])
@php
    try {
        $marketingUser = auth()->user();
    } catch (\Illuminate\Database\QueryException) {
        $marketingUser = null;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' - '.config('app.name') : config('app.name') }}</title>

        <link rel="icon" type="image/png" href="/favicon.png">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="font-sans antialiased text-boss-dark bg-white overflow-x-hidden"
        x-data="{ scrolled: false, navOpen: false, transparent: {{ $transparentNav ? 'true' : 'false' }} }"
        @scroll.window="scrolled = (window.pageYOffset || document.documentElement.scrollTop) > 60"
    >
        <x-marketing-navbar :user="$marketingUser" />

        <main>
            {{ $slot }}
        </main>

        <x-marketing-footer :user="$marketingUser" />
    </body>
</html>
