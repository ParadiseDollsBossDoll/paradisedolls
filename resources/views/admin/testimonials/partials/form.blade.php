<div class="rounded-xl border border-boss-rose/15 bg-boss-rose/8 p-4 text-[0.78rem] leading-relaxed text-boss-ivory/62">
    {{ __('This matches the member success story form. Public cards show the display name, handle, story text, hashtag, and avatar/photo.') }}
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Display name')" />
        <x-text-input id="name" name="name" type="text" class="mt-2" :value="old('name', $testimonial?->name)" required placeholder="{{ __('Kayla / Amber / Paradise Doll') }}" />
        <p class="mt-1.5 text-[0.62rem] leading-relaxed text-boss-ivory/25">{{ __('Shown above the handle on the success story card.') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="display_handle" :value="__('Display handle')" />
        <x-text-input id="display_handle" name="display_handle" type="text" class="mt-2" :value="old('display_handle', $testimonial?->display_handle ? '@'.$testimonial->display_handle : null)" required placeholder="@neljhanredondo" />
        <p class="mt-1.5 text-[0.62rem] leading-relaxed text-boss-ivory/25">{{ __('Shown under the display name. Use letters, numbers, underscores, or periods.') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('display_handle')" />
    </div>
</div>

<div>
    <x-input-label for="quote" :value="__('Success story text')" />
    <textarea id="quote" name="quote" rows="6" class="pd-input mt-2" required maxlength="700" placeholder="{{ __('Write the success story text shown on the landing page card.') }}">{{ old('quote', $testimonial?->quote) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('quote')" />
</div>

<div>
    <x-input-label for="result_label" :value="__('Hashtag')" />
    <x-text-input id="result_label" name="result_label" type="text" class="mt-2" :value="old('result_label', $testimonial?->result_label)" required placeholder="{{ __('Community Support') }}" />
    <p class="mt-1.5 text-[0.62rem] leading-relaxed text-boss-ivory/25">{{ __('This appears as the hashtag. You can type it with or without #.') }}</p>
    <x-input-error class="mt-2" :messages="$errors->get('result_label')" />
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <x-input-label for="photo" :value="__('Story photo')" />
        @if ($testimonial && ($testimonial->image_path || $testimonial->image_url || $testimonial->submitter?->profile_photo_path))
            <div class="mt-2 flex items-center gap-3 rounded-xl border border-white/[0.06] bg-white/[0.03] p-3">
                <img src="{{ $testimonial->displayAvatar() }}" alt="" class="h-14 w-14 rounded-full object-cover">
                <p class="text-[0.72rem] leading-relaxed text-boss-ivory/35">{{ __('Upload a new photo to replace the current one.') }}</p>
            </div>
        @endif
        <input id="photo" name="photo" type="file" accept="image/*" class="mt-2 block w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-[0.82rem] text-boss-ivory/70 file:mr-4 file:rounded-full file:border-0 file:bg-boss-rose/20 file:px-4 file:py-2 file:text-[0.72rem] file:font-semibold file:text-boss-rose hover:file:bg-boss-rose/30">
        <p class="mt-1.5 text-[0.62rem] leading-relaxed text-boss-ivory/25">{{ __('Optional for admin-created stories. Upload JPG, PNG, or WEBP up to 5MB.') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('photo')" />
    </div>

    <div>
        <x-input-label for="sort_order" :value="__('Sort order')" />
        <x-text-input id="sort_order" name="sort_order" type="number" class="mt-2" :value="old('sort_order', $testimonial?->sort_order ?? 0)" />
        <p class="mt-1.5 text-[0.62rem] leading-relaxed text-boss-ivory/25">{{ __('Lower numbers show first. Use 0 for normal priority.') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
    </div>
</div>

<label for="is_published" class="flex items-start gap-3 rounded-xl border border-white/[0.06] bg-white/[0.03] p-4">
    <input id="is_published" name="is_published" type="checkbox" value="1" class="mt-1 rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold" @checked(old('is_published', $testimonial?->is_published ?? false))>
    <span>
        <span class="block text-[0.85rem] text-boss-ivory">{{ __('Approved & published') }}</span>
        <span class="mt-1 block text-[0.72rem] text-boss-ivory/32">{{ __('Approved stories are visible on the homepage and success stories page.') }}</span>
    </span>
</label>
