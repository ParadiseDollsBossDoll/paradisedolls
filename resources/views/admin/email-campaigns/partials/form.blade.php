<section class="pd-panel space-y-5 p-5 sm:p-6">
    <div>
        <p class="pd-kicker">{{ __('Setup') }}</p>
        <h2 class="pd-heading mt-1 text-xl">{{ __('Campaign Details') }}</h2>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="name" :value="__('Campaign name')" />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-2"
                :value="old('name', $campaign->name)"
                placeholder="{{ __('e.g. July Motivation Update') }}"
                required
                maxlength="255"
            />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="audience" :value="__('Recipients')" />
            <select id="audience" name="audience" class="pd-input mt-2" required x-model="audience">
                @foreach (\App\Models\EmailCampaign::audienceOptions() as $value => $label)
                    <option value="{{ $value }}" @selected(old('audience', $campaign->audience) === $value)>
                        {{ $label }} ({{ number_format($audienceCounts[$value] ?? 0) }})
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('audience')" />
        </div>
    </div>

    <div>
        <x-input-label for="subject" :value="__('Email subject line')" />
        <x-text-input
            id="subject"
            name="subject"
            type="text"
            class="mt-2"
            :value="old('subject', $campaign->subject)"
            placeholder="{{ __('e.g. Your weekly Paradise Dolls update') }}"
            required
            maxlength="255"
        />
        <x-input-error class="mt-2" :messages="$errors->get('subject')" />
    </div>
</section>

<section class="pd-panel space-y-5 p-5 sm:p-6">
    <div>
        <p class="pd-kicker">{{ __('Message') }}</p>
        <h2 class="pd-heading mt-1 text-xl">{{ __('Email Content') }}</h2>
    </div>

    <div>
        <x-input-label for="body" :value="__('Message body')" />
        <textarea
            id="body"
            name="body"
            rows="12"
            class="pd-input mt-2 min-h-64 resize-y leading-relaxed"
            placeholder="{{ __('Write your message here...') }}"
            required
            maxlength="20000"
        >{{ old('body', $campaign->body) }}</textarea>
        <p class="mt-2 text-[0.7rem] text-boss-ivory/40">
            {{ __('Use') }} <code class="rounded bg-boss-gold/10 px-1.5 py-0.5 text-boss-gold">{name}</code>
            {{ __('to personalize the email with each recipient\'s name.') }}
        </p>
        <x-input-error class="mt-2" :messages="$errors->get('body')" />
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="action_label" :value="__('Button label (optional)')" />
            <x-text-input
                id="action_label"
                name="action_label"
                type="text"
                class="mt-2"
                :value="old('action_label', $campaign->action_label)"
                placeholder="{{ __('e.g. Open Your Dashboard') }}"
                maxlength="80"
            />
            <x-input-error class="mt-2" :messages="$errors->get('action_label')" />
        </div>

        <div>
            <x-input-label for="action_url" :value="__('Button link (optional)')" />
            <x-text-input
                id="action_url"
                name="action_url"
                type="url"
                class="mt-2"
                :value="old('action_url', $campaign->action_url)"
                placeholder="https://"
                maxlength="2000"
            />
            <x-input-error class="mt-2" :messages="$errors->get('action_url')" />
        </div>
    </div>
</section>
