@php
    $initialModules = old('modules', $course->modules->map(fn ($module) => [
        'id' => $module->id,
        'client_key' => 'module-'.$module->id,
        'title' => $module->title,
        'description' => $module->description,
        'is_published' => $module->is_published,
        'sort_order' => $module->sort_order,
    ])->values()->all());

    if ($initialModules === []) {
        $initialModules = [[
            'id' => null,
            'client_key' => 'module-1',
            'title' => 'Core Training',
            'description' => '',
            'is_published' => true,
            'sort_order' => 1,
        ]];
    }

    $initialLessons = old('lessons', $course->lessons->map(fn ($lesson) => [
        'id' => $lesson->id,
        'course_id' => $lesson->course_id,
        'course_module_id' => $lesson->course_module_id,
        'module_key' => $lesson->course_module_id ? 'module-'.$lesson->course_module_id : ($initialModules[0]['client_key'] ?? 'module-1'),
        'module_title' => $lesson->module?->title ?? ($initialModules[0]['title'] ?? 'Core Training'),
        'title' => $lesson->title,
        'body' => $lesson->body,
        'overview' => $lesson->overview,
        'steps' => $lesson->steps,
        'tips' => $lesson->tips,
        'safety_notes' => $lesson->safety_notes,
        'resource_links' => $lesson->resource_links,
        'lesson_banner_image' => $lesson->lesson_banner_image,
        'lesson_banner_image_url' => $lesson->lessonBannerImageUrl(),
        'lesson_images' => $lesson->lesson_images ?? [],
        'lesson_image_urls' => $lesson->lessonImageUrls(),
        'content_blocks' => $lesson->contentBlocks->map(fn ($block) => [
            'id' => $block->id,
            'block_type' => $block->block_type,
            'title' => $block->title,
            'content' => $block->content,
            'image_path' => $block->image_path,
            'image_url' => $block->imageUrl(),
            'gallery_image_urls' => $block->galleryImageUrls(),
            'gallery_captions' => $block->galleryCaptions() !== [] ? implode("\n", $block->galleryCaptions()) : '',
            'file_path' => $block->file_path,
            'file_url' => $block->fileUrl(),
            'slide_images' => $block->settings['slide_images'] ?? [],
            'button_label' => $block->buttonLabel(''),
            'bunny_video_id' => $block->bunny_video_id,
            'bunny_library_id' => $block->bunny_library_id,
            'bunny_video_title' => $block->bunny_video_title,
            'bunny_thumbnail_url' => $block->bunny_thumbnail_url,
            'bunny_upload_fingerprint' => $block->bunny_upload_fingerprint,
            'bunny_status' => $block->bunny_status,
            'duration' => $block->duration,
            'presentation_url' => $block->presentation_url,
            'sort_order' => $block->sort_order,
        ])->values()->all(),
        'content_blocks_enabled' => true,
        'is_published' => $lesson->is_published,
        'video_url' => $lesson->video_url,
        'bunny_video_id' => $lesson->bunny_video_id,
        'bunny_library_id' => $lesson->bunny_library_id,
        'bunny_video_title' => $lesson->bunny_video_title,
        'bunny_thumbnail_url' => $lesson->bunny_thumbnail_url,
        'bunny_upload_fingerprint' => $lesson->bunny_upload_fingerprint,
        'bunny_status' => $lesson->bunny_status,
        'duration' => $lesson->duration,
        'pdf_url' => $lesson->pdf_url,
        'presentation_url' => $lesson->presentation_url,
        'sort_order' => $lesson->sort_order,
    ])->values()->all());

    if ($initialLessons === []) {
        $initialLessons = [[
            'id' => null,
            'course_id' => $course->id,
            'course_module_id' => '',
            'module_key' => $initialModules[0]['client_key'] ?? 'module-1',
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
        ]];
    }
@endphp

