<x-chatter-layout title="Submit Model Review">
    @php
        $ratingLabels = \App\Models\ChatterModelReview::RATING_LABELS;
        $groups = [
            'energy_motivation' => [
                'label' => __('Did the model maintain good energy throughout their streams?'),
                'options' => \App\Models\ChatterModelReview::ENERGY_OPTIONS,
            ],
            'effort_towards_earnings' => [
                'label' => __('Did the model actively try to maximise their earnings?'),
                'options' => \App\Models\ChatterModelReview::EFFORT_OPTIONS,
                'hint' => __('Examples: staying online consistently, following advice, promoting goals, encouraging engagement, and taking suitable private or exclusive shows.'),
            ],
            'communication_teamwork' => [
                'label' => __('How well did the model communicate with you?'),
                'options' => \App\Models\ChatterModelReview::COMMUNICATION_OPTIONS,
            ],
            'followed_guidance' => [
                'label' => __('Did the model follow the advice and instructions given during the week?'),
                'options' => \App\Models\ChatterModelReview::GUIDANCE_OPTIONS,
            ],
        ];
        $modelOptions = $models->map(fn ($model) => [
            'id' => (string) $model->id,
            'name' => $model->name,
            'email' => $model->email,
            'label' => trim($model->name.' - '.$model->email),
        ])->values();
        $selectedModelValue = (string) old('model_id', $selectedModelId);
    @endphp

    <div class="mx-auto max-w-6xl space-y-6 text-boss-ivory">
        <header>
            <a href="{{ route('chatter.model-reviews.index') }}" class="text-sm text-boss-gold hover:text-boss-gold-light">{{ __('Back to model reviews') }}</a>
            <p class="pd-kicker mt-5">{{ __('Paradise Dolls') }}</p>
            <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,3rem)]">{{ __('Weekly Chatter Model Review Form') }}</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-boss-ivory/45">
                {{ __('This review is mandatory for every model you worked with during the week. Submit it every Sunday before 11:59 PM UK time.') }}
            </p>
        </header>

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if ($models->isEmpty())
            <section class="pd-panel-strong p-6 text-sm leading-6 text-boss-ivory/50">
                <p class="font-semibold text-boss-ivory">{{ __('No assigned models yet') }}</p>
                <p class="mt-2">{{ __('Your weekly review form opens once an admin assigns the model or models you worked with. Please ask the admin team to update your chatter account before submitting a review.') }}</p>
                <a href="{{ route('chatter.model-reviews.index') }}" class="pd-btn-secondary mt-5 inline-flex justify-center">{{ __('Back to model reviews') }}</a>
            </section>
        @else
        <form method="POST" action="{{ route('chatter.model-reviews.store') }}" class="pd-panel-strong space-y-7 p-5 md:p-6">
            @csrf

            <section class="space-y-4">
                <div>
                    <p class="pd-kicker">{{ __('Chatter Information') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.45rem] text-boss-ivory">{{ __('Reporting details') }}</h2>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label :value="__('Chatter name')" />
                        <div class="pd-input mt-2 flex items-center">{{ auth()->user()->name }}</div>
                    </div>
                    <div>
                        <x-input-label for="model_id" :value="__('Model name')" />
                        <div
                            class="relative mt-2"
                            x-data="{
                                open: false,
                                search: '',
                                selected: @js($selectedModelValue),
                                models: @js($modelOptions),
                                init() {
                                    const current = this.models.find((model) => model.id === this.selected);
                                    this.search = current ? current.label : '';
                                },
                                get filtered() {
                                    const q = this.search.trim().toLowerCase();
                                    if (!q || this.models.some((model) => model.id === this.selected && model.label === this.search)) {
                                        return this.models;
                                    }

                                    return this.models.filter((model) =>
                                        `${model.name || ''} ${model.email || ''} ${model.label || ''}`.toLowerCase().includes(q)
                                    );
                                },
                                choose(model) {
                                    this.selected = model.id;
                                    this.search = model.label;
                                    this.open = false;
                                },
                                openDropdown() {
                                    this.open = true;
                                    this.$nextTick(() => this.$refs.modelSearch?.focus());
                                },
                            }"
                            @keydown.escape.window="open = false"
                            @click.outside="open = false"
                        >
                            <input id="model_id" type="hidden" name="model_id" x-model="selected" required>
                            <button
                                type="button"
                                class="pd-input pd-combobox-trigger"
                                aria-haspopup="listbox"
                                :aria-expanded="open.toString()"
                                @click="openDropdown()"
                            >
                                <span class="min-w-0 flex-1 truncate" x-text="search || '{{ __('Select model') }}'"></span>
                                <svg class="h-3.5 w-3.5 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 6l4 4 4-4" />
                                </svg>
                            </button>

                            <div
                                x-cloak
                                x-show="open"
                                x-transition
                                class="pd-combobox-menu absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-md shadow-luxe"
                                role="listbox"
                            >
                                <div class="pd-combobox-search-wrap p-2">
                                    <input
                                        x-ref="modelSearch"
                                        type="text"
                                        x-model="search"
                                        placeholder="{{ __('Search model name or email...') }}"
                                        class="pd-combobox-search"
                                        autocomplete="off"
                                        @input="selected = ''; open = true"
                                        @keydown.escape.stop="open = false"
                                    >
                                </div>
                                <div class="max-h-64 overflow-y-auto py-1">
                                    <template x-if="filtered.length === 0">
                                        <p class="pd-combobox-empty">{{ __('No models found') }}</p>
                                    </template>
                                    <template x-for="model in filtered" :key="model.id">
                                        <button
                                            type="button"
                                            class="pd-combobox-option"
                                            :class="selected === model.id ? 'is-selected' : ''"
                                            role="option"
                                            :aria-selected="(selected === model.id).toString()"
                                            @click="choose(model)"
                                        >
                                            <span class="font-semibold" x-text="model.name"></span>
                                            <span class="pd-combobox-option-meta" x-text="model.email"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('model_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label :value="__('Date submitted')" />
                        <div class="pd-input mt-2 flex items-center">{{ now('Europe/London')->format('M j, Y') }}</div>
                    </div>
                    <div>
                        <x-input-label for="week_ending" :value="__('Week ending')" />
                        <x-text-input id="week_ending" name="week_ending" type="date" class="mt-2" :value="old('week_ending', $weekEnding->toDateString())" required />
                    </div>
                </div>
            </section>

            <section class="space-y-4 border-t border-white/[0.06] pt-6">
                <div>
                    <p class="pd-kicker">{{ __('Overall Performance') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.45rem] text-boss-ivory">{{ __('Rate the model') }}</h2>
                    <p class="mt-2 text-xs text-boss-ivory/35">{{ __('1 = Poor, 2 = Needs Improvement, 3 = Good, 4 = Very Good, 5 = Excellent') }}</p>
                </div>
                <fieldset class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                    <legend class="text-sm font-semibold text-boss-ivory">{{ __('How would you rate the model overall this week?') }}</legend>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($ratingLabels as $rating => $label)
                            <label class="pd-rating-option cursor-pointer">
                                <input type="radio" name="overall_rating" value="{{ $rating }}" class="sr-only" @checked((string) old('overall_rating') === (string) $rating) required>
                                <span class="pd-rating-pill" title="{{ $label }}">{{ $rating }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('overall_rating')" class="mt-2" />
                </fieldset>
                <div>
                    <x-input-label for="overall_rating_explanation" :value="__('Please explain your rating')" />
                    <textarea id="overall_rating_explanation" name="overall_rating_explanation" class="pd-input mt-2 min-h-[120px]" required>{{ old('overall_rating_explanation') }}</textarea>
                </div>
            </section>

            <section class="space-y-5 border-t border-white/[0.06] pt-6">
                <div>
                    <p class="pd-kicker">{{ __('Weekly Performance') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.45rem] text-boss-ivory">{{ __('Core review areas') }}</h2>
                </div>

                @foreach ($groups as $field => $group)
                    <fieldset class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                        <legend class="text-sm font-semibold text-boss-ivory">{{ $group['label'] }}</legend>
                        @if (! empty($group['hint']))
                            <p class="mt-2 text-xs leading-5 text-boss-ivory/35">{{ $group['hint'] }}</p>
                        @endif
                        <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($group['options'] as $value => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-sm border border-white/[0.08] bg-white/[0.03] p-3 text-sm has-[:checked]:border-boss-gold/50 has-[:checked]:bg-boss-gold/10 has-[:checked]:text-boss-gold">
                                    <input type="radio" name="{{ $field }}" value="{{ $value }}" @checked(old($field) === $value) required>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    @php
                        $commentField = match ($field) {
                            'energy_motivation' => 'energy_comments',
                            'effort_towards_earnings' => 'effort_comments',
                            'communication_teamwork' => 'communication_comments',
                            default => 'guidance_examples',
                        };
                    @endphp
                    <div>
                        <x-input-label for="{{ $commentField }}" :value="$field === 'followed_guidance' ? __('Please provide examples') : __('Comments')" />
                        <textarea id="{{ $commentField }}" name="{{ $commentField }}" class="pd-input mt-2 min-h-[100px]">{{ old($commentField) }}</textarea>
                    </div>
                @endforeach
            </section>

            <section class="space-y-5 border-t border-white/[0.06] pt-6">
                <div>
                    <p class="pd-kicker">{{ __('Detailed Feedback') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.45rem] text-boss-ivory">{{ __('What management should know') }}</h2>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="went_well" :value="__('What did the model do particularly well this week?')" />
                        <textarea id="went_well" name="went_well" class="pd-input mt-2 min-h-[120px]" required>{{ old('went_well') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="could_improve" :value="__('Please list any areas where the model could improve')" />
                        <textarea id="could_improve" name="could_improve" class="pd-input mt-2 min-h-[120px]" required>{{ old('could_improve') }}</textarea>
                    </div>
                </div>

                <fieldset class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                    <legend class="text-sm font-semibold text-boss-ivory">{{ __('Were there any issues during your shifts?') }}</legend>
                    <p class="mt-2 text-xs leading-5 text-boss-ivory/35">{{ __('Examples: internet issues, technical problems, attendance, communication, or customer concerns.') }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ([0 => __('No'), 1 => __('Yes')] as $value => $label)
                            <label class="flex cursor-pointer items-center gap-3 rounded-sm border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm has-[:checked]:border-boss-gold/50 has-[:checked]:bg-boss-gold/10 has-[:checked]:text-boss-gold">
                                <input type="radio" name="shift_issues" value="{{ $value }}" @checked((string) old('shift_issues', '0') === (string) $value) required>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <textarea name="shift_issues_explanation" class="pd-input mt-4 min-h-[100px]" placeholder="{{ __('If yes, please explain') }}">{{ old('shift_issues_explanation') }}</textarea>
                </fieldset>

                <fieldset class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                    <legend class="text-sm font-semibold text-boss-ivory">{{ __('Did you notice any behaviour that may require management attention?') }}</legend>
                    <p class="mt-2 text-xs leading-5 text-boss-ivory/35">{{ __('Examples: sharing personal contact details, off-platform payments, bank details, encouraging customers to leave the platform, private information, or rule breaches.') }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ([0 => __('No concerns'), 1 => __('Yes')] as $value => $label)
                            <label class="flex cursor-pointer items-center gap-3 rounded-sm border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm has-[:checked]:border-boss-gold/50 has-[:checked]:bg-boss-gold/10 has-[:checked]:text-boss-gold">
                                <input type="radio" name="security_concerns" value="{{ $value }}" @checked((string) old('security_concerns', '0') === (string) $value) required>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <textarea name="security_concerns_explanation" class="pd-input mt-4 min-h-[100px]" placeholder="{{ __('If yes, please explain in detail') }}">{{ old('security_concerns_explanation') }}</textarea>
                </fieldset>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="authorised_actions" :value="__('Did you take any authorised actions during the week?')" />
                        <textarea id="authorised_actions" name="authorised_actions" class="pd-input mt-2 min-h-[110px]" placeholder="{{ __('Blocked users, reported abusive members, escalated an issue, contacted management, or assisted with technical problems') }}">{{ old('authorised_actions') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="performance_strategies" :value="__('What conversations, promotions, or techniques noticeably improved results?')" />
                        <textarea id="performance_strategies" name="performance_strategies" class="pd-input mt-2 min-h-[110px]" placeholder="{{ __('Tips, customer engagement, private shows, exclusive shows, or customer retention') }}">{{ old('performance_strategies') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="customer_feedback" :value="__('Did customers respond positively? Were there repeated compliments or complaints?')" />
                        <textarea id="customer_feedback" name="customer_feedback" class="pd-input mt-2 min-h-[110px]">{{ old('customer_feedback') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="coaching_recommendations" :value="__('Top three coaching recommendations for next week')" />
                        <textarea id="coaching_recommendations" name="coaching_recommendations" class="pd-input mt-2 min-h-[110px]">{{ old('coaching_recommendations') }}</textarea>
                    </div>
                </div>

                <fieldset class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                    <legend class="text-sm font-semibold text-boss-ivory">{{ __('Do you believe this model deserves recognition this week?') }}</legend>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ([0 => __('No'), 1 => __('Yes')] as $value => $label)
                            <label class="flex cursor-pointer items-center gap-3 rounded-sm border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm has-[:checked]:border-boss-gold/50 has-[:checked]:bg-boss-gold/10 has-[:checked]:text-boss-gold">
                                <input type="radio" name="recognition" value="{{ $value }}" @checked((string) old('recognition', '0') === (string) $value) required>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <textarea name="recognition_reason" class="pd-input mt-4 min-h-[100px]" placeholder="{{ __('If yes, why?') }}">{{ old('recognition_reason') }}</textarea>
                </fieldset>

                <div>
                    <x-input-label for="additional_comments" :value="__('Additional comments')" />
                    <textarea id="additional_comments" name="additional_comments" class="pd-input mt-2 min-h-[120px]">{{ old('additional_comments') }}</textarea>
                </div>
            </section>

            <section class="space-y-4 border-t border-white/[0.06] pt-6">
                <div>
                    <p class="pd-kicker">{{ __('Chatter Declaration') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.45rem] text-boss-ivory">{{ __('Confirm and submit') }}</h2>
                </div>
                <label class="flex cursor-pointer items-start gap-3 rounded-sm border border-white/[0.08] bg-white/[0.03] p-4">
                    <input type="checkbox" name="declaration_accepted" value="1" class="mt-1" @checked(old('declaration_accepted')) required>
                    <span class="text-sm leading-6 text-boss-ivory/70">{{ __('I confirm this review is honest, accurate and based on my experience working with this model during the reporting period.') }}</span>
                </label>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="signature" :value="__('Signature')" />
                        <x-text-input id="signature" name="signature" class="mt-2" :value="old('signature', auth()->user()->name)" required />
                    </div>
                    <div>
                        <x-input-label :value="__('Date')" />
                        <div class="pd-input mt-2 flex items-center">{{ now('Europe/London')->format('M j, Y') }}</div>
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('chatter.model-reviews.index') }}" class="pd-btn-secondary justify-center">{{ __('Cancel') }}</a>
                    <button type="submit" class="pd-btn-primary justify-center">{{ __('Submit Weekly Review') }}</button>
                </div>
            </section>
        </form>
        @endif
    </div>
</x-chatter-layout>
