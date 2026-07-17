@php
    $includeDeliveryMode = $includeDeliveryMode ?? false;
    $defaultDeliveryMode = $defaultDeliveryMode ?? 'draft';
    $useParentState = $useParentState ?? false;
    $selectedDeliveryMode = old('delivery_mode', $defaultDeliveryMode);
    $selectedRepeatPreset = old('repeat_preset', $campaign->repeatPreset());
    $customRepeatDays = old('repeat_every_days', $campaign->repeat_every_days);
    $campaignTimezone = \App\Models\EmailCampaign::schedulingTimezone();
    $campaignTimezoneAbbreviation = now($campaignTimezone)->format('T');
    $scheduledAtForAdmin = $campaign->nextSendAtForAdmin();
    $scheduleDate = old('schedule_date', $scheduledAtForAdmin?->format('Y-m-d'));
    $scheduleTime = old('schedule_time', $scheduledAtForAdmin?->format('H:i'));
@endphp

<section
    class="pd-panel space-y-5 p-5"
    @unless ($useParentState)
        x-data="{
            deliveryMode: @js($selectedDeliveryMode),
            repeatPreset: @js($selectedRepeatPreset),
            scheduleDate: @js($scheduleDate),
            scheduleTime: @js($scheduleTime)
        }"
    @endunless
>
    <div>
        <p class="pd-kicker">{{ __('Schedule') }}</p>
        <h2 class="pd-heading mt-1 text-xl">{{ __('Automatic Sending') }}</h2>
        <p class="mt-2 text-xs leading-relaxed text-boss-ivory/45">
            {{ __('Schedule campaigns ahead of time. The server checks for due messages every minute.') }}
        </p>
    </div>

    @if (! $includeDeliveryMode && $campaign->next_send_at)
        <div class="rounded-lg border border-boss-gold/20 bg-boss-gold/10 px-3 py-2 text-xs text-boss-gold">
            {{ $campaign->repeatLabel() }}
        </div>
    @endif

    @if ($includeDeliveryMode)
        <div>
            <x-input-label for="delivery_mode" :value="__('Delivery')" />
            <select id="delivery_mode" name="delivery_mode" class="pd-input mt-2" required x-model="deliveryMode">
                <option value="draft" @selected($selectedDeliveryMode === 'draft')>{{ __('Save as draft') }}</option>
                <option value="send_now" @selected($selectedDeliveryMode === 'send_now')>{{ __('Send immediately') }}</option>
                <option value="schedule" @selected($selectedDeliveryMode === 'schedule')>{{ __('Schedule for later') }}</option>
            </select>
            <p class="mt-2 text-[0.68rem] text-boss-ivory/35" x-show="deliveryMode === 'draft'">
                {{ __('Drafts do not send until you schedule or send them manually.') }}
            </p>
            <p class="mt-2 text-[0.68rem] text-boss-ivory/35" x-show="deliveryMode === 'send_now'" x-cloak>
                {{ __('The campaign will be queued as soon as you create it.') }}
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('delivery_mode')" />
        </div>
    @endif

    <div
        class="space-y-4 rounded-lg border border-boss-gold/15 bg-boss-gold/[0.035] p-4"
        @if ($includeDeliveryMode) x-show="deliveryMode === 'schedule'" x-cloak @endif
    >
        <div>
            <p class="text-xs font-semibold text-boss-ivory/75">{{ __('Choose delivery date and time') }}</p>
            <p class="mt-1 text-[0.68rem] leading-relaxed text-boss-ivory/35">
                {{ $includeDeliveryMode
                    ? __('Select when the first scheduled delivery should begin.')
                    : __('Select the next future date and time for this campaign.') }}
            </p>
        </div>

        <div class="flex items-center justify-between gap-3 rounded-md border border-boss-gold/15 bg-boss-gold/[0.06] px-3 py-2 text-[0.68rem]">
            <span class="font-semibold text-boss-gold">{{ __('UK time') }}</span>
            <span class="text-right text-boss-ivory/50">{{ $campaignTimezone }} &middot; {{ $campaignTimezoneAbbreviation }}</span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
            <div>
                <x-input-label for="schedule_date" :value="__('Send date')" />
                <input
                    id="schedule_date"
                    name="schedule_date"
                    type="date"
                    class="pd-input pd-campaign-datetime-input mt-2"
                    min="{{ now()->format('Y-m-d') }}"
                    value="{{ $scheduleDate }}"
                    x-model="scheduleDate"
                    @if ($includeDeliveryMode)
                        x-bind:required="deliveryMode === 'schedule'"
                    @else
                        required
                    @endif
                />
                <x-input-error class="mt-2" :messages="$errors->get('schedule_date')" />
            </div>

            <div>
                <x-input-label for="schedule_time" :value="__('Send time')" />
                <input
                    id="schedule_time"
                    name="schedule_time"
                    type="time"
                    class="pd-input pd-campaign-datetime-input mt-2"
                    step="60"
                    value="{{ $scheduleTime }}"
                    x-model="scheduleTime"
                    @if ($includeDeliveryMode)
                        x-bind:required="deliveryMode === 'schedule'"
                    @else
                        required
                    @endif
                />
                <x-input-error class="mt-2" :messages="$errors->get('schedule_time')" />
            </div>
        </div>

        <x-input-error class="mt-2" :messages="$errors->get('scheduled_for')" />
    </div>

    <div>
        <x-input-label for="repeat_preset" :value="__('Repeat')" />
        <select id="repeat_preset" name="repeat_preset" class="pd-input mt-2" x-model="repeatPreset">
            @foreach (\App\Models\EmailCampaign::repeatPresetOptions() as $value => $label)
                <option value="{{ $value }}" @selected($selectedRepeatPreset === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="mt-2 text-[0.68rem] text-boss-ivory/35">
            {{ __('Choose a one-time send or an automatic recurring schedule.') }}
        </p>
        <x-input-error class="mt-2" :messages="$errors->get('repeat_preset')" />
    </div>

    <div x-show="repeatPreset === 'custom'" x-cloak>
        <x-input-label for="repeat_every_days" :value="__('Custom repeat days')" />
        <x-text-input
            id="repeat_every_days"
            name="repeat_every_days"
            type="number"
            min="1"
            max="365"
            class="mt-2"
            :value="$customRepeatDays"
            x-bind:required="repeatPreset === 'custom'"
        />
        <p class="mt-2 text-[0.68rem] text-boss-ivory/35">
            {{ __('Enter how many days to wait between sends.') }}
        </p>
        <x-input-error class="mt-2" :messages="$errors->get('repeat_every_days')" />
    </div>
</section>
