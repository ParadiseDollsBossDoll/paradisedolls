@php
    $initialLessons = old('lessons', [[
        'title' => '',
        'body' => '',
        'video_url' => '',
        'duration' => '',
        'has_pdf' => false,
        'pdf_url' => '',
        'presentation_url' => '',
        'sort_order' => 1,
    ]]);
@endphp

<x-admin-layout>
    <div
        class="mx-auto max-w-4xl space-y-5 pb-10 text-boss-ivory"
        x-data="{
            platform: @js(old('platform_label', '')),
            platformColor: @js(old('platform_color', '#C9A96E')),
            showSuggestions: true,
            lessons: @js(array_values($initialLessons)),
            suggestions: @js($platformSuggestions),
            colors: @js($colorSwatches),
            pickPlatform(name, color) {
                this.platform = name;
                this.platformColor = color;
                this.showSuggestions = false;
            },
            addLesson() {
                this.lessons.push({ title: '', body: '', video_url: '', duration: '', has_pdf: false, pdf_url: '', presentation_url: '', sort_order: this.lessons.length + 1 });
            },
            removeLesson(index) {
                this.lessons.splice(index, 1);
                this.lessons = this.lessons.map((lesson, i) => ({ ...lesson, sort_order: i + 1 }));
            }
        }"
    >
        <header class="flex items-center gap-4">
            <a href="{{ route('admin.courses.index') }}" class="rounded-xl border border-white/[0.07] bg-white/[0.04] px-3 py-2 text-[0.78rem] text-boss-ivory/45 transition-colors hover:text-boss-gold">
                <- {{ __('Courses') }}
            </a>
            <div>
                <p class="pd-kicker">{{ __('New Course') }}</p>
                <h1 class="pd-heading mt-1 text-[clamp(1.7rem,3vw,2.3rem)]">{{ __('Create Course') }}</h1>
            </div>
        </header>

        <form method="POST" action="{{ route('admin.courses.store') }}" class="space-y-5">
            @csrf

            <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#0E0E1A]">
                <div class="border-b border-white/[0.05] bg-white/[0.01] px-6 py-4">
                    <p class="pd-heading text-[1.05rem] text-boss-gold">{{ __('Course Details') }}</p>
                </div>

                <div class="space-y-5 p-6">
                    <div>
                        <x-input-label for="platform_label" :value="__('Platform Name')" />
                        <div class="relative mt-2">
                            <x-text-input id="platform_label" name="platform_label" type="text" x-model="platform" required placeholder="{{ __('Type any platform name') }}" class="pr-32" />
                            <span x-show="platform" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full border px-2 py-0.5 text-[0.65rem]" x-bind:style="`background-color: ${platformColor}20; color: ${platformColor}; border-color: ${platformColor}30;`" x-text="platform"></span>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('platform_label')" />

                        <button type="button" @click="showSuggestions = !showSuggestions" class="mt-3 text-[0.68rem] text-boss-ivory/30 transition-colors hover:text-boss-gold">
                            <span x-text="showSuggestions ? '{{ __('Hide popular platform suggestions') }}' : '{{ __('Show popular platform suggestions') }}'"></span>
                        </button>

                        <div x-show="showSuggestions" x-transition class="mt-3 rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">
                            <p class="mb-3 text-[0.6rem] uppercase tracking-[0.15em] text-boss-ivory/25">{{ __('Popular platforms - click to fill') }}</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="suggestion in suggestions" :key="suggestion.name">
                                    <button
                                        type="button"
                                        @click="pickPlatform(suggestion.name, suggestion.color)"
                                        class="rounded-full border px-3 py-1.5 text-[0.75rem] transition-colors"
                                        x-bind:style="platform === suggestion.name ? `background-color: ${suggestion.color}18; border-color: ${suggestion.color}50; color: ${suggestion.color};` : ''"
                                        x-bind:class="platform === suggestion.name ? '' : 'border-white/[0.08] bg-white/[0.04] text-boss-ivory/50'"
                                        x-text="suggestion.name"
                                    ></button>
                                </template>
                            </div>
                            <p class="mt-3 text-[0.62rem] leading-relaxed text-boss-ivory/20">{{ __('Do not see your platform? Just type it above. Any name works.') }}</p>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="platform_color" :value="__('Accent Colour')" />
                        <input type="hidden" name="platform_color" x-model="platformColor">
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <template x-for="color in colors" :key="color">
                                <button
                                    type="button"
                                    class="h-7 w-7 shrink-0 rounded-full transition-transform"
                                    x-bind:title="color"
                                    x-bind:style="`background-color: ${color}; outline: ${platformColor === color ? '2px solid ' + color : '2px solid transparent'}; outline-offset: 2px; transform: ${platformColor === color ? 'scale(1.15)' : 'scale(1)'};`"
                                    @click="platformColor = color"
                                ></button>
                            </template>
                            <input type="text" x-model="platformColor" class="w-28 rounded-lg border border-white/10 bg-white/[0.06] px-3 py-2 font-mono text-[0.78rem] text-boss-ivory focus:border-boss-gold/50 focus:outline-none" placeholder="#C9A96E">
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('platform_color')" />
                    </div>

                    <div>
                        <x-input-label for="title" :value="__('Course Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-2" :value="old('title')" required placeholder="{{ __('Give your course a clear title') }}" />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3" class="pd-input mt-2" required placeholder="{{ __('Describe what models will learn') }}">{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="grid gap-5 md:grid-cols-[1fr_180px] md:items-end">
                        <label for="is_published" class="flex items-start gap-3 rounded-xl border border-white/[0.06] bg-white/[0.03] p-4">
                            <input id="is_published" name="is_published" type="checkbox" value="1" class="mt-1 rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold" {{ old('is_published') ? 'checked' : '' }}>
                            <span>
                                <span class="block text-[0.85rem] text-boss-ivory">{{ __('Published') }}</span>
                                <span class="mt-1 block text-[0.72rem] text-boss-ivory/32">{{ __('Visible to approved members after saving.') }}</span>
                            </span>
                        </label>

                        <div>
                            <x-input-label for="sort_order" :value="__('Sort order')" />
                            <x-text-input id="sort_order" name="sort_order" type="number" class="mt-2" :value="old('sort_order', 0)" />
                            <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                        </div>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#0E0E1A]">
                <div class="flex items-center justify-between border-b border-white/[0.05] bg-white/[0.01] px-6 py-4">
                    <p class="pd-heading text-[1.05rem] text-boss-gold">{{ __('Lessons') }} <span class="text-boss-ivory/30" x-text="`(${lessons.length})`"></span></p>
                    <button type="button" @click="addLesson()" class="rounded-xl border border-boss-gold/20 bg-boss-gold/10 px-4 py-2 text-[0.75rem] text-boss-gold transition-colors hover:bg-boss-gold/15">
                        {{ __('Add Lesson') }}
                    </button>
                </div>

                <div class="space-y-4 p-6">
                    <x-input-error class="mt-2" :messages="$errors->get('lessons')" />

                    <template x-for="(lesson, index) in lessons" :key="index">
                        <div class="overflow-hidden rounded-2xl border border-white/[0.05] bg-[#131320]">
                            <div class="flex items-center gap-3 border-b border-white/[0.05] bg-white/[0.01] px-4 py-3">
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-[0.72rem] font-semibold" x-bind:style="`background-color: ${platformColor}20; color: ${platformColor}; border-color: ${platformColor}30;`" x-text="index + 1"></div>
                                <p class="text-[0.75rem] text-boss-ivory/40" x-text="`Lesson ${index + 1}`"></p>
                                <button type="button" @click="removeLesson(index)" class="ml-auto rounded-lg border border-red-400/10 bg-red-400/[0.05] px-2.5 py-1.5 text-[0.7rem] text-red-400/60 transition-colors hover:text-red-300">
                                    {{ __('Remove') }}
                                </button>
                            </div>

                            <div class="grid gap-3 p-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`lesson_title_${index}`" :value="__('Lesson Title')" />
                                    <input type="text" required class="pd-input mt-2" x-model="lesson.title" x-bind:id="`lesson_title_${index}`" x-bind:name="`lessons[${index}][title]`" placeholder="{{ __('Account setup and profile optimisation') }}">
                                </div>

                                <div>
                                    <x-input-label ::for="`lesson_video_${index}`" :value="__('Video URL')" />
                                    <input type="text" class="pd-input mt-2" x-model="lesson.video_url" x-bind:id="`lesson_video_${index}`" x-bind:name="`lessons[${index}][video_url]`" placeholder="https://www.youtube.com/embed/...">
                                    <p class="mt-1 text-[0.6rem] text-boss-ivory/20">{{ __('Use the /embed/ URL format from YouTube or your video host.') }}</p>
                                </div>

                                <div>
                                    <x-input-label ::for="`lesson_duration_${index}`" :value="__('Duration')" />
                                    <input type="text" class="pd-input mt-2" x-model="lesson.duration" x-bind:id="`lesson_duration_${index}`" x-bind:name="`lessons[${index}][duration]`" placeholder="12:30">
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`lesson_body_${index}`" :value="__('Lesson Description')" />
                                    <textarea rows="2" class="pd-input mt-2" x-model="lesson.body" x-bind:id="`lesson_body_${index}`" x-bind:name="`lessons[${index}][body]`" placeholder="{{ __('What will members learn in this lesson?') }}"></textarea>
                                </div>

                                <div>
                                    <label class="flex items-center gap-3 rounded-xl border border-white/[0.08] bg-white/[0.025] px-3 py-2.5">
                                        <input type="checkbox" value="1" x-model="lesson.has_pdf" x-bind:name="`lessons[${index}][has_pdf]`" class="rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold">
                                        <span class="text-[0.78rem] text-boss-ivory/45">{{ __('PDF guide included') }}</span>
                                    </label>
                                </div>

                                <div>
                                    <x-input-label ::for="`lesson_pdf_${index}`" :value="__('PDF URL')" />
                                    <input type="text" class="pd-input mt-2" x-model="lesson.pdf_url" x-bind:id="`lesson_pdf_${index}`" x-bind:name="`lessons[${index}][pdf_url]`" placeholder="https://...">
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`lesson_presentation_${index}`" :value="__('Canva / presentation URL')" />
                                    <input type="text" class="pd-input mt-2" x-model="lesson.presentation_url" x-bind:id="`lesson_presentation_${index}`" x-bind:name="`lessons[${index}][presentation_url]`" placeholder="https://www.canva.com/design/...">
                                    <p class="mt-1 text-[0.6rem] text-boss-ivory/20">{{ __('Use this for Canva-style visual presentations or slide decks.') }}</p>
                                </div>

                                <input type="hidden" x-bind:name="`lessons[${index}][sort_order]`" x-bind:value="index + 1">
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            <div class="flex items-center gap-3">
                <x-primary-button>{{ __('Create Course') }}</x-primary-button>
                <a href="{{ route('admin.courses.index') }}" class="text-[0.78rem] text-boss-ivory/30 transition-colors hover:text-boss-ivory">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-admin-layout>
