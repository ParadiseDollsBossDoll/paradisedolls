<button {{ $attributes->merge(['type' => 'submit', 'class' => 'pd-btn-primary disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
