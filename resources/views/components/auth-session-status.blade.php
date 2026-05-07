@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-green-400/20 bg-green-400/10 p-3 font-medium text-sm text-green-200']) }}>
        {{ $status }}
    </div>
@endif
