@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-boss-gold bg-boss-gold/10 py-2 ps-3 pe-4 text-start text-base font-medium text-boss-gold transition duration-150 ease-in-out focus:outline-none'
            : 'block w-full border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-boss-ivory/48 transition duration-150 ease-in-out hover:border-boss-gold/30 hover:bg-white/[0.03] hover:text-boss-gold focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
