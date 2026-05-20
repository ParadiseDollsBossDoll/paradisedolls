<div
    x-show="bunnyModalOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
    @keydown.escape.window="closeBunnyPicker()"
>
    <div class="w-full max-w-3xl overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0E0E1A] shadow-2xl" @click.outside="closeBunnyPicker()">
        <div class="flex items-start justify-between gap-4 border-b border-white/[0.06] px-5 py-4">
            <div>
                <p class="pd-kicker">{{ __('Bunny Stream') }}</p>
                <h3 class="pd-heading mt-1 text-[1.25rem] text-boss-ivory">{{ __('Select Existing Bunny Video') }}</h3>
            </div>
            <button type="button" class="rounded-lg border border-white/[0.07] bg-white/[0.04] px-3 py-1.5 text-[0.72rem] text-boss-ivory/45 transition-colors hover:text-boss-gold" @click="closeBunnyPicker()">
                {{ __('Close') }}
            </button>
        </div>

        <div class="space-y-4 p-5">
            <div>
                <x-input-label for="bunny_video_search" :value="__('Search videos')" />
                <input
                    id="bunny_video_search"
                    type="search"
                    class="pd-input mt-2"
                    x-model="bunnySearch"
                    @input.debounce.350ms="fetchBunnyVideos()"
                    placeholder="{{ __('Search Bunny video title') }}"
                >
            </div>

            <div x-show="bunnyError" class="rounded-lg border border-red-400/20 bg-red-400/10 p-3 text-[0.78rem] text-red-200" x-text="bunnyError"></div>

            <div class="max-h-[420px] overflow-y-auto rounded-xl border border-white/[0.06] bg-white/[0.02]">
                <div x-show="bunnyLoading" class="px-4 py-10 text-center text-[0.82rem] text-boss-ivory/35">
                    {{ __('Loading Bunny videos...') }}
                </div>

                <template x-if="!bunnyLoading && bunnyVideos.length === 0">
                    <div class="px-4 py-10 text-center text-[0.82rem] text-boss-ivory/35">
                        {{ __('No Bunny videos found.') }}
                    </div>
                </template>

                <template x-for="video in bunnyVideos" :key="video.id">
                    <button type="button" class="flex w-full items-center gap-3 border-t border-white/[0.05] px-4 py-3 text-left transition-colors first:border-t-0 hover:bg-boss-gold/[0.06]" @click="selectBunnyVideo(video)">
                        <div class="flex h-16 w-24 shrink-0 items-center justify-center overflow-hidden rounded-md border border-white/[0.06] bg-[#08080f] text-[0.58rem] text-boss-ivory/25">
                            <img x-show="video.thumbnail_url" x-bind:src="video.thumbnail_url" x-bind:alt="video.title" class="h-full w-full object-cover">
                            <span x-show="!video.thumbnail_url">{{ __('No thumb') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[0.82rem] text-boss-ivory" x-text="video.title"></p>
                            <p class="mt-1 text-[0.62rem] text-boss-ivory/30">
                                <span x-text="video.duration || '{{ __('Processing') }}'"></span>
                                <span> · </span>
                                <span x-text="video.id"></span>
                            </p>
                        </div>
                        <span class="text-[0.68rem] text-boss-gold">{{ __('Use') }}</span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>

