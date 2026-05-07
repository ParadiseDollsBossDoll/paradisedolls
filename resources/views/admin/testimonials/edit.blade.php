<x-admin-layout>
    <div class="mx-auto max-w-3xl space-y-6 text-boss-ivory">
        <header class="flex items-center justify-between gap-4">
            <div>
                <p class="pd-kicker">{{ __('Success Stories') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(1.8rem,4vw,2.5rem)]">{{ __('Edit Story') }}</h1>
            </div>
            <a href="{{ route('admin.testimonials.index') }}" class="pd-btn-secondary">{{ __('Back') }}</a>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" class="pd-panel space-y-5 p-6">
            @csrf
            @method('PUT')
            @include('admin.testimonials.partials.form', ['testimonial' => $testimonial])
            <x-primary-button>{{ __('Save Story') }}</x-primary-button>
        </form>
    </div>
</x-admin-layout>
