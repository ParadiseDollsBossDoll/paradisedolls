@php
    use App\Support\MarketingContent;

    $activeRoute = $activeDefinition['route'] ?? 'home';
    $viewPageUrl = route($activeRoute);

    $inputClasses = 'mt-2 w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-3 text-[0.88rem] text-boss-ivory outline-none transition-colors placeholder:text-boss-ivory/25 focus:border-boss-gold/50 focus:bg-white/[0.06]';
    $labelClasses = 'text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-boss-ivory/45';
@endphp

<x-admin-layout>
    <div class="mx-auto max-w-6xl space-y-6 pb-10 text-boss-ivory">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Main Site') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(1.9rem,4vw,2.8rem)]">{{ __('Site Editor') }}</h1>
                <p class="mt-3 max-w-2xl text-[0.9rem] leading-relaxed text-boss-ivory/50">
                    {{ __('Update the public website copy and images while keeping the current Paradise Dolls design intact.') }}
                </p>
            </div>

            <a href="{{ $viewPageUrl }}" target="_blank" rel="noopener" class="pd-btn-secondary self-start sm:self-auto">
                {{ __('View page') }}
            </a>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/25 bg-red-500/10 p-4 text-sm text-red-100">
                <p class="font-semibold">{{ __('Please check the highlighted fields.') }}</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-red-100/80">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <nav class="flex gap-2 overflow-x-auto rounded-2xl border border-white/[0.07] bg-white/[0.035] p-2">
            @foreach ($pages as $pageKey => $page)
                <a
                    href="{{ route('admin.site-editor.edit', ['page' => $pageKey]) }}"
                    @class([
                        'shrink-0 rounded-xl px-4 py-2.5 text-[0.76rem] font-semibold transition-colors',
                        'bg-boss-gold text-boss-dark' => $activePage === $pageKey,
                        'text-boss-ivory/50 hover:bg-white/[0.06] hover:text-boss-ivory' => $activePage !== $pageKey,
                    ])
                >
                    {{ __($page['label']) }}
                </a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.site-editor.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="_page" value="{{ $activePage }}">

            <section class="rounded-2xl border border-white/[0.07] bg-boss-panel/80 p-5 shadow-luxe sm:p-6">
                <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="pd-kicker">{{ __($activeDefinition['label']) }}</p>
                        <h2 class="mt-1 font-display text-[1.6rem] text-boss-ivory">{{ __('Editable content') }}</h2>
                    </div>
                    <a href="{{ $viewPageUrl }}" target="_blank" rel="noopener" class="text-[0.72rem] uppercase tracking-[0.16em] text-boss-gold transition-colors hover:text-boss-gold-light">
                        {{ __('View page') }} ->
                    </a>
                </div>

                <div class="space-y-6">
                    @foreach ($activeDefinition['fields'] as $field)
                        @php
                            $fieldId = MarketingContent::fieldId($field['key']);
                            $fullKey = $activePage.'.'.$field['key'];
                            $fieldType = $field['type'];
                            $errorKey = "content.{$activePage}.{$fieldId}";
                        @endphp

                        <div class="rounded-2xl border border-white/[0.06] bg-white/[0.025] p-4">
                            @if ($fieldType === 'cards')
                                @php
                                    $cards = MarketingContent::items($fullKey);
                                    $cardFields = $field['card_fields'] ?? [];
                                    $blankCard = array_fill_keys(array_keys($cardFields), '');
                                    $cardRows = array_merge($cards, [$blankCard, $blankCard]);
                                @endphp

                                <div class="mb-4">
                                    <label class="{{ $labelClasses }}">{{ __($field['label']) }}</label>
                                    <p class="mt-1 text-[0.75rem] text-boss-ivory/35">{{ __('Edit each card row. Empty rows will not show on the public page.') }}</p>
                                </div>

                                <div class="space-y-4">
                                    @foreach ($cardRows as $index => $card)
                                        <div class="rounded-xl border border-white/[0.06] bg-black/10 p-4">
                                            <p class="mb-4 text-[0.68rem] uppercase tracking-[0.16em] text-boss-gold/80">
                                                {{ __('Card :number', ['number' => $index + 1]) }}
                                            </p>

                                            <div class="grid gap-4 md:grid-cols-2">
                                                @foreach ($cardFields as $subKey => $subType)
                                                    @php
                                                        $subLabel = str_replace('_', ' ', $subKey);
                                                        $subValue = (string) ($card[$subKey] ?? '');
                                                    @endphp

                                                    <div @class(['md:col-span-2' => in_array($subType, ['textarea', 'image'], true)])>
                                                        <label class="{{ $labelClasses }}">{{ __(ucwords($subLabel)) }}</label>

                                                        @if ($subType === 'image')
                                                            @if ($subValue !== '')
                                                                <img src="{{ MarketingContent::imageUrl($subValue) }}" alt="" class="mt-3 h-36 w-full rounded-xl object-cover">
                                                            @endif
                                                            <input type="hidden" name="content[{{ $activePage }}][{{ $fieldId }}][{{ $index }}][{{ $subKey }}]" value="{{ $subValue }}">
                                                            <input type="file" name="card_image_files[{{ $activePage }}][{{ $fieldId }}][{{ $index }}][{{ $subKey }}]" accept=".jpg,.jpeg,.png,.webp" class="mt-3 block w-full text-[0.8rem] text-boss-ivory/55 file:mr-4 file:rounded-lg file:border-0 file:bg-boss-gold file:px-4 file:py-2 file:text-[0.72rem] file:font-semibold file:text-boss-dark">
                                                        @elseif ($subType === 'textarea')
                                                            <textarea name="content[{{ $activePage }}][{{ $fieldId }}][{{ $index }}][{{ $subKey }}]" rows="3" class="{{ $inputClasses }}">{{ old("content.{$activePage}.{$fieldId}.{$index}.{$subKey}", $subValue) }}</textarea>
                                                        @else
                                                            <input type="text" name="content[{{ $activePage }}][{{ $fieldId }}][{{ $index }}][{{ $subKey }}]" value="{{ old("content.{$activePage}.{$fieldId}.{$index}.{$subKey}", $subValue) }}" class="{{ $inputClasses }}">
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <label for="{{ $activePage }}-{{ $fieldId }}" class="{{ $labelClasses }}">{{ __($field['label']) }}</label>

                                @if ($fieldType === 'image')
                                    @php $imageValue = MarketingContent::text($fullKey); @endphp
                                    @if ($imageValue !== '')
                                        <img src="{{ MarketingContent::imageUrl($imageValue) }}" alt="" class="mt-3 h-48 w-full rounded-xl object-cover">
                                    @endif
                                    <input type="hidden" name="content[{{ $activePage }}][{{ $fieldId }}]" value="{{ $imageValue }}">
                                    <input id="{{ $activePage }}-{{ $fieldId }}" type="file" name="image_files[{{ $activePage }}][{{ $fieldId }}]" accept=".jpg,.jpeg,.png,.webp" class="mt-3 block w-full text-[0.8rem] text-boss-ivory/55 file:mr-4 file:rounded-lg file:border-0 file:bg-boss-gold file:px-4 file:py-2 file:text-[0.72rem] file:font-semibold file:text-boss-dark">
                                    <p class="mt-2 text-[0.72rem] text-boss-ivory/35">{{ __('JPG, PNG, or WEBP. Maximum 5MB.') }}</p>
                                @elseif (in_array($fieldType, ['textarea', 'paragraphs', 'list'], true))
                                    @php
                                        $textareaValue = $fieldType === 'textarea'
                                            ? MarketingContent::text($fullKey)
                                            : MarketingContent::textareaValue($fullKey, $fieldType);
                                    @endphp
                                    <textarea id="{{ $activePage }}-{{ $fieldId }}" name="content[{{ $activePage }}][{{ $fieldId }}]" rows="{{ $fieldType === 'list' ? 5 : 6 }}" class="{{ $inputClasses }}">{{ old($errorKey, $textareaValue) }}</textarea>
                                    @if ($fieldType === 'list')
                                        <p class="mt-2 text-[0.72rem] text-boss-ivory/35">{{ __('One item per line.') }}</p>
                                    @elseif ($fieldType === 'paragraphs')
                                        <p class="mt-2 text-[0.72rem] text-boss-ivory/35">{{ __('Separate paragraphs with a blank line.') }}</p>
                                    @endif
                                @else
                                    <input id="{{ $activePage }}-{{ $fieldId }}" type="text" name="content[{{ $activePage }}][{{ $fieldId }}]" value="{{ old($errorKey, MarketingContent::text($fullKey)) }}" class="{{ $inputClasses }}">
                                    @if ($fieldType === 'url')
                                        <p class="mt-2 text-[0.72rem] text-boss-ivory/35">{{ __('Use /page, #section, or a full https:// URL.') }}</p>
                                    @endif
                                @endif

                                @error($errorKey)
                                    <p class="mt-2 text-[0.76rem] text-red-300">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="sticky bottom-4 z-20 flex flex-col gap-3 rounded-2xl border border-white/[0.08] bg-boss-dark/95 p-4 shadow-luxe backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[0.8rem] text-boss-ivory/45">{{ __('Saving publishes these changes to the live public site immediately.') }}</p>
                <button type="submit" class="pd-btn-primary">
                    {{ __('Save Site Content') }}
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
