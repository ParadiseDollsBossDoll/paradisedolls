@php
    $selectedAudience = old('audience', $campaign->audience);
    $selectedDeliveryMode = old('delivery_mode', 'draft');
    $selectedRepeatPreset = old('repeat_preset', $campaign->repeatPreset());
    $selectedScheduleDate = old('schedule_date');
    $selectedScheduleTime = old('schedule_time');
    $campaignTimezone = \App\Models\EmailCampaign::schedulingTimezone();
@endphp

<x-admin-layout>
    <div class="pd-email-campaigns mx-auto max-w-7xl space-y-6 text-boss-ivory">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Email Campaigns') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(1.9rem,4vw,2.6rem)]">{{ __('New Campaign') }}</h1>
            </div>
            <a href="{{ route('admin.email-campaigns.index') }}" class="pd-btn-secondary w-full shrink-0 sm:w-auto">{{ __('Back') }}</a>
        </header>

        @if ($errors->any())
            <div class="rounded-lg border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">
                {{ __('Please review the highlighted campaign fields.') }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.email-campaigns.store') }}"
            class="grid items-start gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]"
            x-data="{
                audience: @js($selectedAudience),
                deliveryMode: @js($selectedDeliveryMode),
                repeatPreset: @js($selectedRepeatPreset),
                scheduleDate: @js($selectedScheduleDate),
                scheduleTime: @js($selectedScheduleTime),
                audienceCounts: @js($audienceCounts)
            }"
        >
            @csrf

            <div class="space-y-5">
                @include('admin.email-campaigns.partials.form')
            </div>

            <aside class="space-y-5 xl:sticky xl:top-6">
                @include('admin.email-campaigns.partials.schedule', [
                    'includeDeliveryMode' => true,
                    'defaultDeliveryMode' => 'draft',
                    'useParentState' => true,
                ])

                <section class="rounded-lg border border-dashed border-boss-gold/25 bg-boss-gold/[0.035] p-5">
                    <p class="pd-kicker">{{ __('Campaign Summary') }}</p>
                    <dl class="mt-4 space-y-3 text-xs">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-boss-ivory/40">{{ __('Recipients') }}</dt>
                            <dd
                                class="text-right font-medium text-boss-ivory/75"
                                x-text="({ all_models: @js(__('All models')), onboarded_models: @js(__('Fully onboarded models')), not_onboarded_models: @js(__('Models not fully onboarded')) })[audience] || @js(__('All models'))"
                            ></dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-boss-ivory/40">{{ __('Estimated audience') }}</dt>
                            <dd class="font-medium text-boss-ivory/75" x-text="Number(audienceCounts[audience] || 0).toLocaleString()"></dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-boss-ivory/40">{{ __('Delivery') }}</dt>
                            <dd
                                class="text-right font-medium text-boss-ivory/75"
                                x-text="deliveryMode === 'send_now' ? @js(__('Send immediately')) : (deliveryMode === 'schedule' ? @js(__('Schedule for later')) : @js(__('Save as draft')))"
                            ></dd>
                        </div>
                        <div class="flex items-start justify-between gap-4" x-show="deliveryMode === 'schedule'" x-cloak>
                            <dt class="text-boss-ivory/40">{{ __('Scheduled') }}</dt>
                            <dd
                                class="text-right font-medium text-boss-ivory/75"
                                x-text="scheduleDate ? scheduleDate + (scheduleTime ? ' at ' + scheduleTime : '') + @js(' UK time') : @js(__('Not selected'))"
                            ></dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-boss-ivory/40">{{ __('Repeat') }}</dt>
                            <dd
                                class="text-right font-medium capitalize text-boss-ivory/75"
                                x-text="repeatPreset === 'none' ? @js(__('One-time send')) : repeatPreset.replace('_', ' ')"
                            ></dd>
                        </div>
                    </dl>
                    <p class="mt-4 border-t border-white/[0.06] pt-3 text-[0.68rem] leading-relaxed text-boss-ivory/40">
                        {{ __('Schedules use UK time (:timezone).', ['timezone' => $campaignTimezone]) }}
                    </p>
                </section>
            </aside>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="submit" class="pd-btn-primary w-full sm:w-auto">
                    <span
                        x-text="deliveryMode === 'send_now' ? @js(__('Send Campaign')) : (deliveryMode === 'schedule' ? @js(__('Schedule Campaign')) : @js(__('Save as Draft')))"
                    >{{ __('Save as Draft') }}</span>
                </button>
                <a href="{{ route('admin.email-campaigns.index') }}" class="pd-btn-secondary w-full sm:w-auto">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-admin-layout>
