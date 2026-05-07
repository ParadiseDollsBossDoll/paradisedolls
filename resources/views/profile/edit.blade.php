<x-dynamic-component :component="auth()->user()->isAdmin() ? 'admin-layout' : 'member-layout'">
    <div class="mx-auto max-w-3xl space-y-6 text-boss-ivory">
        <header>
            <p class="pd-kicker">{{ __('Account') }}</p>
            <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.6rem)]">{{ __('Profile') }}</h1>
        </header>

        <div class="pd-panel p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="pd-panel p-6 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="rounded-2xl border border-red-400/15 bg-red-400/[0.04] p-6 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-dynamic-component>
