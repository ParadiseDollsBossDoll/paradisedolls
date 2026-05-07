<div class="grid gap-5 md:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Member name')" />
        <x-text-input id="name" name="name" type="text" class="mt-2" :value="old('name', $testimonial?->name)" required />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="location" :value="__('Location')" />
        <x-text-input id="location" name="location" type="text" class="mt-2" :value="old('location', $testimonial?->location)" placeholder="{{ __('London / Remote / Bali') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('location')" />
    </div>
</div>

<div>
    <x-input-label for="headline" :value="__('Headline')" />
    <x-text-input id="headline" name="headline" type="text" class="mt-2" :value="old('headline', $testimonial?->headline)" required placeholder="{{ __('From beginner to confident online earner') }}" />
    <x-input-error class="mt-2" :messages="$errors->get('headline')" />
</div>

<div>
    <x-input-label for="quote" :value="__('Quote')" />
    <textarea id="quote" name="quote" rows="5" class="pd-input mt-2" required>{{ old('quote', $testimonial?->quote) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('quote')" />
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <x-input-label for="result_label" :value="__('Result label')" />
        <x-text-input id="result_label" name="result_label" type="text" class="mt-2" :value="old('result_label', $testimonial?->result_label)" placeholder="{{ __('Remote income / Confidence / Travel') }}" />
        <x-input-error class="mt-2" :messages="$errors->get('result_label')" />
    </div>

    <div>
        <x-input-label for="sort_order" :value="__('Sort order')" />
        <x-text-input id="sort_order" name="sort_order" type="number" class="mt-2" :value="old('sort_order', $testimonial?->sort_order ?? 0)" />
        <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
    </div>
</div>

<div>
    <x-input-label for="image_url" :value="__('Image URL')" />
    <x-text-input id="image_url" name="image_url" type="text" class="mt-2" :value="old('image_url', $testimonial?->image_url)" placeholder="https://..." />
    <x-input-error class="mt-2" :messages="$errors->get('image_url')" />
</div>

<label for="is_published" class="flex items-start gap-3 rounded-xl border border-white/[0.06] bg-white/[0.03] p-4">
    <input id="is_published" name="is_published" type="checkbox" value="1" class="mt-1 rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold" @checked(old('is_published', $testimonial?->is_published ?? false))>
    <span>
        <span class="block text-[0.85rem] text-boss-ivory">{{ __('Published') }}</span>
        <span class="mt-1 block text-[0.72rem] text-boss-ivory/32">{{ __('Visible on the homepage and success stories page.') }}</span>
    </span>
</label>
