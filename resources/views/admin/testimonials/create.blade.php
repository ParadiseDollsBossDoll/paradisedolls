<x-admin-layout>
    <div class="mx-auto max-w-3xl space-y-6 text-boss-ivory">
        <header class="flex items-center justify-between gap-4">
            <div>
                <p class="pd-kicker">{{ __('Success Stories') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(1.8rem,4vw,2.5rem)]">{{ __('New Story') }}</h1>
            </div>
            <a href="{{ route('admin.testimonials.index') }}" class="pd-btn-secondary">{{ __('Back') }}</a>
        </header>

        <form method="POST" action="{{ route('admin.testimonials.store') }}" class="pd-panel space-y-5 p-6">
            @csrf
            @include('admin.testimonials.partials.form', ['testimonial' => null])
            <x-primary-button>{{ __('Create Story') }}</x-primary-button>
        </form>
    </div>
</x-admin-layout>
