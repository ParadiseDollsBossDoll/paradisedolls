<x-member-layout>
    <div class="mx-auto max-w-4xl">
        <div class="pd-panel p-8">
            <p class="pd-kicker">{{ __('Dashboard') }}</p>
            <h1 class="pd-heading pd-text-gradient mt-2 text-[clamp(2rem,4vw,2.7rem)]">{{ __("You're logged in!") }}</h1>
            <p class="mt-4 text-boss-ivory/42">{{ __('Head to the academy to continue your training.') }}</p>
            <a href="{{ route('member.courses.index') }}" class="pd-btn-primary mt-6">{{ __('Browse Courses') }}</a>
        </div>
    </div>
</x-member-layout>
