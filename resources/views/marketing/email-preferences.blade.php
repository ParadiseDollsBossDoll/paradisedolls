<x-guest-layout>
    <div class="mx-auto max-w-lg space-y-6 py-8 text-center">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-boss-rose">{{ __('Email preferences') }}</p>
            <h1 class="mt-3 font-display text-3xl text-boss-dark">{{ __('Paradise Dolls updates') }}</h1>
        </div>

        @if (session('status') || $user->marketing_unsubscribed_at)
            <div class="border border-green-500/20 bg-green-50 px-5 py-4 text-sm text-green-800">
                {{ session('status') ?: __('You are not subscribed to marketing emails.') }}
            </div>
        @else
            <form method="POST" action="{{ request()->fullUrl() }}" class="space-y-5">
                @csrf
                <p class="text-sm leading-relaxed text-boss-dark/65">{{ __('Unsubscribing will stop newsletters and promotional updates. Account, security, application, and onboarding emails will still be sent when needed.') }}</p>
                <x-danger-button type="submit">{{ __('Unsubscribe from marketing emails') }}</x-danger-button>
            </form>
        @endif
    </div>
</x-guest-layout>
