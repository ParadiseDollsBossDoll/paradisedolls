<button {{ $attributes->merge(['type' => 'submit', 'class' => 'pd-btn-danger disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
