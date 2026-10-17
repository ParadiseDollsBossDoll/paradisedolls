@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-[#FF0080] px-1 pt-1 text-sm font-medium leading-5 text-[#FF0080] transition duration-150 ease-in-out focus:outline-none'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-boss-ivory/45 transition duration-150 ease-in-out hover:border-[#FF0080]/30 hover:text-[#FF0080] focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

