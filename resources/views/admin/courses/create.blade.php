@php
    $initialModules = old('modules', [[
        'id' => null,
        'client_key' => 'module-1',
        'title' => 'Core Training',
        'description' => '',
        'is_published' => true,
        'sort_order' => 1,
    ]]);

    $initialLessons = old('lessons', [[
        'course_module_id' => '',
        'module_key' => 'module-1',
        'module_title' => 'Core Training',
        'title' => '',
        'body' => '',
        'overview' => '',
        'steps' => '',
        'tips' => '',
        'safety_notes' => '',
        'resource_links' => '',
        'lesson_banner_image' => '',
        'lesson_banner_image_url' => '',
        'lesson_images' => [],
        'lesson_image_urls' => [],
        'content_blocks' => [],
        'content_blocks_enabled' => true,
        'is_published' => true,
        'video_url' => '',
        'bunny_video_id' => '',
        'bunny_library_id' => '',
        'bunny_video_title' => '',
        'bunny_thumbnail_url' => '',
        'bunny_upload_fingerprint' => '',
        'bunny_status' => '',
        'duration' => '',
        'pdf_url' => '',
        'presentation_url' => '',
        'sort_order' => 1,
    ]]);
@endphp

<x-admin-layout>
    <div
        class="mx-auto max-w-4xl space-y-5 pb-10 text-boss-ivory"
        x-data="adminCourseForm({
            platform: @js(old('platform_label', '')),
            platformColor: @js(old('platform_color', '#C9A96E')),
            showSuggestions: true,
            hasCourseOutline: @js(old('has_course_outline', false)),
            hasIntro: @js(old('has_intro', false)),
            introVideo: @js([
                'video_url' => old('intro_video_url', ''),
                'bunny_video_id' => old('intro_bunny_video_id', ''),
                'bunny_library_id' => old('intro_bunny_library_id', ''),
                'bunny_video_title' => old('intro_bunny_video_title', ''),
                'bunny_thumbnail_url' => old('intro_bunny_thumbnail_url', ''),
                'bunny_upload_fingerprint' => old('intro_bunny_upload_fingerprint', ''),
                'bunny_status' => old('intro_bunny_status', ''),
                'duration' => old('intro_duration', ''),
            ]),
            modules: @js(array_values($initialModules)),
            lessons: @js(array_values($initialLessons)),
            suggestions: @js($platformSuggestions),
            colors: @js($colorSwatches),
            bunnyVideosUrl: @js(route('admin.bunny.videos.index')),
            bunnyUploadIntentUrl: @js(route('admin.bunny.videos.upload-intent')),
            bunnyVideoUrlTemplate: @js(route('admin.bunny.videos.show', ['videoId' => '__VIDEO_ID__'])),
            lessonPreviewUrlTemplate: null,
        })"
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

        {{-- Step indicator --}}
        <div class="flex items-center gap-2 text-[0.68rem] text-boss-ivory/30">
            <span class="rounded-full px-2.5 py-0.5" x-bind:style="`background-color: ${platformColor}20; color: ${platformColor};`">① Course Details</span>
            <span class="text-boss-ivory/15">→</span>
            <span class="rounded-full px-2.5 py-0.5" x-bind:style="`background-color: ${platformColor}20; color: ${platformColor};`">② Course Materials</span>
            <span class="text-boss-ivory/15">→</span>
            <span class="rounded-full px-2.5 py-0.5" x-bind:style="`background-color: ${platformColor}20; color: ${platformColor};`">3 Modules</span>
            <span class="text-boss-ivory/15">→</span>
            <span class="rounded-full px-2.5 py-0.5" x-bind:style="`background-color: ${platformColor}20; color: ${platformColor};`">4 Lessons</span>
        </div>

        <form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- ① COURSE DETAILS --}}
            <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#0E0E1A]">
                <div class="border-b border-white/[0.05] bg-white/[0.01] px-5 py-3">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[0.6rem] font-bold" x-bind:style="`background-color: ${platformColor}25; color: ${platformColor};`">1</span>
                        <p class="pd-heading text-[0.9rem] text-boss-gold">{{ __('Course Details') }}</p>
                    </div>
                </div>

                <div class="space-y-4 p-5">
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
                                    class="h-5 w-5 shrink-0 rounded-full transition-transform"
                                    x-bind:title="color"
                                    x-bind:style="`background-color: ${color}; outline: ${platformColor === color ? '2px solid ' + color : '2px solid transparent'}; outline-offset: 2px; transform: ${platformColor === color ? 'scale(1.15)' : 'scale(1)'};`"
                                    @click="platformColor = color"
                                ></button>
                            </template>
                            <input type="text" x-model="platformColor" class="w-24 rounded-lg border border-white/10 bg-white/[0.06] px-2.5 py-1.5 font-mono text-[0.72rem] text-boss-ivory focus:border-boss-gold/50 focus:outline-none" placeholder="#C9A96E">
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('platform_color')" />
                    </div>

                    <div>
                        <x-input-label for="title" :value="__('Course Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-2" :value="old('title')" required placeholder="{{ __('Give your course a clear title') }}" />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="slug" :value="__('Slug')" />
                        <input type="text" id="slug" name="slug" class="pd-input mt-2" value="{{ old('slug') }}" placeholder="{{ __('leave blank to generate from title') }}">
                        <p class="mt-1 text-[0.6rem] text-boss-ivory/20">{{ __('Optional. Used in the course URL.') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                    </div>

                    <div>
                        <x-input-label for="short_description" :value="__('Short Description')" />
                        <textarea id="short_description" name="short_description" rows="2" class="pd-input mt-2" placeholder="{{ __('One or two lines for catalog cards and quick previews') }}">{{ old('short_description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('short_description')" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Full Description')" />
                        <textarea id="description" name="description" rows="4" class="pd-input mt-2" required placeholder="{{ __('Describe what models will learn') }}">{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div>
                        <x-input-label for="thumbnail_url" :value="__('Course Banner / Thumbnail URL')" />
                        <input type="text" id="thumbnail_url" name="thumbnail_url" class="pd-input mt-2" value="{{ old('thumbnail_url') }}" placeholder="https://...">
                        <p class="mt-1 text-[0.6rem] text-boss-ivory/20">{{ __('Optional. Used on the course overview page. Bunny thumbnails are used as a fallback.') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('thumbnail_url')" />
                    </div>

                    <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-4">
                        <x-input-label for="course_cover_image_upload" :value="__('Course Cover Image')" />
                        <input type="file" id="course_cover_image_upload" name="course_cover_image_upload" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="pd-input mt-2">
                        <p class="mt-1.5 text-[0.62rem] leading-relaxed text-boss-ivory/25">{{ __('Optional. Used on academy cards and the course hero. If empty, the current gradient/Bunny thumbnail fallback stays in place.') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('course_cover_image_upload')" />
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <x-input-label for="difficulty_level" :value="__('Difficulty Level')" />
                            <input type="text" id="difficulty_level" name="difficulty_level" class="pd-input mt-2" value="{{ old('difficulty_level', 'Beginner friendly') }}" placeholder="{{ __('Beginner friendly') }}">
                            <x-input-error class="mt-2" :messages="$errors->get('difficulty_level')" />
                        </div>
                        <div>
                            <x-input-label for="estimated_duration" :value="__('Estimated Duration')" />
                            <input type="text" id="estimated_duration" name="estimated_duration" class="pd-input mt-2" value="{{ old('estimated_duration') }}" placeholder="{{ __('45 minutes') }}">
                            <x-input-error class="mt-2" :messages="$errors->get('estimated_duration')" />
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <x-input-label for="what_you_will_learn" :value="__('What Members Will Learn')" />
                            <textarea id="what_you_will_learn" name="what_you_will_learn" rows="4" class="pd-input mt-2" placeholder="{{ __('One point per line') }}">{{ old('what_you_will_learn') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('what_you_will_learn')" />
                        </div>
                        <div>
                            <x-input-label for="requirements" :value="__('Requirements')" />
                            <textarea id="requirements" name="requirements" rows="4" class="pd-input mt-2" placeholder="{{ __('One requirement per line') }}">{{ old('requirements') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('requirements')" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-[1fr_180px] md:items-end">
                        <label for="is_published" class="flex items-start gap-2.5 rounded-lg border border-white/[0.06] bg-white/[0.03] p-3">
                            <input id="is_published" name="is_published" type="checkbox" value="1" class="mt-0.5 rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold" {{ old('is_published') ? 'checked' : '' }}>
                            <span>
                                <span class="block text-[0.78rem] text-boss-ivory">{{ __('Published') }}</span>
                                <span class="mt-0.5 block text-[0.68rem] text-boss-ivory/32">{{ __('Visible to approved members after saving.') }}</span>
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

            {{-- ② COURSE MATERIALS --}}
            <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#0E0E1A]">
                <div class="border-b border-white/[0.05] bg-white/[0.01] px-5 py-3">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[0.6rem] font-bold" x-bind:style="`background-color: ${platformColor}25; color: ${platformColor};`">2</span>
                        <div>
                            <p class="pd-heading text-[0.9rem] text-boss-gold">{{ __('Course Materials') }}</p>
                            <p class="mt-0.5 text-[0.65rem] text-boss-ivory/30">{{ __('Optional — members will see these before the lessons begin.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 p-5">

                    {{-- Course Outline PDF --}}
                    <div class="overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.02]">
                        <div class="flex items-center gap-3 px-4 py-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-white/[0.06] bg-white/[0.04]" x-bind:style="`color: ${platformColor};`">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-[0.8rem] text-boss-ivory">{{ __('Course Outline / PDF Guide') }}</p>
                                <p class="text-[0.65rem] text-boss-ivory/30">{{ __('Shown to members as the first item — acts as a course outline.') }}</p>
                            </div>
                            {{-- Toggle --}}
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" name="has_course_outline" value="1" x-model="hasCourseOutline" class="peer sr-only" {{ old('has_course_outline') ? 'checked' : '' }}>
                                <div class="peer h-5 w-9 rounded-full border border-white/10 bg-white/[0.08] transition-colors peer-checked:border-boss-gold/40 peer-checked:bg-boss-gold/20"></div>
                                <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white/30 transition-transform peer-checked:translate-x-4 peer-checked:bg-boss-gold"></div>
                            </label>
                        </div>

                        <div x-show="hasCourseOutline" x-transition class="border-t border-white/[0.05] px-4 pb-4 pt-3">
                            <x-input-label for="course_outline_url" :value="__('PDF URL')" />
                            <input type="text" id="course_outline_url" name="course_outline_url" class="pd-input mt-2" value="{{ old('course_outline_url') }}" placeholder="https://...">
                            <p class="mt-1.5 text-[0.62rem] text-boss-ivory/20">{{ __('Paste a direct link to the PDF. Members will see this as "Course Outline" at the top.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('course_outline_url')" />
                        </div>
                    </div>

                    {{-- Course Introduction --}}
                    <div class="overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.02]">
                        <div class="flex items-center gap-3 px-4 py-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-white/[0.06] bg-white/[0.04]" x-bind:style="`color: ${platformColor};`">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-[0.8rem] text-boss-ivory">{{ __('Course Introduction') }}</p>
                                <p class="text-[0.65rem] text-boss-ivory/30">{{ __('An intro video or orientation shown before Lesson 1.') }}</p>
                            </div>
                            {{-- Toggle --}}
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" name="has_intro" value="1" x-model="hasIntro" class="peer sr-only" {{ old('has_intro') ? 'checked' : '' }}>
                                <div class="peer h-5 w-9 rounded-full border border-white/10 bg-white/[0.08] transition-colors peer-checked:border-boss-gold/40 peer-checked:bg-boss-gold/20"></div>
                                <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white/30 transition-transform peer-checked:translate-x-4 peer-checked:bg-boss-gold"></div>
                            </label>
                        </div>

                        <div x-show="hasIntro" x-transition class="border-t border-white/[0.05] px-4 pb-4 pt-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <x-input-label for="intro_title" :value="__('Intro Title')" />
                                    <input type="text" id="intro_title" name="intro_title" class="pd-input mt-2" value="{{ old('intro_title', 'Course Orientation') }}" placeholder="{{ __('e.g. Course Orientation') }}">
                                    <x-input-error class="mt-1" :messages="$errors->get('intro_title')" />
                                </div>
                                <div class="sm:col-span-2">
                                    <input type="hidden" name="intro_video_url" x-bind:value="introVideo.video_url || ''">
                                    <input type="hidden" name="intro_bunny_video_id" x-bind:value="introVideo.bunny_video_id || ''">
                                    <input type="hidden" name="intro_bunny_library_id" x-bind:value="introVideo.bunny_library_id || ''">
                                    <input type="hidden" name="intro_bunny_video_title" x-bind:value="introVideo.bunny_video_title || ''">
                                    <input type="hidden" name="intro_bunny_thumbnail_url" x-bind:value="introVideo.bunny_thumbnail_url || ''">
                                    <input type="hidden" name="intro_bunny_upload_fingerprint" x-bind:value="introVideo.bunny_upload_fingerprint || ''">
                                    <input type="hidden" name="intro_bunny_status" x-bind:value="introVideo.bunny_status || ''">
                                    <input type="hidden" name="intro_duration" x-bind:value="introVideo.duration || ''">

                                    <x-input-label for="intro_bunny_video" :value="__('Introduction Bunny Video')" />
                                    <div class="mt-2 rounded-lg border border-white/[0.06] bg-white/[0.025] p-3">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                            <div class="flex h-20 w-full shrink-0 items-center justify-center overflow-hidden rounded-md border border-white/[0.06] bg-[#08080f] text-[0.62rem] text-boss-ivory/25 sm:w-32">
                                                <img x-show="introVideo.bunny_thumbnail_url" x-bind:src="introVideo.bunny_thumbnail_url" x-bind:alt="introVideo.bunny_video_title || '{{ __('Course Introduction') }}'" class="h-full w-full object-cover">
                                                <span x-show="!introVideo.bunny_thumbnail_url">{{ __('No video') }}</span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-[0.78rem] text-boss-ivory" x-text="introVideo.bunny_video_title || '{{ __('No Bunny video selected') }}'"></p>
                                                <p class="mt-1 text-[0.62rem] text-boss-ivory/28">
                                                    <span x-show="introVideo.bunny_video_id" x-text="introVideo.duration ? `${introVideo.duration} · ${introVideo.bunny_video_id}` : introVideo.bunny_video_id"></span>
                                                    <span x-show="!introVideo.bunny_video_id">{{ __('Select an existing Bunny video or upload a new one.') }}</span>
                                                </p>
                                                <div x-show="uploads.intro" class="mt-2">
                                                    <div class="h-1.5 overflow-hidden rounded-full bg-white/[0.06]">
                                                        <div class="h-full rounded-full bg-boss-gold transition-all" x-bind:style="`width: ${uploads.intro?.progress || 0}%`"></div>
                                                    </div>
                                                    <p class="mt-1 text-[0.62rem]" x-bind:class="uploads.intro?.error ? 'text-red-300' : 'text-boss-ivory/32'" x-text="uploads.intro?.error || uploads.intro?.status"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <button type="button" @click="openIntroBunnyPicker()" class="rounded-lg border border-boss-gold/20 bg-boss-gold/10 px-3 py-1.5 text-[0.68rem] text-boss-gold transition-colors hover:bg-boss-gold/15">
                                                {{ __('Select Existing Bunny Video') }}
                                            </button>
                                            <label class="cursor-pointer rounded-lg border border-white/[0.07] bg-white/[0.04] px-3 py-1.5 text-[0.68rem] text-boss-ivory/45 transition-colors hover:text-boss-gold">
                                                {{ __('Upload New Bunny Video') }}
                                                <input id="intro_bunny_video" type="file" accept="video/*" class="hidden" @change="uploadIntroBunnyVideo($event)">
                                            </label>
                                            <button x-show="introVideo.bunny_video_id" type="button" @click="clearIntroVideo()" class="rounded-lg border border-red-400/10 bg-red-400/[0.05] px-3 py-1.5 text-[0.68rem] text-red-300/70 transition-colors hover:text-red-200">
                                                {{ __('Remove Video') }}
                                            </button>
                                        </div>
                                    </div>
                                    <x-input-error class="mt-1" :messages="$errors->get('intro_video_url')" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="intro_body" :value="__('Intro Description')" />
                                    <textarea id="intro_body" name="intro_body" rows="2" class="pd-input mt-2" placeholder="{{ __('Brief overview of what members will learn in this course.') }}">{{ old('intro_body') }}</textarea>
                                    <x-input-error class="mt-1" :messages="$errors->get('intro_body')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-[0.62rem] text-boss-ivory/20">
                        {{ __('Member view order: Course Outline (if on) → Introduction (if on) → Lesson 1, 2, 3…') }}
                    </p>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#0E0E1A]">
                <div class="flex items-center justify-between border-b border-white/[0.05] bg-white/[0.01] px-5 py-3">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[0.6rem] font-bold" x-bind:style="`background-color: ${platformColor}25; color: ${platformColor};`">3</span>
                        <div>
                            <p class="pd-heading text-[0.9rem] text-boss-gold">{{ __('Modules') }} <span class="text-boss-ivory/30" x-text="`(${modules.length})`"></span></p>
                            <p class="mt-0.5 text-[0.65rem] text-boss-ivory/30">{{ __('Group lessons into a guided course path.') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="addModule()" class="rounded-lg border border-boss-gold/20 bg-boss-gold/10 px-3 py-1.5 text-[0.7rem] text-boss-gold transition-colors hover:bg-boss-gold/15">
                        {{ __('+ Add Module') }}
                    </button>
                </div>

                <div class="space-y-3 p-5">
                    <template x-for="(module, moduleIndex) in modules" :key="module.client_key">
                        <div class="overflow-hidden rounded-xl border border-white/[0.05] bg-[#131320]">
                            <div class="flex items-center gap-2 border-b border-white/[0.05] bg-white/[0.01] px-3 py-2">
                                <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-[0.62rem] font-semibold" x-bind:style="`background-color: ${platformColor}20; color: ${platformColor}; border-color: ${platformColor}30;`" x-text="moduleIndex + 1"></div>
                                <p class="text-[0.7rem] text-boss-ivory/40" x-text="module.title || `Module ${moduleIndex + 1}`"></p>
                                <div class="ml-auto flex items-center gap-1">
                                    <button type="button" @click="moveModule(moduleIndex, -1)" x-bind:disabled="moduleIndex === 0" class="rounded border border-white/[0.06] bg-white/[0.03] px-2 py-1 text-[0.65rem] text-boss-ivory/35 transition-colors hover:text-boss-gold disabled:opacity-30">{{ __('Up') }}</button>
                                    <button type="button" @click="moveModule(moduleIndex, 1)" x-bind:disabled="moduleIndex === modules.length - 1" class="rounded border border-white/[0.06] bg-white/[0.03] px-2 py-1 text-[0.65rem] text-boss-ivory/35 transition-colors hover:text-boss-gold disabled:opacity-30">{{ __('Down') }}</button>
                                    <button type="button" @click="removeModule(moduleIndex)" class="rounded border border-red-400/10 bg-red-400/[0.05] px-2 py-1 text-[0.65rem] text-red-400/60 transition-colors hover:text-red-300">{{ __('Remove') }}</button>
                                </div>
                            </div>

                            <div class="grid gap-2.5 p-3 sm:grid-cols-2">
                                <input type="hidden" x-bind:name="`modules[${moduleIndex}][id]`" x-bind:value="module.id || ''">
                                <input type="hidden" x-bind:name="`modules[${moduleIndex}][client_key]`" x-bind:value="module.client_key">
                                <input type="hidden" x-bind:name="`modules[${moduleIndex}][sort_order]`" x-bind:value="moduleIndex + 1">

                                <div>
                                    <x-input-label ::for="`module_title_${moduleIndex}`" :value="__('Module Title')" />
                                    <input type="text" required class="pd-input mt-2" x-model="module.title" x-bind:id="`module_title_${moduleIndex}`" x-bind:name="`modules[${moduleIndex}][title]`" placeholder="{{ __('Getting Started') }}">
                                </div>

                                <label class="flex items-start gap-2.5 rounded-lg border border-white/[0.06] bg-white/[0.03] p-3">
                                    <input type="hidden" x-bind:name="`modules[${moduleIndex}][is_published]`" value="0">
                                    <input type="checkbox" value="1" x-model="module.is_published" x-bind:name="`modules[${moduleIndex}][is_published]`" class="mt-0.5 rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold">
                                    <span>
                                        <span class="block text-[0.78rem] text-boss-ivory">{{ __('Published') }}</span>
                                        <span class="mt-0.5 block text-[0.68rem] text-boss-ivory/32">{{ __('Visible to enrolled members.') }}</span>
                                    </span>
                                </label>

                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`module_description_${moduleIndex}`" :value="__('Module Description')" />
                                    <textarea rows="2" class="pd-input mt-2" x-model="module.description" x-bind:id="`module_description_${moduleIndex}`" x-bind:name="`modules[${moduleIndex}][description]`" placeholder="{{ __('Optional context for this part of the training.') }}"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            {{-- Lessons --}}
            <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#0E0E1A]">
                <div class="flex items-center justify-between border-b border-white/[0.05] bg-white/[0.01] px-5 py-3">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[0.6rem] font-bold" x-bind:style="`background-color: ${platformColor}25; color: ${platformColor};`">4</span>
                        <p class="pd-heading text-[0.9rem] text-boss-gold">
                            {{ __('Lessons') }} <span class="text-boss-ivory/30" x-text="`(${lessons.length})`"></span>
                        </p>
                    </div>
                    <button type="button" @click="addLesson()" class="rounded-lg border border-boss-gold/20 bg-boss-gold/10 px-3 py-1.5 text-[0.7rem] text-boss-gold transition-colors hover:bg-boss-gold/15">
                        {{ __('+ Add Lesson') }}
                    </button>
                </div>

                <div class="space-y-3 p-5">
                    <x-input-error class="mt-2" :messages="$errors->get('lessons')" />
                    <x-input-error class="mt-2" :messages="$errors->get('lessons.*.lesson_banner_image_upload')" />
                    <x-input-error class="mt-2" :messages="$errors->get('lessons.*.lesson_images_upload.*')" />
                    <x-input-error class="mt-2" :messages="$errors->get('lessons.*.content_blocks.*.image_upload')" />
                    <x-input-error class="mt-2" :messages="$errors->get('lessons.*.content_blocks.*.gallery_uploads.*')" />
                    <x-input-error class="mt-2" :messages="$errors->get('lessons.*.content_blocks.*.file_upload')" />

                    <template x-for="(lesson, index) in lessons" :key="index">
                        <div class="overflow-hidden rounded-xl border border-white/[0.05] bg-[#131320]">
                            <div class="flex items-center gap-2 border-b border-white/[0.05] bg-white/[0.01] px-3 py-2">
                                <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-[0.62rem] font-semibold" x-bind:style="`background-color: ${platformColor}20; color: ${platformColor}; border-color: ${platformColor}30;`" x-text="index + 1"></div>
                                <p class="text-[0.7rem] text-boss-ivory/40" x-text="`Lesson ${index + 1}`"></p>
                                <button type="button" @click="removeLesson(index)" class="ml-auto rounded border border-red-400/10 bg-red-400/[0.05] px-2 py-1 text-[0.65rem] text-red-400/60 transition-colors hover:text-red-300">
                                    {{ __('Remove') }}
                                </button>
                            </div>

                            <div class="grid gap-2.5 p-3 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`lesson_title_${index}`" :value="__('Lesson Title')" />
                                    <input type="text" required class="pd-input mt-2" x-model="lesson.title" x-bind:id="`lesson_title_${index}`" x-bind:name="`lessons[${index}][title]`" placeholder="{{ __('Account setup and profile optimisation') }}">
                                </div>

                                <div>
                                    <x-input-label ::for="`lesson_module_${index}`" :value="__('Module')" />
                                    <input type="hidden" x-bind:name="`lessons[${index}][course_module_id]`" x-bind:value="moduleIdForKey(lesson.module_key)">
                                    <input type="hidden" x-bind:name="`lessons[${index}][module_title]`" x-bind:value="moduleTitleForKey(lesson.module_key)">
                                    <select class="pd-input mt-2" x-model="lesson.module_key" x-bind:id="`lesson_module_${index}`" x-bind:name="`lessons[${index}][module_key]`" @change="syncLessonModule(lesson)">
                                        <template x-for="module in modules" :key="module.client_key">
                                            <option x-bind:value="module.client_key" x-text="module.title || 'Untitled Module'"></option>
                                        </template>
                                    </select>
                                </div>

                                <label class="flex items-start gap-2.5 rounded-lg border border-white/[0.06] bg-white/[0.03] p-3">
                                    <input type="hidden" x-bind:name="`lessons[${index}][is_published]`" value="0">
                                    <input type="checkbox" value="1" x-model="lesson.is_published" x-bind:name="`lessons[${index}][is_published]`" class="mt-0.5 rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold">
                                    <span>
                                        <span class="block text-[0.78rem] text-boss-ivory">{{ __('Published') }}</span>
                                        <span class="mt-0.5 block text-[0.68rem] text-boss-ivory/32">{{ __('Visible inside the course.') }}</span>
                                    </span>
                                </label>

                                <div class="sm:col-span-2">
                                    <input type="hidden" x-bind:name="`lessons[${index}][video_url]`" x-bind:value="lesson.video_url || ''">
                                    <input type="hidden" x-bind:name="`lessons[${index}][bunny_video_id]`" x-bind:value="lesson.bunny_video_id || ''">
                                    <input type="hidden" x-bind:name="`lessons[${index}][bunny_library_id]`" x-bind:value="lesson.bunny_library_id || ''">
                                    <input type="hidden" x-bind:name="`lessons[${index}][bunny_video_title]`" x-bind:value="lesson.bunny_video_title || ''">
                                    <input type="hidden" x-bind:name="`lessons[${index}][bunny_thumbnail_url]`" x-bind:value="lesson.bunny_thumbnail_url || ''">
                                    <input type="hidden" x-bind:name="`lessons[${index}][bunny_upload_fingerprint]`" x-bind:value="lesson.bunny_upload_fingerprint || ''">
                                    <input type="hidden" x-bind:name="`lessons[${index}][bunny_status]`" x-bind:value="lesson.bunny_status || ''">
                                    <input type="hidden" x-bind:name="`lessons[${index}][duration]`" x-bind:value="lesson.duration || ''">

                                    <x-input-label ::for="`lesson_video_${index}`" :value="__('Bunny Video')" />
                                    <div class="mt-2 rounded-lg border border-white/[0.06] bg-white/[0.025] p-3">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                            <div class="flex h-20 w-full shrink-0 items-center justify-center overflow-hidden rounded-md border border-white/[0.06] bg-[#08080f] text-[0.62rem] text-boss-ivory/25 sm:w-32">
                                                <img x-show="lesson.bunny_thumbnail_url" x-bind:src="lesson.bunny_thumbnail_url" x-bind:alt="lesson.bunny_video_title || lesson.title" class="h-full w-full object-cover">
                                                <span x-show="!lesson.bunny_thumbnail_url">{{ __('No video') }}</span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-[0.78rem] text-boss-ivory" x-text="lesson.bunny_video_title || '{{ __('No Bunny video selected') }}'"></p>
                                                <p class="mt-1 text-[0.62rem] text-boss-ivory/28">
                                                    <span x-show="lesson.bunny_video_id" x-text="lesson.duration ? `${lesson.duration} · ${lesson.bunny_video_id}` : lesson.bunny_video_id"></span>
                                                    <span x-show="!lesson.bunny_video_id">{{ __('Select an existing Bunny video or upload a new one.') }}</span>
                                                </p>
                                                <div x-show="uploads[index]" class="mt-2">
                                                    <div class="h-1.5 overflow-hidden rounded-full bg-white/[0.06]">
                                                        <div class="h-full rounded-full bg-boss-gold transition-all" x-bind:style="`width: ${uploads[index]?.progress || 0}%`"></div>
                                                    </div>
                                                    <p class="mt-1 text-[0.62rem]" x-bind:class="uploads[index]?.error ? 'text-red-300' : 'text-boss-ivory/32'" x-text="uploads[index]?.error || uploads[index]?.status"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <button type="button" @click="openBunnyPicker(index)" class="rounded-lg border border-boss-gold/20 bg-boss-gold/10 px-3 py-1.5 text-[0.68rem] text-boss-gold transition-colors hover:bg-boss-gold/15">
                                                {{ __('Select Existing Bunny Video') }}
                                            </button>
                                            <label class="cursor-pointer rounded-lg border border-white/[0.07] bg-white/[0.04] px-3 py-1.5 text-[0.68rem] text-boss-ivory/45 transition-colors hover:text-boss-gold">
                                                {{ __('Upload New Bunny Video') }}
                                                <input type="file" accept="video/*" class="hidden" @change="uploadBunnyVideo(index, $event)">
                                            </label>
                                            <button x-show="lesson.bunny_video_id" type="button" @click="lesson.bunny_video_id = ''; lesson.bunny_library_id = ''; lesson.bunny_video_title = ''; lesson.bunny_thumbnail_url = ''; lesson.video_url = ''; lesson.duration = ''" class="rounded-lg border border-red-400/10 bg-red-400/[0.05] px-3 py-1.5 text-[0.68rem] text-red-300/70 transition-colors hover:text-red-200">
                                                {{ __('Remove Video') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="sm:col-span-2 rounded-lg border border-white/[0.06] bg-white/[0.025] p-3">
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <div>
                                            <x-input-label ::for="`lesson_banner_image_${index}`" :value="__('Lesson Banner Image')" />
                                            <input type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="pd-input mt-2" x-bind:id="`lesson_banner_image_${index}`" x-bind:name="`lessons[${index}][lesson_banner_image_upload]`">
                                            <p class="mt-1 text-[0.6rem] text-boss-ivory/22">{{ __('Optional image shown above this lesson.') }}</p>
                                        </div>
                                        <div>
                                            <x-input-label ::for="`lesson_images_${index}`" :value="__('Lesson Gallery Images')" />
                                            <input type="file" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="pd-input mt-2" x-bind:id="`lesson_images_${index}`" x-bind:name="`lessons[${index}][lesson_images_upload][]`">
                                            <p class="mt-1 text-[0.6rem] text-boss-ivory/22">{{ __('Optional screenshots, examples, or walkthrough images.') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`lesson_body_${index}`" :value="__('Lesson Description')" />
                                    <textarea rows="2" class="pd-input mt-2" x-model="lesson.body" x-bind:id="`lesson_body_${index}`" x-bind:name="`lessons[${index}][body]`" placeholder="{{ __('What will members learn in this lesson?') }}"></textarea>
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`lesson_overview_${index}`" :value="__('Overview Content')" />
                                    <textarea rows="3" class="pd-input mt-2" x-model="lesson.overview" x-bind:id="`lesson_overview_${index}`" x-bind:name="`lessons[${index}][overview]`" placeholder="{{ __('Plain-language explanation for this lesson.') }}"></textarea>
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`lesson_steps_${index}`" :value="__('Step-by-step Guide')" />
                                    <textarea rows="4" class="pd-input mt-2" x-model="lesson.steps" x-bind:id="`lesson_steps_${index}`" x-bind:name="`lessons[${index}][steps]`" placeholder="{{ __('One step per line') }}"></textarea>
                                </div>

                                <div>
                                    <x-input-label ::for="`lesson_tips_${index}`" :value="__('Important Tips')" />
                                    <textarea rows="4" class="pd-input mt-2" x-model="lesson.tips" x-bind:id="`lesson_tips_${index}`" x-bind:name="`lessons[${index}][tips]`" placeholder="{{ __('One tip per line') }}"></textarea>
                                </div>

                                <div>
                                    <x-input-label ::for="`lesson_safety_${index}`" :value="__('Safety Notes')" />
                                    <textarea rows="4" class="pd-input mt-2" x-model="lesson.safety_notes" x-bind:id="`lesson_safety_${index}`" x-bind:name="`lessons[${index}][safety_notes]`" placeholder="{{ __('One note per line') }}"></textarea>
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`lesson_resources_${index}`" :value="__('Resource Links')" />
                                    <textarea rows="3" class="pd-input mt-2" x-model="lesson.resource_links" x-bind:id="`lesson_resources_${index}`" x-bind:name="`lessons[${index}][resource_links]`" placeholder="{{ __('Label | https://example.com') }}"></textarea>
                                    <p class="mt-1 text-[0.6rem] text-boss-ivory/20">{{ __('Use one resource per line. Format: Label | URL') }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`lesson_pdf_${index}`" :value="__('PDF URL (optional)')" />
                                    <input type="text" class="pd-input mt-2" x-model="lesson.pdf_url" x-bind:id="`lesson_pdf_${index}`" x-bind:name="`lessons[${index}][pdf_url]`" placeholder="https://... (leave blank if lesson is video only)">
                                    <p class="mt-1 text-[0.6rem] text-boss-ivory/20">{{ __('If this lesson is a PDF, paste the link here. Leave blank for video-only lessons.') }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`lesson_presentation_${index}`" :value="__('Canva / presentation URL')" />
                                    <textarea rows="2" class="pd-input mt-2" x-model="lesson.presentation_url" x-bind:id="`lesson_presentation_${index}`" x-bind:name="`lessons[${index}][presentation_url]`" placeholder="https://www.canva.com/design/... or Canva iframe embed code"></textarea>
                                    <p class="mt-1 text-[0.6rem] text-boss-ivory/20">{{ __('Paste a Canva presentation URL or full Canva iframe embed code.') }}</p>
                                </div>

                                @include('admin.courses.partials.lesson-content-blocks')

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

        @include('admin.courses.partials.bunny-video-modal')
    </div>
</x-admin-layout>
