@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-[#EEB4C3] px-1 pt-1 text-sm font-medium leading-5 text-[#EEB4C3] transition duration-150 ease-in-out focus:outline-none'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-boss-ivory/45 transition duration-150 ease-in-out hover:border-[#EEB4C3]/30 hover:text-[#EEB4C3] focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

