<div class="grid gap-5 md:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Campaign name')" />
        <x-text-input id="name" name="name" type="text" class="mt-2" :value="old('name', $campaign->name)" required maxlength="255" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="audience" :value="__('Recipients')" />
        <select id="audience" name="audience" class="pd-input mt-2" required>
            @foreach (\App\Models\EmailCampaign::audienceOptions() as $value => $label)
                <option value="{{ $value }}" @selected(old('audience', $campaign->audience) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('audience')" />
    </div>
</div>

<div>
    <x-input-label for="subject" :value="__('Email subject')" />
    <x-text-input id="subject" name="subject" type="text" class="mt-2" :value="old('subject', $campaign->subject)" required maxlength="255" />
    <x-input-error class="mt-2" :messages="$errors->get('subject')" />
</div>

<div>
    <x-input-label for="body" :value="__('Email message')" />
    <textarea id="body" name="body" rows="12" class="pd-input mt-2 resize-y" required maxlength="20000">{{ old('body', $campaign->body) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('body')" />
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <x-input-label for="action_label" :value="__('Button label (optional)')" />
        <x-text-input id="action_label" name="action_label" type="text" class="mt-2" :value="old('action_label', $campaign->action_label)" maxlength="80" />
        <x-input-error class="mt-2" :messages="$errors->get('action_label')" />
    </div>

    <div>
        <x-input-label for="action_url" :value="__('Button link (optional)')" />
        <x-text-input id="action_url" name="action_url" type="url" class="mt-2" :value="old('action_url', $campaign->action_url)" placeholder="https://" maxlength="2000" />
        <x-input-error class="mt-2" :messages="$errors->get('action_url')" />
    </div>
</div>

<div class="max-w-xs">
    <x-input-label for="repeat_every_days" :value="__('Repeat every (days)')" />
    <x-text-input id="repeat_every_days" name="repeat_every_days" type="number" min="1" max="365" class="mt-2" :value="old('repeat_every_days', $campaign->repeat_every_days)" />
    <x-input-error class="mt-2" :messages="$errors->get('repeat_every_days')" />
</div>
