<x-member-layout>
    @php
        $profilePhotoUrl = auth()->user()->profilePhotoUrl();
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Success Stories') }}</p>
                <h1 class="pd-heading pd-text-gradient mt-2 text-[clamp(2rem,4vw,2.6rem)]">{{ __('Share Your Testimonial') }}</h1>
                <p class="mt-2 max-w-2xl text-[0.86rem] leading-relaxed text-boss-ivory/40">{{ __('Submitted testimonials go to the Paradise Dolls team for approval before they appear on the public site.') }}</p>
            </div>
            <a href="{{ route('success-stories') }}" class="pd-btn-secondary self-start sm:self-auto">{{ __('View public stories') }}</a>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <form method="POST" action="{{ route('member.testimonials.store') }}" enctype="multipart/form-data" class="pd-panel-strong space-y-5 p-5 md:p-6">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="rounded-xl border border-white/[0.06] bg-white/[0.03] p-4">
                        <x-input-label :value="__('Profile photo')" />
                        <div class="mt-3 flex items-center gap-3">
                            <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#EEB4C3]/25 bg-[#EEB4C3]/10 text-[0.72rem] font-semibold text-[#EEB4C3]">
                                <span>{{ auth()->user()->initials() }}</span>
                                @if ($profilePhotoUrl)
                                    <img src="{{ $profilePhotoUrl }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-[0.82rem] text-boss-ivory/70">{{ __('This will be used as your testimonial avatar.') }}</p>
                                <a href="{{ route('profile.edit') }}" class="mt-1 inline-flex text-[0.7rem] text-[#EEB4C3] hover:text-[#F3C3CF]">{{ __('Change profile photo') }} -></a>
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="name" :value="__('Display name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-2" :value="old('name', auth()->user()->name)" required />
                        <p class="mt-1.5 text-[0.62rem] leading-relaxed text-boss-ivory/25">{{ __('Shown above your handle on the testimonial card.') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="display_handle" :value="__('Display handle')" />
                    <x-text-input id="display_handle" name="display_handle" type="text" class="mt-2" :value="old('display_handle')" required placeholder="@neljhanredondo" />
                    <p class="mt-1.5 text-[0.62rem] leading-relaxed text-boss-ivory/25">{{ __('Shown under your display name. Use letters, numbers, underscores, or periods.') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('display_handle')" />
                </div>

                <div>
                    <x-input-label for="quote" :value="__('Testimonial text')" />
                    <textarea id="quote" name="quote" rows="6" class="pd-input mt-2" required maxlength="700" placeholder="{{ __('Write the short testimonial text shown in the landing page card.') }}">{{ old('quote') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('quote')" />
                </div>

                <div>
                    <x-input-label for="result_label" :value="__('Hashtag')" />
                    <x-text-input id="result_label" name="result_label" type="text" class="mt-2" :value="old('result_label')" required placeholder="{{ __('Community Support') }}" />
                    <p class="mt-1.5 text-[0.62rem] leading-relaxed text-boss-ivory/25">{{ __('This appears as the blue hashtag. You can type it with or without #.') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('result_label')" />
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-white/[0.06] pt-5">
                    <x-primary-button>{{ __('Submit for approval') }}</x-primary-button>
                </div>
            </form>

            <aside class="pd-panel p-5">
                <div class="mb-4">
                    <p class="pd-kicker text-boss-ivory/35">{{ __('Review Status') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.25rem] text-boss-ivory">{{ __('Your stories') }}</h2>
                </div>

                <div class="space-y-3">
                    @forelse ($testimonials as $testimonial)
                        @php
                            $statusClass = $testimonial->is_published
                                ? 'bg-green-400/10 text-green-300'
                                : 'bg-amber-400/10 text-amber-200';
                        @endphp
                        <div class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-4">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2 py-0.5 text-[0.62rem] {{ $statusClass }}">{{ $testimonial->statusLabel() }}</span>
                                <span class="text-[0.62rem] text-boss-ivory/25">{{ $testimonial->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="truncate text-[0.9rem] text-boss-ivory/72">{{ $testimonial->name }}</p>
                            <p class="mt-1 truncate text-[0.7rem] text-boss-ivory/30">{{ $testimonial->displayHandle() }}</p>
                            <p class="mt-2 line-clamp-3 text-[0.75rem] leading-relaxed text-boss-ivory/35">{{ $testimonial->quote }}</p>
                            @if ($testimonial->result_label)
                                <p class="mt-2 truncate text-[0.7rem] text-[#EEB4C3]">{{ '#'.ltrim($testimonial->result_label, '#') }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-sm border border-white/[0.06] bg-white/[0.025] p-5 text-[0.82rem] leading-relaxed text-boss-ivory/35">
                            {{ __('Once you submit a testimonial, its approval status will show here.') }}
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>
    </div>
</x-member-layout>

