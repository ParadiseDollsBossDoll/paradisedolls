@props(['value'])

<label {{ $attributes->merge(['class' => 'pd-label']) }}>
    {{ $value ?? $slot }}
</label>
