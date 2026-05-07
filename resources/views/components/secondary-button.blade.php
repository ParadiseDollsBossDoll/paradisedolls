<button {{ $attributes->merge(['type' => 'button', 'class' => 'pd-btn-secondary disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
