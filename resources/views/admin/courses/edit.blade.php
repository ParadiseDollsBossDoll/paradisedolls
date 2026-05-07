<x-admin-layout>
    <div
        class="mx-auto max-w-5xl space-y-8 text-boss-ivory"
        x-data="{
            platform: @js(old('platform_label', $course->platform_label)),
            platformColor: @js(old('platform_color', $course->displayColor())),
            showSuggestions: false,
            suggestions: @js($platformSuggestions),
            colors: @js($colorSwatches),
            pickPlatform(name, color) {
                this.platform = name;
                this.platformColor = color;
                this.showSuggestions = false;
            }
        }"
    >
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Academy') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.6rem)]">{{ __('Edit Course') }}</h1>
            </div>
            <a href="{{ route('admin.courses.index') }}" class="pd-btn-secondary">{{ __('Back to Courses') }}</a>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <section class="pd-panel p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.courses.update', $course) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-2" :value="old('title', $course->title)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="slug" :value="__('URL slug')" />
                        <x-text-input id="slug" name="slug" type="text" class="mt-2" :value="old('slug', $course->slug)" />
                        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="platform_label" :value="__('Platform label')" />
                    <div class="relative mt-2">
                        <x-text-input id="platform_label" name="platform_label" type="text" x-model="platform" required class="pr-32" />
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
                        <input id="platform_color" type="text" x-model="platformColor" class="w-28 rounded-lg border border-white/10 bg-white/[0.06] px-3 py-2 font-mono text-[0.78rem] text-boss-ivory focus:border-boss-gold/50 focus:outline-none" placeholder="#C9A96E">
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('platform_color')" />
                </div>

                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="5" class="pd-input mt-2">{{ old('description', $course->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="grid gap-6 md:grid-cols-[1fr_180px] md:items-end">
                    <div class="rounded-xl border border-white/[0.06] bg-white/[0.03] p-4">
                        <label for="is_published" class="flex items-start gap-3">
                            <input id="is_published" name="is_published" type="checkbox" value="1" class="mt-1 rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold" @checked(old('is_published', $course->is_published))>
                            <span>
                                <span class="block text-[0.85rem] text-boss-ivory">{{ __('Published') }}</span>
                                <span class="mt-1 block text-[0.72rem] text-boss-ivory/32">{{ __('Visible to approved members.') }}</span>
                            </span>
                        </label>
                    </div>

                    <div>
                        <x-input-label for="sort_order" :value="__('Sort order')" />
                        <x-text-input id="sort_order" name="sort_order" type="number" class="mt-2" :value="old('sort_order', $course->sort_order)" />
                        <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                    </div>
                </div>

                <x-primary-button>{{ __('Update Course') }}</x-primary-button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-panel">
            <div class="border-b border-white/[0.06] px-6 py-5">
                <h2 class="pd-heading text-[1.35rem] text-boss-ivory">{{ __('Lessons') }}</h2>
                <p class="mt-1 text-[0.78rem] text-boss-ivory/35">{{ __('Paste embed URLs from your video host, then add notes for the member view.') }}</p>
            </div>

            <div class="divide-y divide-white/[0.06]">
                @forelse ($course->lessons as $lesson)
                    <div class="space-y-5 px-6 py-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="pd-kicker">{{ __('Lesson #:id', ['id' => $lesson->id]) }}</p>
                                <h3 class="pd-heading mt-1 text-[1.2rem] text-boss-ivory">{{ $lesson->title }}</h3>
                            </div>
                            <form method="POST" action="{{ route('admin.courses.lessons.destroy', [$course, $lesson]) }}" onsubmit="return confirm('{{ __('Remove this lesson?') }}');">
                                @csrf
                                @method('DELETE')
                                <x-danger-button type="submit">{{ __('Remove') }}</x-danger-button>
                            </form>
                        </div>

                        <form method="POST" action="{{ route('admin.courses.lessons.update', [$course, $lesson]) }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <x-input-label for="title_{{ $lesson->id }}" :value="__('Title')" />
                                <x-text-input id="title_{{ $lesson->id }}" name="title" type="text" class="mt-2" :value="old('title', $lesson->title)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('title')" />
                            </div>

                            <div>
                                <x-input-label for="body_{{ $lesson->id }}" :value="__('Body / notes')" />
                                <textarea id="body_{{ $lesson->id }}" name="body" rows="4" class="pd-input mt-2">{{ old('body', $lesson->body) }}</textarea>
                            </div>

                            <div class="grid gap-4 md:grid-cols-[1fr_160px]">
                                <div>
                                    <x-input-label for="video_{{ $lesson->id }}" :value="__('Video embed URL')" />
                                    <x-text-input id="video_{{ $lesson->id }}" name="video_url" type="text" class="mt-2" :value="old('video_url', $lesson->video_url)" />
                                </div>

                                <div>
                                    <x-input-label for="duration_{{ $lesson->id }}" :value="__('Duration')" />
                                    <x-text-input id="duration_{{ $lesson->id }}" name="duration" type="text" class="mt-2" :value="old('duration', $lesson->duration)" placeholder="12:30" />
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-[1fr_160px]">
                                <div>
                                    <x-input-label for="pdf_{{ $lesson->id }}" :value="__('PDF URL')" />
                                    <x-text-input id="pdf_{{ $lesson->id }}" name="pdf_url" type="text" class="mt-2" :value="old('pdf_url', $lesson->pdf_url)" />
                                </div>

                                <div>
                                    <x-input-label for="sort_{{ $lesson->id }}" :value="__('Sort order')" />
                                    <x-text-input id="sort_{{ $lesson->id }}" name="sort_order" type="number" class="mt-2" :value="old('sort_order', $lesson->sort_order)" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="presentation_{{ $lesson->id }}" :value="__('Canva / presentation URL')" />
                                <x-text-input id="presentation_{{ $lesson->id }}" name="presentation_url" type="text" class="mt-2" :value="old('presentation_url', $lesson->presentation_url)" placeholder="https://www.canva.com/design/..." />
                                <p class="mt-1 text-[0.65rem] text-boss-ivory/25">{{ __('Visual slide deck shown beside PDF and video resources in the member lesson view.') }}</p>
                            </div>

                            <label for="has_pdf_{{ $lesson->id }}" class="flex items-center gap-3 rounded-xl border border-white/[0.08] bg-white/[0.025] px-3 py-2.5">
                                <input id="has_pdf_{{ $lesson->id }}" name="has_pdf" type="checkbox" value="1" class="rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold" @checked(old('has_pdf', $lesson->has_pdf))>
                                <span class="text-[0.78rem] text-boss-ivory/45">{{ __('PDF guide included') }}</span>
                            </label>

                            <x-secondary-button type="submit">{{ __('Save Lesson') }}</x-secondary-button>
                        </form>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-[0.9rem] text-boss-ivory/35">{{ __('No lessons yet. Add the first one below.') }}</div>
                @endforelse
            </div>

            <div class="border-t border-white/[0.06] bg-white/[0.02] px-6 py-6">
                <h3 class="pd-heading text-[1.2rem] text-boss-ivory">{{ __('Add Lesson') }}</h3>
                <form method="POST" action="{{ route('admin.courses.lessons.store', $course) }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="new_title" :value="__('Title')" />
                        <x-text-input id="new_title" name="title" type="text" class="mt-2" required />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="new_body" :value="__('Body / notes')" />
                        <textarea id="new_body" name="body" rows="4" class="pd-input mt-2"></textarea>
                    </div>

                    <div class="grid gap-4 md:grid-cols-[1fr_160px]">
                        <div>
                            <x-input-label for="new_video_url" :value="__('Video embed URL')" />
                            <x-text-input id="new_video_url" name="video_url" type="url" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="new_duration" :value="__('Duration')" />
                            <x-text-input id="new_duration" name="duration" type="text" class="mt-2" placeholder="12:30" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-[1fr_160px]">
                        <div>
                            <x-input-label for="new_pdf_url" :value="__('PDF URL')" />
                            <x-text-input id="new_pdf_url" name="pdf_url" type="text" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="new_sort_order" :value="__('Sort order')" />
                            <x-text-input id="new_sort_order" name="sort_order" type="number" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="new_presentation_url" :value="__('Canva / presentation URL')" />
                        <x-text-input id="new_presentation_url" name="presentation_url" type="text" class="mt-2" placeholder="https://www.canva.com/design/..." />
                    </div>

                    <label for="new_has_pdf" class="flex items-center gap-3 rounded-xl border border-white/[0.08] bg-white/[0.025] px-3 py-2.5">
                        <input id="new_has_pdf" name="has_pdf" type="checkbox" value="1" class="rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold">
                        <span class="text-[0.78rem] text-boss-ivory/45">{{ __('PDF guide included') }}</span>
                    </label>

                    <x-primary-button>{{ __('Add Lesson') }}</x-primary-button>
                </form>
            </div>
        </section>
    </div>
</x-admin-layout>