<x-admin-layout>
    <div
        class="mx-auto max-w-5xl space-y-5 pb-10 text-boss-ivory"
        x-data="adminCourseForm({
            courseId: @js($course->id),
            platform: @js(old('platform_label', $course->platform_label)),
            platformColor: @js(old('platform_color', $course->displayColor())),
            showSuggestions: false,
            hasCourseOutline: @js((bool) old('has_course_outline', $course->has_course_outline)),
            hasIntro: @js((bool) old('has_intro', $course->has_intro)),
            introVideo: @js([
                'video_url' => old('intro_video_url', $course->intro_video_url),
                'bunny_video_id' => old('intro_bunny_video_id', $course->intro_bunny_video_id),
                'bunny_library_id' => old('intro_bunny_library_id', $course->intro_bunny_library_id),
                'bunny_video_title' => old('intro_bunny_video_title', $course->intro_bunny_video_title),
                'bunny_thumbnail_url' => old('intro_bunny_thumbnail_url', $course->intro_bunny_thumbnail_url),
                'bunny_upload_fingerprint' => old('intro_bunny_upload_fingerprint', $course->intro_bunny_upload_fingerprint),
                'bunny_status' => old('intro_bunny_status', $course->intro_bunny_status),
                'duration' => old('intro_duration', $course->intro_duration),
            ]),
            modules: @js(array_values($initialModules)),
            lessons: @js(array_values($initialLessons)),
            suggestions: @js($platformSuggestions),
            colors: @js($colorSwatches),
            bunnyVideosUrl: @js(route('admin.bunny.videos.index')),
            bunnyUploadIntentUrl: @js(route('admin.bunny.videos.upload-intent')),
            bunnyVideoUrlTemplate: @js(route('admin.bunny.videos.show', ['videoId' => '__VIDEO_ID__'])),
            blockFileUploadUrl: @js(route('admin.courses.block-file')),
            lessonPreviewUrlTemplate: @js(route('admin.courses.lessons.preview', [$course, '__LESSON_ID__'])),
            autosaveUrls: {
                moduleSave: @js(route('admin.courses.modules.store', $course)),
                moduleDelete: @js(route('admin.courses.modules.store', $course)),
                lessonSave: @js(route('admin.courses.lessons.autosave', $course)),
                lessonDelete: @js(route('admin.courses.lessons.destroy', [$course, '__LESSON_ID__'])),
            },
        })"
    >
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.courses.index') }}" class="rounded-xl border border-white/[0.07] bg-white/[0.04] px-3 py-2 text-[0.78rem] text-boss-ivory/45 transition-colors hover:text-boss-gold">
                    &larr; {{ __('Courses') }}
                </a>
                <div>
                    <p class="pd-kicker">{{ __('Academy') }}</p>
                    <h1 class="pd-heading mt-1 text-[clamp(1.7rem,3vw,2.3rem)]">{{ __('Edit Course') }}</h1>
                </div>
            </div>
            <a href="{{ route('admin.courses.preview', $course) }}" class="pd-btn-secondary self-start sm:self-auto">
                {{ __('Preview Course') }}
            </a>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        {{-- Draft restoration banner --}}
        <div x-show="draftAvailable" x-cloak
            class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-boss-gold/25 bg-boss-gold/[0.07] px-4 py-3">
            <div>
                <p class="text-[0.82rem] font-medium text-boss-gold">{{ __('Unsaved draft found') }}</p>
                <p class="mt-0.5 text-[0.68rem] text-boss-ivory/50"
                    x-text="`${draftAvailable?.lessonCount} lesson(s), ${draftAvailable?.moduleCount} module(s) — saved ${draftAvailable ? new Date(draftAvailable.timestamp).toLocaleString() : ''}`">
                </p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="restoreDraft()"
                    class="rounded-lg border border-boss-gold/30 bg-boss-gold/15 px-3 py-1.5 text-[0.72rem] text-boss-gold transition-colors hover:bg-boss-gold/20">
                    {{ __('Restore draft') }}
                </button>
                <button type="button" @click="discardDraft()"
                    class="rounded-lg border border-white/[0.07] bg-white/[0.04] px-3 py-1.5 text-[0.72rem] text-boss-ivory/45 transition-colors hover:text-boss-ivory">
                    {{ __('Discard') }}
                </button>
            </div>
        </div>

        {{-- Autosave status bar --}}
        <div x-show="autosaveStatusLabel" x-cloak
            class="flex items-center gap-2 rounded-lg px-3 py-2 text-[0.68rem] transition-all"
            :class="{
                'border border-green-400/15 bg-green-400/[0.06] text-green-300': autosave.status === 'saved',
                'border border-boss-gold/15 bg-boss-gold/[0.05] text-boss-gold/70': autosave.status === 'saving',
                'border border-red-400/15 bg-red-400/[0.06] text-red-300': autosave.status === 'error',
                'border border-amber-400/15 bg-amber-400/[0.06] text-amber-300': autosave.status === 'offline',
                'border border-white/[0.06] bg-white/[0.02] text-boss-ivory/40': autosave.status === 'idle',
            }">
            <span x-show="autosave.status === 'saving'" class="inline-block h-3 w-3 animate-spin rounded-full border border-current border-t-transparent"></span>
            <span x-show="autosave.status === 'saved'">&#10003;</span>
            <span x-show="autosave.status === 'error'">&#9888;</span>
            <span x-show="autosave.status === 'offline'">&#9888;</span>
            <span x-text="autosaveStatusLabel"></span>
        </div>

        {{-- Step indicator --}}
        <div class="flex flex-wrap items-center gap-2 text-[0.68rem] text-boss-ivory/30">
            <span class="rounded-full px-2.5 py-0.5" x-bind:style="`background-color: ${platformColor}20; color: ${platformColor};`">① Course Details</span>
            <span class="text-boss-ivory/15">→</span>
            <span class="rounded-full px-2.5 py-0.5" x-bind:style="`background-color: ${platformColor}20; color: ${platformColor};`">② Course Materials</span>
            <span class="text-boss-ivory/15">→</span>
            <span class="rounded-full px-2.5 py-0.5" x-bind:style="`background-color: ${platformColor}20; color: ${platformColor};`">③ Modules + Lessons</span>
        </div>

        <form method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data" class="space-y-5" @submit="rebuildBlockHiddenInputs($event.target); debugCourseSubmit($event.target)">
            @csrf
            @method('PUT')
            {{-- Safety: tells the backend how many lessons the frontend sent.
                 If PHP's max_input_vars truncated the payload, this count will
                 not match and the backend will reject the save instead of
                 silently deleting the missing lessons. --}}
            <input type="hidden" name="_lesson_count" :value="lessons.length">

            {{-- ① COURSE DETAILS — 2-column layout --}}
            <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#0E0E1A]">
                <div class="border-b border-white/[0.05] bg-white/[0.01] px-5 py-3">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[0.6rem] font-bold" x-bind:style="`background-color: ${platformColor}25; color: ${platformColor};`">1</span>
                        <p class="pd-heading text-[0.9rem] text-boss-gold">{{ __('Course Details') }}</p>
                    </div>
                </div>

                <div class="p-5">
                    <div class="grid gap-6 lg:grid-cols-2 lg:items-start">

                        {{-- LEFT COLUMN: Platform → Accent → Cover Image → Title → Slug --}}
                        <div class="space-y-4">

                            {{-- Platform Name --}}
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

                            {{-- Accent Colour --}}
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
                                    <input id="platform_color" type="text" x-model="platformColor" class="w-24 rounded-lg border border-white/10 bg-white/[0.06] px-2.5 py-1.5 font-mono text-[0.72rem] text-boss-ivory focus:border-boss-gold/50 focus:outline-none" placeholder="#C9A96E">
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('platform_color')" />
                            </div>

                            {{-- Course Cover Image — drag-and-drop with existing preview --}}
                            <div>
                                <x-input-label for="course_cover_image_upload" :value="__('Course Cover Image')" />
                                <div
                                    x-data="{ drag: false, fileLabel: '', previewSrc: null }"
                                    @dragover.prevent="drag = true"
                                    @dragleave.prevent="drag = false"
                                    @drop.prevent="drag = false; const f = $event.dataTransfer?.files; if (f?.length && f[0].type.startsWith('image/')) { $refs.fileInput.files = f; fileLabel = f[0].name; previewSrc = URL.createObjectURL(f[0]); }"
                                    class="mt-2"
                                >
                                    @if ($course->courseCoverImageUrl())
                                        <div x-show="!previewSrc" class="mb-2 overflow-hidden rounded-xl border border-white/[0.06] bg-[#08080f]">
                                            <img src="{{ $course->courseCoverImageUrl() }}" alt="{{ $course->title }}" class="h-32 w-full object-cover">
                                            <p class="px-3 py-1.5 text-[0.6rem] text-boss-ivory/25">{{ __('Current saved image') }}</p>
                                        </div>
                                    @endif
                                    {{-- Instant preview of newly selected replacement image --}}
                                    <template x-if="previewSrc">
                                        <div class="mb-2 overflow-hidden rounded-xl border border-boss-gold/25 bg-[#08080f]">
                                            <img :src="previewSrc" alt="" class="h-32 w-full object-cover">
                                            <div class="flex items-center justify-between gap-3 px-3 py-1.5">
                                                <p class="text-[0.6rem] text-boss-gold/60">{{ __('New image selected — will replace current image on save') }}</p>
                                                <button type="button" class="text-[0.6rem] text-boss-ivory/35 transition-colors hover:text-boss-gold" @click="previewSrc = null; fileLabel = ''; $refs.fileInput.value = ''">{{ __('Clear') }}</button>
                                            </div>
                                        </div>
                                    </template>
                                    <label
                                        for="course_cover_image_upload"
                                        class="flex min-h-[7rem] cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed px-4 py-5 text-center transition-all duration-200"
                                        :class="drag ? 'border-boss-gold/70 bg-boss-gold/[0.07]' : (previewSrc ? 'border-boss-gold/30 bg-boss-gold/[0.03]' : 'border-white/[0.10] bg-white/[0.025] hover:border-boss-gold/30 hover:bg-white/[0.035]')"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                            class="h-6 w-6 transition-colors duration-200"
                                            :class="drag ? 'text-boss-gold/80' : (previewSrc ? 'text-boss-gold/50' : 'text-boss-ivory/20')">
                                            <path d="M12 15V3m0 0l-4 4m4-4 4 4"/>
                                            <path d="M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2"/>
                                        </svg>
                                        <span class="text-[0.58rem] font-semibold uppercase tracking-[0.15em] transition-colors duration-200" :class="drag ? 'text-boss-gold' : (previewSrc ? 'text-boss-gold/70' : 'text-boss-gold/55')">
                                            <span x-text="previewSrc ? '{{ __('CHANGE IMAGE') }}' : '{{ __('DROP IMAGE HERE') }}'"></span>
                                        </span>
                                        <span class="text-[0.75rem] text-boss-ivory/42">{{ __('Drag and drop, or click to browse') }}</span>
                                        <span class="text-[0.65rem] text-boss-ivory/28" x-text="fileLabel || '{{ __('No file selected') }}'"></span>
                                        <span class="mt-0.5 text-[0.58rem] text-boss-ivory/18">{{ __('Upload JPG, PNG, WEBP') }}</span>
                                        <input
                                            type="file"
                                            id="course_cover_image_upload"
                                            name="course_cover_image_upload"
                                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                            class="sr-only"
                                            x-ref="fileInput"
                                            @change="const f = $event.target.files; if (f.length) { fileLabel = f[0].name; previewSrc = URL.createObjectURL(f[0]); } else { fileLabel = ''; previewSrc = null; }"
                                        >
                                    </label>
                                </div>
                                <p class="mt-1.5 text-[0.62rem] leading-relaxed text-boss-ivory/25">{{ __('Optional. Upload a new image to replace the current cover. If empty, the current visual fallback stays in place.') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('course_cover_image_upload')" />
                            </div>

                            {{-- Course Title --}}
                            <div>
                                <x-input-label for="title" :value="__('Course Title')" />
                                <x-text-input id="title" name="title" type="text" class="mt-2" :value="old('title', $course->title)" required placeholder="{{ __('Give your course a clear title') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('title')" />
                            </div>

                            {{-- Slug --}}
                            <div>
                                <x-input-label for="slug" :value="__('Slug')" />
                                <input type="text" id="slug" name="slug" class="pd-input mt-2" value="{{ old('slug', $course->slug) }}" placeholder="{{ __('leave blank to generate from title') }}">
                                <p class="mt-1 text-[0.6rem] text-boss-ivory/20">{{ __('Optional. Used in the course URL.') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                            </div>

                        </div>{{-- /LEFT COLUMN --}}

                        {{-- RIGHT COLUMN: Descriptions → Meta → Learn/Requirements → Published --}}
                        <div class="space-y-4">

                            {{-- Short Description --}}
                            <div>
                                <x-input-label for="short_description" :value="__('Short Description')" />
                                <textarea id="short_description" name="short_description" rows="2" class="pd-input mt-2" placeholder="{{ __('One or two lines for catalog cards and quick previews') }}">{{ old('short_description', $course->short_description) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('short_description')" />
                            </div>

                            {{-- Full Description --}}
                            <div>
                                <x-input-label for="description" :value="__('Full Description')" />
                                <textarea id="description" name="description" rows="5" class="pd-input mt-2" required placeholder="{{ __('Describe what models will learn') }}">{{ old('description', $course->description) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                            </div>

                            {{-- Thumbnail URL --}}
                            <div>
                                <x-input-label for="thumbnail_url" :value="__('Course Banner / Thumbnail URL')" />
                                <input type="text" id="thumbnail_url" name="thumbnail_url" class="pd-input mt-2" value="{{ old('thumbnail_url', $course->thumbnail_url) }}" placeholder="https://...">
                                <p class="mt-1 text-[0.6rem] text-boss-ivory/20">{{ __('Optional. Bunny thumbnails are used as a fallback.') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('thumbnail_url')" />
                            </div>

                            {{-- Difficulty + Duration --}}
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="difficulty_level" :value="__('Difficulty Level')" />
                                    <input type="text" id="difficulty_level" name="difficulty_level" class="pd-input mt-2" value="{{ old('difficulty_level', $course->difficulty_level) }}" placeholder="{{ __('Beginner friendly') }}">
                                    <x-input-error class="mt-2" :messages="$errors->get('difficulty_level')" />
                                </div>
                                <div>
                                    <x-input-label for="estimated_duration" :value="__('Estimated Duration')" />
                                    <input type="text" id="estimated_duration" name="estimated_duration" class="pd-input mt-2" value="{{ old('estimated_duration', $course->estimated_duration) }}" placeholder="{{ __('45 minutes') }}">
                                    <x-input-error class="mt-2" :messages="$errors->get('estimated_duration')" />
                                </div>
                            </div>

                            {{-- What You'll Learn + Requirements --}}
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="what_you_will_learn" :value="__('What Members Will Learn')" />
                                    <textarea id="what_you_will_learn" name="what_you_will_learn" rows="4" class="pd-input mt-2" placeholder="{{ __('One point per line') }}">{{ old('what_you_will_learn', $course->what_you_will_learn) }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('what_you_will_learn')" />
                                </div>
                                <div>
                                    <x-input-label for="requirements" :value="__('Requirements')" />
                                    <textarea id="requirements" name="requirements" rows="4" class="pd-input mt-2" placeholder="{{ __('One requirement per line') }}">{{ old('requirements', $course->requirements) }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('requirements')" />
                                </div>
                            </div>

                            {{-- Published + Sort Order --}}
                            <div class="grid gap-4 sm:grid-cols-[1fr_160px] sm:items-end">
                                <label for="is_published" class="flex items-start gap-2.5 rounded-lg border border-white/[0.06] bg-white/[0.03] p-3">
                                    <input type="hidden" name="is_published" value="0">
                                    <input id="is_published" name="is_published" type="checkbox" value="1" class="mt-0.5 rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold" @checked(old('is_published', $course->is_published))>
                                    <span>
                                        <span class="block text-[0.78rem] text-boss-ivory">{{ __('Published') }}</span>
                                        <span class="mt-0.5 block text-[0.68rem] text-boss-ivory/32">{{ __('Visible to approved members after saving.') }}</span>
                                    </span>
                                </label>
                                <div>
                                    <x-input-label for="sort_order" :value="__('Sort order')" />
                                    <x-text-input id="sort_order" name="sort_order" type="number" class="mt-2" :value="old('sort_order', $course->sort_order)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                                </div>
                            </div>

                        </div>{{-- /RIGHT COLUMN --}}
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
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="hidden" name="has_course_outline" value="0">
                                <input type="checkbox" name="has_course_outline" value="1" x-model="hasCourseOutline" class="peer sr-only" @checked(old('has_course_outline', $course->has_course_outline))>
                                <div class="peer h-5 w-9 rounded-full border border-white/10 bg-white/[0.08] transition-colors peer-checked:border-boss-gold/40 peer-checked:bg-boss-gold/20"></div>
                                <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white/30 transition-transform peer-checked:translate-x-4 peer-checked:bg-boss-gold"></div>
                            </label>
                        </div>

                        <div x-show="hasCourseOutline" x-transition class="border-t border-white/[0.05] px-4 pb-4 pt-3">
                            <x-input-label for="course_outline_upload" :value="__('Guide File')" />
                            <div class="mt-2">
                                @include('admin.courses.partials.course-outline-upload', ['course' => $course])
                            </div>
                        </div>
                    </div>

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
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="hidden" name="has_intro" value="0">
                                <input type="checkbox" name="has_intro" value="1" x-model="hasIntro" class="peer sr-only" @checked(old('has_intro', $course->has_intro))>
                                <div class="peer h-5 w-9 rounded-full border border-white/10 bg-white/[0.08] transition-colors peer-checked:border-boss-gold/40 peer-checked:bg-boss-gold/20"></div>
                                <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white/30 transition-transform peer-checked:translate-x-4 peer-checked:bg-boss-gold"></div>
                            </label>
                        </div>

                        <div x-show="hasIntro" x-transition class="border-t border-white/[0.05] px-4 pb-4 pt-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <x-input-label for="intro_title" :value="__('Intro Title')" />
                                    <input type="text" id="intro_title" name="intro_title" class="pd-input mt-2" value="{{ old('intro_title', $course->intro_title ?? 'Course Orientation') }}" placeholder="{{ __('e.g. Course Orientation') }}">
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
                                    <textarea id="intro_body" name="intro_body" rows="2" class="pd-input mt-2" placeholder="{{ __('Brief overview of what members will learn in this course.') }}">{{ old('intro_body', $course->intro_body) }}</textarea>
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

            {{-- ③ MODULES + LESSONS — Tab-based compact editor --}}
            <div
                x-data="{ activeSection: 'modules', activeModuleTab: 0, lessonModuleFilter: 0, activeLessonKey: null }"
                x-init="lessonModuleFilter = activeModuleKey(activeModuleTab); activeLessonKey = firstLessonKeyForFilter(lessonModuleFilter)"
                x-effect="activeLessonKey = lessonsForFilter(lessonModuleFilter).some((lesson) => lessonKey(lesson) === activeLessonKey) ? activeLessonKey : firstLessonKeyForFilter(lessonModuleFilter)"
                @pd:lesson-id-updated.window="if (activeLessonKey === $event.detail.oldKey) activeLessonKey = $event.detail.newKey"
                class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#0E0E1A]"
            >
                {{-- Section switcher header --}}
                <div class="flex flex-wrap items-center gap-3 border-b border-white/[0.05] bg-white/[0.01] px-5 py-3">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[0.6rem] font-bold" x-bind:style="`background-color: ${platformColor}25; color: ${platformColor};`">3</span>
                    <div class="flex gap-1">
                        <button type="button" @click="activeSection = 'modules'"
                            class="rounded-lg border px-4 py-1.5 text-[0.75rem] font-medium transition-colors"
                            :class="activeSection === 'modules' ? 'border-boss-gold/30 bg-boss-gold/[0.12] text-boss-gold' : 'border-white/[0.07] bg-white/[0.04] text-boss-ivory/45 hover:text-boss-ivory/75'">
                            {{ __('Modules') }} <span class="opacity-60" x-text="`(${modules.length})`"></span>
                        </button>
                        <button type="button" @click="activeSection = 'lessons'; lessonModuleFilter = activeModuleKey(activeModuleTab); lessonMoveNotice = null; activeLessonKey = firstLessonKeyForFilter(lessonModuleFilter)"
                            class="rounded-lg border px-4 py-1.5 text-[0.75rem] font-medium transition-colors"
                            :class="activeSection === 'lessons' ? 'border-boss-gold/30 bg-boss-gold/[0.12] text-boss-gold' : 'border-white/[0.07] bg-white/[0.04] text-boss-ivory/45 hover:text-boss-ivory/75'">
                            {{ __('Lessons') }} <span class="opacity-60" x-text="`(${lessonsForFilter(lessonModuleFilter).length}/${lessons.length})`"></span>
                        </button>
                    </div>
                </div>

                {{-- ═══ MODULES PANEL ═══ --}}
                <div x-show="activeSection === 'modules'">
                    {{-- Chrome-style module tabs --}}
                    <div class="flex items-end gap-0.5 overflow-x-auto border-b border-white/[0.06] bg-[#080810] px-3 pt-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <template x-for="(module, moduleIndex) in modules" :key="module.client_key">
                            <button type="button" @click="activeModuleTab = moduleIndex; lessonModuleFilter = module.client_key; lessonMoveNotice = null; activeLessonKey = firstLessonKeyForFilter(lessonModuleFilter)"
                                x-bind:title="module.title || moduleLabel(moduleIndex)"
                                class="relative shrink-0 rounded-t-md border px-3.5 py-2 text-[0.72rem] transition-colors"
                                :class="activeModuleTab === moduleIndex ? 'border-white/[0.08] bg-[#0E0E1A] text-boss-ivory -mb-px z-10' : 'border-transparent text-boss-ivory/38 hover:text-boss-ivory/65'"
                                :style="activeModuleTab === moduleIndex ? 'border-bottom-color: #0E0E1A' : ''"
                                x-text="moduleLabel(moduleIndex)">
                            </button>
                        </template>
                        <button type="button" @click="addModule(); activeModuleTab = modules.length - 1; lessonModuleFilter = activeModuleKey(activeModuleTab); lessonMoveNotice = null; activeLessonKey = firstLessonKeyForFilter(lessonModuleFilter)"
                            class="shrink-0 rounded-t-md px-3 py-2 text-[0.72rem] text-boss-gold/50 transition-colors hover:text-boss-gold">
                            + {{ __('Add Module') }}
                        </button>
                    </div>

                    {{-- Module forms — all in DOM (x-show preserves data), only active visible --}}
                    <template x-for="(module, moduleIndex) in modules" :key="module.client_key">
                        <div x-show="activeModuleTab === moduleIndex" class="p-5"
                             @input.debounce.1800ms="scheduleAutosaveModule(module, $event)"
                             @change.debounce.400ms="scheduleAutosaveModule(module, $event)">
                            <input type="hidden" x-bind:name="`modules[${moduleIndex}][id]`" x-bind:value="module.id || ''">
                            <input type="hidden" x-bind:name="`modules[${moduleIndex}][client_key]`" x-bind:value="module.client_key">
                            <input type="hidden" x-bind:name="`modules[${moduleIndex}][sort_order]`" x-bind:value="moduleIndex + 1">

                            <div class="grid gap-4 sm:grid-cols-2">
                                {{-- Controls bar --}}
                                <div class="sm:col-span-2 flex items-center justify-between">
                                    <p class="text-[0.63rem] text-boss-ivory/28">
                                        {{ __('Module') }} <span x-text="moduleIndex + 1"></span>
                                        <span class="mx-1 opacity-30">·</span>
                                        <span x-text="module.is_published ? '{{ __('Published') }}' : '{{ __('Draft') }}'"></span>
                                    </p>
                                    <div class="flex items-center gap-1">
                                        <button type="button"
                                            @click="moveModule(moduleIndex, -1); activeModuleTab = Math.max(0, activeModuleTab - 1); lessonModuleFilter = activeModuleKey(activeModuleTab); lessonMoveNotice = null; activeLessonKey = firstLessonKeyForFilter(lessonModuleFilter)"
                                            x-bind:disabled="moduleIndex === 0"
                                            class="rounded border border-white/[0.06] bg-white/[0.03] px-2 py-1 text-[0.63rem] text-boss-ivory/35 transition-colors hover:text-boss-gold disabled:opacity-25">
                                            &larr; {{ __('Left') }}
                                        </button>
                                        <button type="button"
                                            @click="moveModule(moduleIndex, 1); activeModuleTab = Math.min(modules.length - 1, activeModuleTab + 1); lessonModuleFilter = activeModuleKey(activeModuleTab); lessonMoveNotice = null; activeLessonKey = firstLessonKeyForFilter(lessonModuleFilter)"
                                            x-bind:disabled="moduleIndex === modules.length - 1"
                                            class="rounded border border-white/[0.06] bg-white/[0.03] px-2 py-1 text-[0.63rem] text-boss-ivory/35 transition-colors hover:text-boss-gold disabled:opacity-25">
                                            {{ __('Right') }} &rarr;
                                        </button>
                                        <button type="button"
                                            @click="const t = activeModuleTab; removeModule(moduleIndex); activeModuleTab = moduleIndex <= t ? Math.max(0, t - 1) : Math.min(t, modules.length - 1); lessonModuleFilter = activeModuleKey(activeModuleTab); lessonMoveNotice = null; activeLessonKey = firstLessonKeyForFilter(lessonModuleFilter)"
                                            class="rounded border border-red-400/10 bg-red-400/[0.05] px-2 py-1 text-[0.63rem] text-red-400/60 transition-colors hover:text-red-300">
                                            {{ __('Remove') }}
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <x-input-label ::for="`module_title_${moduleIndex}`" :value="__('Module Title')" />
                                    <input type="text" required class="pd-input mt-2"
                                        x-model="module.title"
                                        x-bind:id="`module_title_${moduleIndex}`"
                                        x-bind:name="`modules[${moduleIndex}][title]`"
                                        placeholder="{{ __('Getting Started') }}">
                                </div>

                                <label class="flex items-start gap-2.5 rounded-lg border border-white/[0.06] bg-white/[0.03] p-3 sm:self-end">
                                    <input type="hidden" x-bind:name="`modules[${moduleIndex}][is_published]`" value="0">
                                    <input type="checkbox" value="1" x-model="module.is_published"
                                        x-bind:name="`modules[${moduleIndex}][is_published]`"
                                        class="mt-0.5 rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold">
                                    <span>
                                        <span class="block text-[0.78rem] text-boss-ivory">{{ __('Published') }}</span>
                                        <span class="mt-0.5 block text-[0.68rem] text-boss-ivory/32">{{ __('Visible to enrolled members.') }}</span>
                                    </span>
                                </label>

                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`module_description_${moduleIndex}`" :value="__('Module Description')" />
                                    <textarea rows="3" class="pd-input mt-2"
                                        x-model="module.description"
                                        x-bind:id="`module_description_${moduleIndex}`"
                                        x-bind:name="`modules[${moduleIndex}][description]`"
                                        placeholder="{{ __('Optional context for this part of the training.') }}"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>{{-- /MODULES PANEL --}}

                {{-- ═══ LESSONS PANEL ═══ --}}
                <div x-show="activeSection === 'lessons'">
                    {{-- Validation errors --}}
                    <div class="px-5 pt-3 empty:hidden space-y-1">
                        <x-input-error :messages="$errors->get('lessons')" />
                        <x-input-error :messages="$errors->get('lessons.*.content_blocks.*.image_upload')" />
                        <x-input-error :messages="$errors->get('lessons.*.content_blocks.*.gallery_uploads.*')" />
                        <x-input-error :messages="$errors->get('lessons.*.content_blocks.*.file_upload')" />
                    </div>

                    <div class="space-y-3 border-b border-white/[0.05] px-5 py-4">
                        <p class="text-[0.78rem] text-boss-ivory/55">
                            {{ __('Editing lessons for:') }}
                            <span class="font-medium text-boss-gold" x-text="lessonFilterContext(lessonModuleFilter)"></span>
                        </p>
                        <div class="flex gap-1 overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                            <template x-for="(module, moduleIndex) in modules" :key="module.client_key">
                                <button type="button"
                                    @click="activeModuleTab = moduleIndex; lessonModuleFilter = module.client_key; lessonMoveNotice = null; activeLessonKey = firstLessonKeyForFilter(lessonModuleFilter)"
                                    x-bind:title="moduleContextLabel(module.client_key)"
                                    class="shrink-0 rounded-lg border px-3 py-1.5 text-[0.7rem] transition-colors"
                                    :class="lessonModuleFilter === module.client_key ? 'border-boss-gold/30 bg-boss-gold/[0.12] text-boss-gold' : 'border-white/[0.07] bg-white/[0.035] text-boss-ivory/42 hover:text-boss-ivory/70'"
                                    x-text="moduleLabel(moduleIndex)">
                                </button>
                            </template>
                            <button type="button"
                                @click="lessonModuleFilter = 'all'; lessonMoveNotice = null; activeLessonKey = firstLessonKeyForFilter(lessonModuleFilter)"
                                class="shrink-0 rounded-lg border px-3 py-1.5 text-[0.7rem] transition-colors"
                                :class="lessonModuleFilter === 'all' ? 'border-boss-gold/30 bg-boss-gold/[0.12] text-boss-gold' : 'border-white/[0.07] bg-white/[0.035] text-boss-ivory/42 hover:text-boss-ivory/70'">
                                {{ __('All Lessons') }}
                            </button>
                        </div>
                        <p x-show="lessonMoveNotice" x-cloak class="rounded-lg border border-boss-gold/15 bg-boss-gold/[0.06] px-3 py-2 text-[0.72rem] text-boss-gold/80" x-text="lessonMoveNotice"></p>
                    </div>

                    {{-- Chrome-style lesson tabs --}}
                    <div class="flex items-end gap-0.5 overflow-x-auto border-b border-white/[0.06] bg-[#080810] px-3 pt-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <template x-for="lesson in lessonsForFilter(lessonModuleFilter)" :key="lessonKey(lesson)">
                            <button type="button" @click="lessonMoveNotice = null; activeLessonKey = lessonKey(lesson)"
                                x-bind:title="lesson.title || lessonTabLabel(lesson, lessonModuleFilter)"
                                class="relative shrink-0 rounded-t-md border px-3.5 py-2 text-[0.72rem] transition-colors"
                                :class="activeLessonKey === lessonKey(lesson) ? 'border-white/[0.08] bg-[#0E0E1A] text-boss-ivory -mb-px z-10' : 'border-transparent text-boss-ivory/38 hover:text-boss-ivory/65'"
                                :style="activeLessonKey === lessonKey(lesson) ? 'border-bottom-color: #0E0E1A' : ''"
                                x-text="lessonTabLabel(lesson, lessonModuleFilter)">
                            </button>
                        </template>
                        <button type="button" @click="lessonMoveNotice = null; activeLessonKey = addLessonForFilter(lessonModuleFilter, activeModuleKey(activeModuleTab))"
                            class="shrink-0 rounded-t-md px-3 py-2 text-[0.72rem] text-boss-gold/50 transition-colors hover:text-boss-gold">
                            + {{ __('Add Lesson') }}
                        </button>
                    </div>

                    {{-- Lesson forms — all in DOM (x-show preserves data), only active visible --}}
                    <div x-show="lessonsForFilter(lessonModuleFilter).length === 0" class="p-5 text-center">
                        <p class="text-[0.86rem] text-boss-ivory/45" x-text="isAllLessonsFilter(lessonModuleFilter) ? '{{ __('No lessons yet.') }}' : '{{ __('No lessons yet for this module.') }}'"></p>
                        <button type="button" @click="lessonMoveNotice = null; activeLessonKey = addLessonForFilter(lessonModuleFilter, activeModuleKey(activeModuleTab))" class="mt-3 rounded-lg border border-boss-gold/20 bg-boss-gold/10 px-3 py-1.5 text-[0.72rem] text-boss-gold transition-colors hover:bg-boss-gold/15">
                            + {{ __('Add Lesson') }}
                        </button>
                    </div>

                    <template x-for="(lesson, index) in lessons" :key="lessonKey(lesson)">
                        <div x-show="activeLessonKey === lessonKey(lesson) && lessonMatchesFilter(lesson, lessonModuleFilter)"
                             class="p-5"
                             @input.debounce.1800ms="scheduleAutosaveLesson(lesson, $event)"
                             @change.debounce.400ms="scheduleAutosaveLesson(lesson, $event)">
                            <input type="hidden" x-bind:name="`lessons[${index}][id]`" x-bind:value="lesson.id || ''">
                            <input type="hidden" x-bind:name="`lessons[${index}][course_id]`" x-bind:value="lesson.course_id || ''">

                            {{-- Lesson controls bar --}}
                            <div class="mb-4 flex items-center justify-between">
                                <p class="text-[0.63rem] text-boss-ivory/28">
                                    <span x-text="lessonTabLabel(lesson, lessonModuleFilter)"></span>
                                    <span class="mx-1 opacity-30">·</span>
                                    <span x-text="lesson.is_published ? '{{ __('Published') }}' : '{{ __('Draft') }}'"></span>
                                </p>
                                <div class="flex items-center gap-1.5">
                                    <template x-if="lesson.id">
                                        <a x-bind:href="lessonPreviewUrl(lesson)"
                                            class="rounded border border-boss-gold/15 bg-boss-gold/[0.06] px-2 py-1 text-[0.63rem] text-boss-gold transition-colors hover:bg-boss-gold/10">
                                            {{ __('Preview') }}
                                        </a>
                                    </template>
                                    <button type="button"
                                        @click="lessonMoveNotice = null; activeLessonKey = removeLessonForFilter(index, lessonModuleFilter)"
                                        class="rounded border border-red-400/10 bg-red-400/[0.05] px-2 py-1 text-[0.63rem] text-red-400/60 transition-colors hover:text-red-300">
                                        {{ __('Remove') }}
                                    </button>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <x-input-label ::for="`lesson_title_${index}`" :value="__('Lesson Title')" />
                                    <input type="text" required class="pd-input mt-2"
                                        x-model="lesson.title"
                                        x-bind:id="`lesson_title_${index}`"
                                        x-bind:name="`lessons[${index}][title]`"
                                        placeholder="{{ __('Account setup and profile optimisation') }}">
                                </div>

                                <div>
                                    <x-input-label ::for="`lesson_module_${index}`" :value="__('Module')" />
                                    <input type="hidden" x-bind:name="`lessons[${index}][course_module_id]`" x-bind:value="lesson.course_module_id || moduleIdForKey(lesson.module_key)">
                                    <input type="hidden" x-bind:name="`lessons[${index}][module_title]`" x-bind:value="moduleTitleForKey(lesson.module_key)">
                                    <select class="pd-input mt-2"
                                        x-model="lesson.module_key"
                                        x-bind:id="`lesson_module_${index}`"
                                        x-bind:name="`lessons[${index}][module_key]`"
                                        @change="activeLessonKey = changeLessonModuleForFilter(lesson, lessonModuleFilter)">
                                        <template x-for="module in modules" :key="module.client_key">
                                            <option x-bind:value="module.client_key" x-text="module.title || 'Untitled Module'"></option>
                                        </template>
                                    </select>
                                </div>

                                <label class="flex items-start gap-2.5 rounded-lg border border-white/[0.06] bg-white/[0.03] p-3">
                                    <input type="hidden" x-bind:name="`lessons[${index}][is_published]`" value="0">
                                    <input type="checkbox" value="1" x-model="lesson.is_published"
                                        x-bind:name="`lessons[${index}][is_published]`"
                                        class="mt-0.5 rounded border-white/15 bg-white/5 text-boss-gold focus:ring-boss-gold">
                                    <span>
                                        <span class="block text-[0.78rem] text-boss-ivory">{{ __('Published') }}</span>
                                        <span class="mt-0.5 block text-[0.68rem] text-boss-ivory/32">{{ __('Visible inside the course.') }}</span>
                                    </span>
                                </label>

                                @include('admin.courses.partials.lesson-content-blocks')

                                <input type="hidden" x-bind:name="`lessons[${index}][sort_order]`" x-bind:value="index + 1">
                            </div>
                        </div>
                    </template>
                </div>{{-- /LESSONS PANEL --}}

            </div>{{-- /TAB EDITOR --}}

            <div class="flex items-center gap-3">
                <x-primary-button>{{ __('Update Course') }}</x-primary-button>
                <a href="{{ route('admin.courses.index') }}" class="text-[0.78rem] text-boss-ivory/30 transition-colors hover:text-boss-ivory">{{ __('Cancel') }}</a>
            </div>
        </form>

        @include('admin.courses.partials.bunny-video-modal')
    </div>
</x-admin-layout>
