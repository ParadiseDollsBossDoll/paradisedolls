@props([
    'transparentNav' => false,
    'title' => null,
    'description' => null,
    'keywords' => [],
])
@php
    try {
        $marketingUser = auth()->user();
    } catch (\Illuminate\Database\QueryException) {
        $marketingUser = null;
    }
@endphp
@php
    $canonicalBase = rtrim(config('app.url'), '/');
    $canonicalPath = request()->path();
    $canonicalUrl = $canonicalBase.($canonicalPath === '/' ? '' : '/'.$canonicalPath);
    $metaDescription = $description ?: 'Paradise Dolls is a luxury webcam model agency and training community for women building online income with professional support, onboarding, and education.';
    $metaKeywords = collect($keywords)->filter()->implode(', ');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' - '.config('app.name') : config('app.name') }}</title>
        <link rel="canonical" href="{{ $canonicalUrl }}">
        <meta name="description" content="{{ $metaDescription }}">
        @if ($metaKeywords !== '')
            <meta name="keywords" content="{{ $metaKeywords }}">
        @endif
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:title" content="{{ $title ? $title.' - '.config('app.name') : config('app.name') }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $title ? $title.' - '.config('app.name') : config('app.name') }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        @include('partials.google-tag-manager-head')
        @include('partials.google-analytics')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="font-sans antialiased text-boss-dark bg-white overflow-x-hidden"
        data-pd-translation-root
        x-data="{ scrolled: false, navOpen: false, transparent: {{ $transparentNav ? 'true' : 'false' }} }"
        @scroll.window="scrolled = (window.pageYOffset || document.documentElement.scrollTop) > 60"
    >
        @include('partials.google-tag-manager-body')

        <x-marketing-navbar :user="$marketingUser" />

        <main>
            {{ $slot }}
        </main>

        <x-marketing-footer :user="$marketingUser" />
    </body>
</html>
