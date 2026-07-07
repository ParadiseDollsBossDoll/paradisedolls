<x-admin-layout>
    <div class="mx-auto max-w-4xl space-y-6 text-boss-ivory">
        <header class="flex items-center justify-between gap-4">
            <div>
                <p class="pd-kicker">{{ __('Email Campaigns') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(1.8rem,4vw,2.5rem)]">{{ __('New Campaign') }}</h1>
            </div>
            <a href="{{ route('admin.email-campaigns.index') }}" class="pd-btn-secondary">{{ __('Back') }}</a>
        </header>

        <form method="POST" action="{{ route('admin.email-campaigns.store') }}" class="pd-panel space-y-6 p-6">
            @csrf
            @include('admin.email-campaigns.partials.form')

            <div class="grid gap-5 border-t border-white/[0.06] pt-6 md:grid-cols-2">
                <div>
                    <x-input-label for="delivery_mode" :value="__('Delivery')" />
                    <select id="delivery_mode" name="delivery_mode" class="pd-input mt-2" required>
                        <option value="draft" @selected(old('delivery_mode') === 'draft')>{{ __('Save as draft') }}</option>
                        <option value="send_now" @selected(old('delivery_mode') === 'send_now')>{{ __('Send now') }}</option>
                        <option value="schedule" @selected(old('delivery_mode') === 'schedule')>{{ __('Schedule') }}</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('delivery_mode')" />
                </div>
                <div>
                    <x-input-label for="scheduled_for" :value="__('Scheduled date and time')" />
                    <x-text-input id="scheduled_for" name="scheduled_for" type="datetime-local" class="mt-2" :value="old('scheduled_for')" />
                    <x-input-error class="mt-2" :messages="$errors->get('scheduled_for')" />
                </div>
            </div>

            <x-primary-button>{{ __('Create Campaign') }}</x-primary-button>
        </form>
    </div>
</x-admin-layout>
