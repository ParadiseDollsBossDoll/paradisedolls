@props([
    'variant' => 'horizontal_pink',
    'alt' => null,
])

@php
    $logos = config('brand.logos');
    $logo = $logos[$variant] ?? $logos['horizontal_pink'];
@endphp

<img
    src="{{ asset($logo) }}"
    alt="{{ $alt ?? config('brand.name') }}"
    {{ $attributes->merge(['class' => 'h-auto object-contain']) }}
>
