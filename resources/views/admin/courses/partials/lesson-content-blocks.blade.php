<div class="sm:col-span-2 rounded-xl border border-[#EEB4C3]/10 bg-[#EEB4C3]/[0.035] p-3">
    <input type="hidden" x-bind:name="`lessons[${index}][content_blocks_enabled]`" value="1">
    <input type="hidden" x-bind:name="`lessons[${index}][_content_block_count]`" x-bind:value="lesson.content_blocks.length">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="pd-heading text-[0.9rem] text-[#EEB4C3]">{{ __('Lesson Flow Builder') }}</p>
            <p class="mt-1 max-w-2xl text-[0.62rem] leading-relaxed text-boss-ivory/30">
                {{ __('Build the lesson content here. Older lessons without flow blocks will still use their saved fallback content.') }}
            </p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <select class="pd-input min-w-44 text-[0.72rem]" x-model="lesson.new_block_type">
                <template x-for="type in contentBlockTypes()" :key="type">
                    <option x-bind:value="type" x-text="blockTypeLabel(type)"></option>
                </template>
            </select>
            <button type="button" @click="addLessonBlock(index, lesson.new_block_type)" class="rounded-lg border border-[#EEB4C3]/20 bg-[#EEB4C3]/10 px-3 py-2 text-[0.68rem] text-[#EEB4C3] transition-colors hover:bg-[#EEB4C3]/15">
                {{ __('Add Block') }}
            </button>
        </div>
    </div>

    <div x-show="lesson.content_blocks.length === 0" class="mt-3 rounded-lg border border-white/[0.05] bg-white/[0.02] px-3 py-2 text-[0.7rem] text-boss-ivory/30">
        {{ __('No flow blocks yet. Add blocks for new lesson content; older saved lesson content will continue to render as fallback.') }}
    </div>

    <div x-show="lesson.content_blocks.length > 0" class="mt-3 rounded-lg border border-white/[0.05] bg-white/[0.02] px-3 py-2 text-[0.68rem] leading-relaxed text-boss-ivory/32">
        {{ __('Preview order:') }}
        <template x-for="(block, blockIndex) in lesson.content_blocks" :key="`preview-${block.id || block.temp_id}`">
            <span>
                <span class="text-[#EEB4C3]" x-text="`${blockIndex + 1}. ${blockTypeLabel(block.block_type)}`"></span><span x-show="blockIndex < lesson.content_blocks.length - 1"> / </span>
            </span>
        </template>
    </div>

    <div class="mt-3 space-y-2">
        <template x-for="(block, blockIndex) in lesson.content_blocks" :key="block.id || block.temp_id">
            <div x-data="{ expanded: true }" class="overflow-hidden rounded-lg border border-white/[0.06] bg-[#0E0E1A]">
                <div class="flex flex-wrap items-center gap-2 border-b border-white/[0.05] bg-white/[0.015] px-3 py-2">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full border border-[#EEB4C3]/25 bg-[#EEB4C3]/10 text-[0.58rem] text-[#EEB4C3]" x-text="blockIndex + 1"></span>
                    <span class="text-[0.68rem] text-boss-ivory/38" x-text="blockTypeLabel(block.block_type)"></span>
                    <span x-show="!expanded && block.title" class="max-w-[140px] truncate text-[0.63rem] text-boss-ivory/22" x-text="block.title"></span>
                    <div class="ml-auto flex flex-wrap items-center gap-1.5">
                        <button type="button" @click="expanded = !expanded"
                            class="rounded border border-white/[0.06] px-2 py-1 text-[0.62rem] text-boss-ivory/35 transition-colors hover:text-[#EEB4C3]"
                            x-bind:title="expanded ? '{{ __('Collapse block') }}' : '{{ __('Expand block') }}'">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                                class="h-3 w-3 transition-transform duration-200"
                                :class="expanded ? '' : 'rotate-180'">
                                <path fill-rule="evenodd" d="M11.78 9.78a.75.75 0 01-1.06 0L8 7.06 5.28 9.78a.75.75 0 01-1.06-1.06l3.25-3.25a.75.75 0 011.06 0l3.25 3.25a.75.75 0 010 1.06z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <button type="button" @click="moveLessonBlock(index, blockIndex, -1)" x-bind:disabled="blockIndex === 0" class="rounded border border-white/[0.06] px-2 py-1 text-[0.62rem] text-boss-ivory/35 transition-colors hover:text-[#EEB4C3] disabled:opacity-30">{{ __('Up') }}</button>
                        <button type="button" @click="moveLessonBlock(index, blockIndex, 1)" x-bind:disabled="blockIndex === lesson.content_blocks.length - 1" class="rounded border border-white/[0.06] px-2 py-1 text-[0.62rem] text-boss-ivory/35 transition-colors hover:text-[#EEB4C3] disabled:opacity-30">{{ __('Down') }}</button>
                        <button type="button" @click="removeLessonBlock(index, blockIndex)" class="rounded border border-red-400/10 bg-red-400/[0.05] px-2 py-1 text-[0.62rem] text-red-300/70 transition-colors hover:text-red-200">{{ __('Delete') }}</button>
                    </div>
                </div>

                <div x-show="expanded" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <div class="grid gap-3 p-3 sm:grid-cols-2">
                        {{-- Block media fields (id, paths, video ids, slide_images) are injected
                             from canonical JS state via rebuildBlockHiddenInputs() on form submit.
                             Do not add hidden inputs here — they would rely on Alpine x-bind
                             evaluation which can be deferred for non-active lesson panels. --}}

                        <div>
                            <x-input-label ::for="`lesson_${index}_block_${blockIndex}_type`" :value="__('Block Type')" />
                            <select class="pd-input mt-2" x-model="block.block_type" x-bind:id="`lesson_${index}_block_${blockIndex}_type`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][block_type]`">
                                <template x-for="type in (contentBlockTypes().includes(block.block_type) ? contentBlockTypes() : [...contentBlockTypes(), block.block_type])" :key="type">
                                    <option x-bind:value="type" x-text="blockTypeLabel(type)"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <x-input-label ::for="`lesson_${index}_block_${blockIndex}_title`" :value="__('Title')" />
                            <input type="text" class="pd-input mt-2" x-model="block.title" x-bind:id="`lesson_${index}_block_${blockIndex}_title`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][title]`" placeholder="{{ __('Optional section title') }}">
                        </div>

                        <div x-show="block.block_type === 'text'" class="sm:col-span-2">
                            <x-input-label ::for="`lesson_${index}_block_${blockIndex}_content`" :value="__('Content')" />
                            <textarea rows="6" class="pd-input mt-2" x-model="block.content" x-bind:id="`lesson_${index}_block_${blockIndex}_content`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][content]`" placeholder="{{ __('Write the lesson text for this section') }}"></textarea>
                        </div>

                        <div x-show="block.block_type === 'image'" class="sm:col-span-2">
                            <x-input-label ::for="`lesson_${index}_block_${blockIndex}_image`" :value="__('Image Upload')" />
                            <div
                                x-data="{ drag: false, fileLabel: '', previewSrc: null }"
                                @dragover.prevent="drag = true"
                                @dragleave.prevent="drag = false"
                                @drop.prevent="drag = false; const f = $event.dataTransfer?.files; if (f?.length && f[0].type.startsWith('image/')) { fileLabel = f[0].name; previewSrc = URL.createObjectURL(f[0]); const dt = new DataTransfer(); dt.items.add(f[0]); $refs.fileInput.files = dt.files; uploadBlockLocalFile(index, blockIndex, { target: $refs.fileInput }, 'image'); }"
                                class="mt-2"
                            >
                                <template x-if="block.image_url && !previewSrc">
                                    <div class="mb-2 overflow-hidden rounded-lg border border-white/[0.06] bg-[#08080f]">
                                        <img x-bind:src="block.image_url" x-bind:alt="block.title || lesson.title || '{{ __('Lesson image') }}'" class="max-h-48 w-full object-cover">
                                        <p class="px-3 py-1 text-[0.58rem] text-boss-ivory/22">{{ __('Current saved image') }}</p>
                                    </div>
                                </template>
                                <template x-if="previewSrc">
                                    <div class="mb-2 overflow-hidden rounded-lg border border-[#EEB4C3]/25 bg-[#08080f]">
                                        <img :src="previewSrc" alt="" class="max-h-48 w-full object-cover">
                                        <div class="flex items-center justify-between gap-3 px-3 py-1">
                                            <p class="text-[0.58rem] text-[#EEB4C3]/60">{{ __('New image — uploading...') }}</p>
                                            <button type="button" class="text-[0.58rem] text-boss-ivory/35 transition-colors hover:text-[#EEB4C3]" @click="previewSrc = null; fileLabel = ''">{{ __('Clear preview') }}</button>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="uploads[blockFileUploadKey(index, blockIndex)]" class="mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1 flex-1 overflow-hidden rounded-full bg-white/[0.06]">
                                            <div class="h-full rounded-full bg-[#EEB4C3] transition-all" x-bind:style="`width: ${uploads[blockFileUploadKey(index, blockIndex)]?.progress || 0}%`"></div>
                                        </div>
                                        <button
                                            type="button"
                                            x-show="(uploads[blockFileUploadKey(index, blockIndex)]?.progress ?? 0) > 0 && (uploads[blockFileUploadKey(index, blockIndex)]?.progress ?? 0) < 100 && !uploads[blockFileUploadKey(index, blockIndex)]?.error"
                                            @click="cancelBlockUpload(blockFileUploadKey(index, blockIndex))"
                                            class="shrink-0 rounded border border-red-400/15 bg-red-400/[0.06] px-2 py-0.5 text-[0.58rem] text-red-300/75 transition-colors hover:border-red-400/30 hover:text-red-200">
                                            {{ __('Cancel') }}
                                        </button>
                                    </div>
                                    <p class="mt-1 text-[0.6rem]" x-bind:class="uploads[blockFileUploadKey(index, blockIndex)]?.error ? 'text-red-300' : 'text-boss-ivory/32'" x-text="uploads[blockFileUploadKey(index, blockIndex)]?.error || uploads[blockFileUploadKey(index, blockIndex)]?.status"></p>
                                </div>
                                <label
                                    class="flex min-h-[7rem] cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed px-4 py-5 text-center transition-all duration-200"
                                    :class="drag ? 'border-[#EEB4C3]/70 bg-[#EEB4C3]/[0.07]' : (previewSrc ? 'border-[#EEB4C3]/30 bg-[#EEB4C3]/[0.03]' : 'border-white/[0.10] bg-white/[0.025] hover:border-[#EEB4C3]/30 hover:bg-white/[0.035]')"
                                >
                                    <span class="text-[0.58rem] font-semibold uppercase tracking-[0.15em] text-[#EEB4C3]/60">{{ __('Drop image here') }}</span>
                                    <span class="text-[0.73rem] text-boss-ivory/40">{{ __('Drag and drop, or click to browse') }}</span>
                                    <span class="text-[0.63rem] text-boss-ivory/28" x-text="fileLabel || '{{ __('No file selected') }}'"></span>
                                    <span class="mt-0.5 text-[0.58rem] text-boss-ivory/18">{{ __('Uploads immediately — JPG, PNG, WEBP') }}</span>
                                    <input
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                        class="sr-only"
                                        x-bind:id="`lesson_${index}_block_${blockIndex}_image`"
                                        x-ref="fileInput"
                                        @change="const f = $event.target.files; if (f.length) { fileLabel = f[0].name; previewSrc = URL.createObjectURL(f[0]); uploadBlockLocalFile(index, blockIndex, $event, 'image'); } else { fileLabel = ''; previewSrc = null; }"
                                    >
                                </label>
                            </div>
                        </div>

                        <div x-show="block.block_type === 'video'" class="sm:col-span-2">
                            <x-input-label ::for="`lesson_${index}_block_${blockIndex}_video`" :value="__('Bunny Video')" />
                            <div class="mt-2 rounded-lg border border-white/[0.06] bg-white/[0.025] p-3">
                                <template x-if="block.bunny_video_id">
                                    <a
                                        x-bind:href="block.bunny_library_id && block.bunny_video_id ? `https://iframe.mediadelivery.net/embed/${block.bunny_library_id}/${block.bunny_video_id}` : '#'"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mb-2 inline-flex items-center gap-1 text-[0.68rem] text-[#EEB4C3] hover:text-[#EEB4C3]/80"
                                    >
                                        {{ __('Current saved video') }}
                                    </a>
                                </template>
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <div class="flex h-20 w-full shrink-0 items-center justify-center overflow-hidden rounded-md border border-white/[0.06] bg-[#08080f] text-[0.62rem] text-boss-ivory/25 sm:w-32">
                                        <img x-show="block.bunny_thumbnail_url" x-bind:src="block.bunny_thumbnail_url" x-bind:alt="block.bunny_video_title || block.title || lesson.title" class="h-full w-full object-cover">
                                        <span x-show="!block.bunny_thumbnail_url">{{ __('No video') }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-[0.78rem] text-boss-ivory" x-text="block.bunny_video_title || (block.bunny_video_id ? '{{ __('Saved Bunny video') }}' : '{{ __('No Bunny video selected') }}')"></p>
                                        <p class="mt-1 text-[0.62rem] text-boss-ivory/28">
                                            <span x-show="block.bunny_video_id" x-text="block.duration ? `${block.duration} - ${block.bunny_video_id}` : block.bunny_video_id"></span>
                                            <span x-show="!block.bunny_video_id">{{ __('Select an existing Bunny video or upload a new one.') }}</span>
                                        </p>
                                        <div x-show="uploads[blockUploadKey(index, blockIndex)]" class="mt-2">
                                            <div class="h-1.5 overflow-hidden rounded-full bg-white/[0.06]">
                                                <div class="h-full rounded-full bg-[#EEB4C3] transition-all" x-bind:style="`width: ${uploads[blockUploadKey(index, blockIndex)]?.progress || 0}%`"></div>
                                            </div>
                                            <p class="mt-1 text-[0.62rem]" x-bind:class="uploads[blockUploadKey(index, blockIndex)]?.error ? 'text-red-300' : 'text-boss-ivory/32'" x-text="uploads[blockUploadKey(index, blockIndex)]?.error || uploads[blockUploadKey(index, blockIndex)]?.status"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="button" @click="openBlockBunnyPicker(index, blockIndex)" class="rounded-lg border border-[#EEB4C3]/20 bg-[#EEB4C3]/10 px-3 py-1.5 text-[0.68rem] text-[#EEB4C3] transition-colors hover:bg-[#EEB4C3]/15">
                                        {{ __('Select Existing Bunny Video') }}
                                    </button>
                                    <label class="cursor-pointer rounded-lg border border-white/[0.07] bg-white/[0.04] px-3 py-1.5 text-[0.68rem] text-boss-ivory/45 transition-colors hover:text-[#EEB4C3]">
                                        {{ __('Upload New Bunny Video') }}
                                        <input type="file" accept="video/*" class="hidden" @change="uploadBlockBunnyVideo(index, blockIndex, $event)">
                                    </label>
                                    <button x-show="block.bunny_video_id" type="button" @click="clearBlockBunnyVideo(index, blockIndex)" class="rounded-lg border border-red-400/10 bg-red-400/[0.05] px-3 py-1.5 text-[0.68rem] text-red-300/70 transition-colors hover:text-red-200">
                                        {{ __('Remove Video') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div x-show="['pdf_resource', 'presentation'].includes(block.block_type)" class="sm:col-span-2">
                            <label class="pd-label" x-bind:for="`lesson_${index}_block_${blockIndex}_file`">
                                <span x-text="block.block_type === 'presentation' ? '{{ __('Presentation Upload (PDF)') }}' : '{{ __('PDF Upload') }}'"></span>
                            </label>
                            <template x-if="block.block_type === 'presentation'">
                                <p class="mt-1 text-[0.62rem] leading-relaxed text-boss-ivory/35">{{ __('Export your PowerPoint or Keynote as a PDF first, then upload that PDF here.') }}</p>
                            </template>
                            <template x-if="block.file_url || block.presentation_url">
                                <a x-bind:href="block.file_url || block.presentation_url" target="_blank" rel="noopener noreferrer" class="mt-1.5 mb-1 inline-flex items-center gap-1 text-[0.68rem] text-[#EEB4C3] hover:text-[#EEB4C3]/80">
                                    <span x-text="block.block_type === 'presentation' ? '{{ __('Current saved presentation PDF') }}' : '{{ __('Current saved PDF') }}'"></span>
                                </a>
                            </template>
                            <div
                                x-data="{ drag: false, fileLabel: '' }"
                                @dragover.prevent="drag = true"
                                @dragleave.prevent="drag = false"
                                @drop.prevent="drag = false; const f = $event.dataTransfer?.files; if (f?.length) { fileLabel = f[0].name; const dt = new DataTransfer(); dt.items.add(f[0]); $refs.fileInput.files = dt.files; uploadBlockLocalFile(index, blockIndex, { target: $refs.fileInput }, block.block_type === 'presentation' ? 'presentation' : 'pdf'); }"
                                class="mt-2"
                            >
                                <div x-show="uploads[blockFileUploadKey(index, blockIndex)]" class="mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1 flex-1 overflow-hidden rounded-full bg-white/[0.06]">
                                            <div class="h-full rounded-full bg-[#EEB4C3] transition-all" x-bind:style="`width: ${uploads[blockFileUploadKey(index, blockIndex)]?.progress || 0}%`"></div>
                                        </div>
                                        <button
                                            type="button"
                                            x-show="(uploads[blockFileUploadKey(index, blockIndex)]?.progress ?? 0) > 0 && (uploads[blockFileUploadKey(index, blockIndex)]?.progress ?? 0) < 100 && !uploads[blockFileUploadKey(index, blockIndex)]?.error"
                                            @click="cancelBlockUpload(blockFileUploadKey(index, blockIndex))"
                                            class="shrink-0 rounded border border-red-400/15 bg-red-400/[0.06] px-2 py-0.5 text-[0.58rem] text-red-300/75 transition-colors hover:border-red-400/30 hover:text-red-200">
                                            {{ __('Cancel') }}
                                        </button>
                                    </div>
                                    <p class="mt-1 text-[0.6rem]" x-bind:class="uploads[blockFileUploadKey(index, blockIndex)]?.error ? 'text-red-300' : 'text-boss-ivory/32'" x-text="uploads[blockFileUploadKey(index, blockIndex)]?.error || uploads[blockFileUploadKey(index, blockIndex)]?.status"></p>
                                </div>
                                <label
                                    class="flex min-h-[7rem] cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed px-4 py-5 text-center transition-all duration-200"
                                    :class="drag ? 'border-[#EEB4C3]/70 bg-[#EEB4C3]/[0.07]' : 'border-white/[0.10] bg-white/[0.025] hover:border-[#EEB4C3]/30 hover:bg-white/[0.035]'"
                                >
                                    <span class="text-[0.58rem] font-semibold uppercase tracking-[0.15em] text-[#EEB4C3]/60" x-text="block.block_type === 'presentation' ? '{{ __('Drop PDF presentation here') }}' : '{{ __('Drop PDF here') }}'"></span>
                                    <span class="text-[0.73rem] text-boss-ivory/40">{{ __('Drag and drop, or click to browse') }}</span>
                                    <span class="text-[0.63rem] text-boss-ivory/28" x-text="fileLabel || '{{ __('No file selected') }}'"></span>
                                    <span class="mt-0.5 text-[0.58rem] text-boss-ivory/18">{{ __('PDF only — up to 100 MB') }}</span>
                                    <input
                                        type="file"
                                        accept=".pdf,application/pdf"
                                        class="sr-only"
                                        x-bind:id="`lesson_${index}_block_${blockIndex}_file`"
                                        x-ref="fileInput"
                                        @change="if ($event.target.files.length) { fileLabel = $event.target.files[0].name; uploadBlockLocalFile(index, blockIndex, $event, block.block_type === 'presentation' ? 'presentation' : 'pdf'); } else { fileLabel = ''; }"
                                    >
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

