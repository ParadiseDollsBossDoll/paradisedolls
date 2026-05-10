<div class="sm:col-span-2 rounded-xl border border-boss-gold/10 bg-boss-gold/[0.035] p-3">
    <input type="hidden" x-bind:name="`lessons[${index}][content_blocks_enabled]`" value="1">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="pd-heading text-[0.9rem] text-boss-gold">{{ __('Lesson Flow Builder') }}</p>
            <p class="mt-1 max-w-2xl text-[0.62rem] leading-relaxed text-boss-ivory/30">
                {{ __('Optional. Build a guided lesson sequence below the fixed lesson fields. The lesson banner above stays separate from flow images and galleries.') }}
            </p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <select class="pd-input min-w-44 text-[0.72rem]" x-model="lesson.new_block_type">
                <template x-for="type in contentBlockTypes()" :key="type">
                    <option x-bind:value="type" x-text="blockTypeLabel(type)"></option>
                </template>
            </select>
            <button type="button" @click="addLessonBlock(index, lesson.new_block_type)" class="rounded-lg border border-boss-gold/20 bg-boss-gold/10 px-3 py-2 text-[0.68rem] text-boss-gold transition-colors hover:bg-boss-gold/15">
                {{ __('Add Block') }}
            </button>
        </div>
    </div>

    <div x-show="lesson.content_blocks.length === 0" class="mt-3 rounded-lg border border-white/[0.05] bg-white/[0.02] px-3 py-2 text-[0.7rem] text-boss-ivory/30">
        {{ __('No flow blocks yet. This lesson will use the existing fixed lesson layout.') }}
    </div>

    <div x-show="lesson.content_blocks.length > 0" class="mt-3 rounded-lg border border-white/[0.05] bg-white/[0.02] px-3 py-2 text-[0.68rem] leading-relaxed text-boss-ivory/32">
        {{ __('Preview order:') }}
        <template x-for="(block, blockIndex) in lesson.content_blocks" :key="`preview-${block.id || blockIndex}`">
            <span>
                <span class="text-boss-gold" x-text="`${blockIndex + 1}. ${blockTypeLabel(block.block_type)}`"></span><span x-show="blockIndex < lesson.content_blocks.length - 1"> / </span>
            </span>
        </template>
    </div>

    <div class="mt-3 space-y-3">
        <template x-for="(block, blockIndex) in lesson.content_blocks" :key="block.id || `${index}-${blockIndex}`">
            <div class="overflow-hidden rounded-lg border border-white/[0.06] bg-[#0E0E1A]">
                <div class="flex flex-wrap items-center gap-2 border-b border-white/[0.05] bg-white/[0.015] px-3 py-2">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full border border-boss-gold/25 bg-boss-gold/10 text-[0.58rem] text-boss-gold" x-text="blockIndex + 1"></span>
                    <span class="text-[0.68rem] text-boss-ivory/38" x-text="blockTypeLabel(block.block_type)"></span>
                    <div class="ml-auto flex flex-wrap gap-1.5">
                        <button type="button" @click="moveLessonBlock(index, blockIndex, -1)" x-bind:disabled="blockIndex === 0" class="rounded border border-white/[0.06] px-2 py-1 text-[0.62rem] text-boss-ivory/35 transition-colors hover:text-boss-gold disabled:opacity-30">{{ __('Up') }}</button>
                        <button type="button" @click="moveLessonBlock(index, blockIndex, 1)" x-bind:disabled="blockIndex === lesson.content_blocks.length - 1" class="rounded border border-white/[0.06] px-2 py-1 text-[0.62rem] text-boss-ivory/35 transition-colors hover:text-boss-gold disabled:opacity-30">{{ __('Down') }}</button>
                        <button type="button" @click="removeLessonBlock(index, blockIndex)" class="rounded border border-red-400/10 bg-red-400/[0.05] px-2 py-1 text-[0.62rem] text-red-300/70 transition-colors hover:text-red-200">{{ __('Delete') }}</button>
                    </div>
                </div>

                <div class="grid gap-3 p-3 sm:grid-cols-2">
                    <input type="hidden" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][id]`" x-bind:value="block.id || ''">
                    <input type="hidden" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][sort_order]`" x-bind:value="blockIndex + 1">

                    <div>
                        <x-input-label ::for="`lesson_${index}_block_${blockIndex}_type`" :value="__('Block Type')" />
                        <select class="pd-input mt-2" x-model="block.block_type" x-bind:id="`lesson_${index}_block_${blockIndex}_type`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][block_type]`">
                            <template x-for="type in contentBlockTypes()" :key="type">
                                <option x-bind:value="type" x-text="blockTypeLabel(type)"></option>
                            </template>
                        </select>
                    </div>

                    <div x-show="!['divider', 'gallery'].includes(block.block_type)">
                        <x-input-label ::for="`lesson_${index}_block_${blockIndex}_title`" :value="__('Title / Heading')" />
                        <input type="text" class="pd-input mt-2" x-model="block.title" x-bind:id="`lesson_${index}_block_${blockIndex}_title`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][title]`" x-bind:placeholder="block.block_type === 'heading' ? '{{ __('Main heading text') }}' : '{{ __('Optional section title') }}'">
                    </div>

                    <div x-show="['heading', 'text', 'image', 'video', 'canva', 'steps', 'tips', 'safety'].includes(block.block_type)" class="sm:col-span-2">
                        <label class="pd-label" x-bind:for="`lesson_${index}_block_${blockIndex}_content`">
                            <span x-text="block.block_type === 'image' ? @js(__('Caption')) : (block.block_type === 'heading' ? @js(__('Subtitle')) : @js(__('Content')))"></span>
                        </label>
                        <textarea rows="4" class="pd-input mt-2" x-model="block.content" x-bind:id="`lesson_${index}_block_${blockIndex}_content`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][content]`" x-bind:placeholder="['steps', 'tips', 'safety'].includes(block.block_type) ? '{{ __('One item per line') }}' : '{{ __('Add the lesson text for this section') }}'"></textarea>
                    </div>

                    <div x-show="block.block_type === 'image'" class="sm:col-span-2">
                        <x-input-label ::for="`lesson_${index}_block_${blockIndex}_image`" :value="__('Content Image Upload')" />
                        <template x-if="block.image_url">
                            <div class="mt-2 overflow-hidden rounded-lg border border-white/[0.06] bg-[#08080f]">
                                <img x-bind:src="block.image_url" x-bind:alt="block.title || lesson.title || '{{ __('Lesson image') }}'" class="max-h-56 w-full object-cover">
                            </div>
                        </template>
                        <input type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="pd-input mt-2" x-bind:id="`lesson_${index}_block_${blockIndex}_image`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][image_upload]`">
                        <p class="mt-1 text-[0.6rem] text-boss-ivory/22">{{ __('This image appears inside the lesson flow only. It will not replace the lesson banner.') }}</p>
                    </div>

                    <div x-show="block.block_type === 'gallery'" class="sm:col-span-2">
                        <x-input-label ::for="`lesson_${index}_block_${blockIndex}_gallery`" :value="__('Image Gallery Uploads')" />
                        <template x-if="block.gallery_image_urls && block.gallery_image_urls.length">
                            <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4">
                                <template x-for="imageUrl in block.gallery_image_urls" :key="imageUrl">
                                    <img x-bind:src="imageUrl" x-bind:alt="lesson.title || '{{ __('Gallery image') }}'" class="h-16 w-full rounded-md border border-white/[0.06] object-cover">
                                </template>
                            </div>
                        </template>
                        <input type="file" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="pd-input mt-2" x-bind:id="`lesson_${index}_block_${blockIndex}_gallery`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][gallery_uploads][]`">
                        <x-input-label ::for="`lesson_${index}_block_${blockIndex}_gallery_captions`" :value="__('Optional Captions')" class="mt-3" />
                        <textarea rows="3" class="pd-input mt-2" x-model="block.gallery_captions" x-bind:id="`lesson_${index}_block_${blockIndex}_gallery_captions`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][gallery_captions]`" placeholder="{{ __('One caption per line, matching image order') }}"></textarea>
                    </div>

                    <div x-show="block.block_type === 'video'" class="sm:col-span-2">
                        <input type="hidden" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][bunny_video_id]`" x-bind:value="block.bunny_video_id || ''">
                        <input type="hidden" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][bunny_library_id]`" x-bind:value="block.bunny_library_id || ''">
                        <input type="hidden" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][bunny_video_title]`" x-bind:value="block.bunny_video_title || ''">
                        <input type="hidden" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][bunny_thumbnail_url]`" x-bind:value="block.bunny_thumbnail_url || ''">
                        <input type="hidden" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][bunny_upload_fingerprint]`" x-bind:value="block.bunny_upload_fingerprint || ''">
                        <input type="hidden" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][bunny_status]`" x-bind:value="block.bunny_status || ''">
                        <input type="hidden" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][duration]`" x-bind:value="block.duration || ''">

                        <x-input-label ::for="`lesson_${index}_block_${blockIndex}_video`" :value="__('Bunny Video Block')" />
                        <div class="mt-2 rounded-lg border border-white/[0.06] bg-white/[0.025] p-3">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div class="flex h-20 w-full shrink-0 items-center justify-center overflow-hidden rounded-md border border-white/[0.06] bg-[#08080f] text-[0.62rem] text-boss-ivory/25 sm:w-32">
                                    <img x-show="block.bunny_thumbnail_url" x-bind:src="block.bunny_thumbnail_url" x-bind:alt="block.bunny_video_title || block.title || lesson.title" class="h-full w-full object-cover">
                                    <span x-show="!block.bunny_thumbnail_url">{{ __('No video') }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[0.78rem] text-boss-ivory" x-text="block.bunny_video_title || '{{ __('No Bunny video selected') }}'"></p>
                                    <p class="mt-1 text-[0.62rem] text-boss-ivory/28">
                                        <span x-show="block.bunny_video_id" x-text="block.duration ? `${block.duration} - ${block.bunny_video_id}` : block.bunny_video_id"></span>
                                        <span x-show="!block.bunny_video_id">{{ __('Select an existing Bunny video or upload a new one.') }}</span>
                                    </p>
                                    <div x-show="uploads[blockUploadKey(index, blockIndex)]" class="mt-2">
                                        <div class="h-1.5 overflow-hidden rounded-full bg-white/[0.06]">
                                            <div class="h-full rounded-full bg-boss-gold transition-all" x-bind:style="`width: ${uploads[blockUploadKey(index, blockIndex)]?.progress || 0}%`"></div>
                                        </div>
                                        <p class="mt-1 text-[0.62rem]" x-bind:class="uploads[blockUploadKey(index, blockIndex)]?.error ? 'text-red-300' : 'text-boss-ivory/32'" x-text="uploads[blockUploadKey(index, blockIndex)]?.error || uploads[blockUploadKey(index, blockIndex)]?.status"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button" @click="openBlockBunnyPicker(index, blockIndex)" class="rounded-lg border border-boss-gold/20 bg-boss-gold/10 px-3 py-1.5 text-[0.68rem] text-boss-gold transition-colors hover:bg-boss-gold/15">
                                    {{ __('Select Existing Bunny Video') }}
                                </button>
                                <label class="cursor-pointer rounded-lg border border-white/[0.07] bg-white/[0.04] px-3 py-1.5 text-[0.68rem] text-boss-ivory/45 transition-colors hover:text-boss-gold">
                                    {{ __('Upload New Bunny Video') }}
                                    <input type="file" accept="video/*" class="hidden" @change="uploadBlockBunnyVideo(index, blockIndex, $event)">
                                </label>
                                <button x-show="block.bunny_video_id" type="button" @click="clearBlockBunnyVideo(index, blockIndex)" class="rounded-lg border border-red-400/10 bg-red-400/[0.05] px-3 py-1.5 text-[0.68rem] text-red-300/70 transition-colors hover:text-red-200">
                                    {{ __('Remove Video') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div x-show="block.block_type === 'canva'" class="sm:col-span-2">
                        <x-input-label ::for="`lesson_${index}_block_${blockIndex}_presentation`" :value="__('Canva Embed or Share Link')" />
                        <textarea rows="2" class="pd-input mt-2" x-model="block.presentation_url" x-bind:id="`lesson_${index}_block_${blockIndex}_presentation`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][presentation_url]`" placeholder="https://www.canva.com/design/... or Canva iframe embed code"></textarea>
                    </div>

                    <div x-show="block.block_type === 'pdf_resource'" class="sm:col-span-2">
                        <x-input-label ::for="`lesson_${index}_block_${blockIndex}_resource`" :value="__('PDF / Resource Upload')" />
                        <template x-if="block.file_url">
                            <a x-bind:href="block.file_url" target="_blank" rel="noopener noreferrer" class="mb-2 inline-flex text-[0.68rem] text-boss-gold hover:text-boss-gold-light">{{ __('Current resource file') }}</a>
                        </template>
                        <input type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.txt,.zip,application/pdf" class="pd-input mt-2" x-bind:id="`lesson_${index}_block_${blockIndex}_resource`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][file_upload]`">
                        <x-input-label ::for="`lesson_${index}_block_${blockIndex}_button`" :value="__('Optional Button Label')" class="mt-3" />
                        <input type="text" class="pd-input mt-2" x-model="block.button_label" x-bind:id="`lesson_${index}_block_${blockIndex}_button`" x-bind:name="`lessons[${index}][content_blocks][${blockIndex}][button_label]`" placeholder="{{ __('Open Resource') }}">
                    </div>

                    <div x-show="block.block_type === 'divider'" class="sm:col-span-2 rounded-lg border border-white/[0.05] bg-white/[0.02] px-3 py-2 text-[0.68rem] text-boss-ivory/30">
                        {{ __('Divider blocks add a visual pause between lesson sections.') }}
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
