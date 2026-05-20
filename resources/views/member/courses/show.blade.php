<x-member-layout>
    @php
        $color = $course->displayColor();
        $bg = $course->displayColorBackground();
        $image = $course->overviewImageUrl();
        $learningPoints = $course->learningPoints();
        $requirements = $course->requirementItems();
        $accessRequirements = $course->accessRequirementItems();
        $accessPhases = $course->accessPhaseInstructions();
        $shouldOpenAccessModal = ! $isEnrolled && (bool) $courseAccessRequest?->isRejected();
        $resourceCount = ($course->has_course_outline && filled($course->course_outline_url) ? 1 : 0)
            + ($course->has_intro ? 1 : 0)
            + $course->lessons->whereNotNull('pdf_url')->count();
    @endphp

    <div class="mx-auto max-w-6xl space-y-6" x-data="{ accessModalOpen: @js($shouldOpenAccessModal) }" @keydown.escape.window="accessModalOpen = false">
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <a href="{{ route('member.courses.index') }}" class="inline-flex rounded-xl border border-white/[0.07] bg-white/[0.04] px-3 py-2 text-[0.78rem] text-boss-ivory/45 transition-colors hover:text-[#EEB4C3]">
            <- {{ __('Academy Catalog') }}
        </a>

        <section class="overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-panel">
            <div class="grid min-h-[360px] lg:grid-cols-[1.05fr_0.95fr]">
                <div class="flex flex-col justify-between gap-8 p-6 md:p-8">
                    <div>
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <span class="rounded-full border px-3 py-1 text-[0.68rem]" style="background-color: {{ $bg }}; color: {{ $color }}; border-color: {{ $color }}25;">{{ $course->displayPlatform() }}</span>
                            <span class="rounded-full border border-white/[0.07] bg-white/[0.04] px-3 py-1 text-[0.68rem] text-boss-ivory/38">{{ $course->difficulty_level ?: __('Guided') }}</span>
                            @if ($isEnrolled)
                                <span class="rounded-full border border-[#EEB4C3]/25 bg-[#EEB4C3]/10 px-3 py-1 text-[0.68rem] text-[#EEB4C3]">{{ __('Enrolled') }}</span>
                            @endif
                        </div>

                        <h1 class="pd-heading text-[clamp(2rem,5vw,4rem)] text-boss-ivory">{{ $course->title }}</h1>
                        <p class="mt-5 max-w-2xl whitespace-pre-line text-[0.95rem] leading-relaxed text-boss-ivory/52">{{ $course->short_description ?: $course->description }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        @if ($isEnrolled)
                            <form method="POST" action="{{ route('member.courses.learn', $course->slug) }}">
                                @csrf
                                <button type="submit" class="pd-btn-primary">
                                    {{ __('Resume Course') }}
                                </button>
                            </form>
                        @else
                            <div class="w-full max-w-xl rounded-xl border border-[#EEB4C3]/20 bg-[#EEB4C3]/10 p-4 text-sm text-[#EEB4C3]">
                                @if (! $isVerified)
                                    {{ __('Verification must be approved before Kayla can review course access.') }}
                                @elseif ($courseAccessRequest?->isPending())
                                    {{ __('Access request sent. Kayla will review your course requirements and unlock this course when approved.') }}
                                @else
                                    <p class="font-medium">{{ $courseAccessRequest?->isRejected() ? __('Kayla requested updates before access.') : __('Locked pending Kayla approval.') }}</p>
                                    <p class="mt-1 text-[#EEB4C3]/70">{{ __("Open the review popup to see Kayla's requirements, upload any QR/code proof, and submit your request.") }}</p>
                                @endif
                            </div>
                        @endif

                        @if ($isEnrolled && isset($communityChannel) && $communityChannel)
                            <a href="{{ route('community.channels.show', $communityChannel->slug) }}" class="pd-btn-secondary inline-flex items-center gap-2">
                                <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.6]"><path d="M14 10c0 1.1-.9 2-2 2H4l-3 3V4c0-1.1.9-2 2-2h9c1.1 0 2 .9 2 2v6z"/></svg>
                                {{ __('Open Community') }}
                            </a>
                        @elseif ($isEnrolled)
                            <a href="{{ route('member.courses.community', $course->slug) }}" class="pd-btn-secondary">{{ __('Course Community') }}</a>
                        @endif
                    </div>
                </div>

                <div class="relative min-h-[280px] border-t border-white/[0.05] lg:border-l lg:border-t-0">
                    @if ($image)
                        <img src="{{ $image }}" alt="{{ $course->title }}" class="absolute inset-0 h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-boss-ink via-boss-ink/35 to-transparent"></div>
                    @else
                        <div class="absolute inset-0" style="background: linear-gradient(135deg, {{ $course->displayColorBackground(0.28) }}, rgba(255,255,255,0.03));"></div>
                    @endif
                    <div class="absolute bottom-0 left-0 right-0 p-5">
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-2">
                            @foreach ([
                                [__('Progress'), $progress['percent'].'%'],
                                [__('Lessons'), $course->lessons_count],
                                [__('Modules'), $course->modules_count],
                                [__('Members'), $course->enrolled_users_count],
                            ] as $stat)
                                <div class="rounded-xl border border-white/[0.08] bg-boss-ink/80 p-3 backdrop-blur">
                                    <p class="font-display text-[1.35rem] leading-none text-[#EEB4C3]">{{ $stat[1] }}</p>
                                    <p class="mt-1 text-[0.62rem] uppercase tracking-[0.12em] text-boss-ivory/35">{{ $stat[0] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if (! $isEnrolled)
            <section class="rounded-2xl border border-[#EEB4C3]/25 bg-[#EEB4C3]/[0.08] p-5 shadow-[0_18px_60px_rgba(0,0,0,0.22)] md:flex md:items-center md:justify-between md:gap-5">
                <div>
                    <p class="pd-kicker">{{ __('Course Access Review') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.45rem] text-boss-ivory">{{ $courseAccessRequest?->isRejected() ? __('Kayla needs a resubmission') : __("Review Kayla's requirements before requesting access") }}</h2>
                    <p class="mt-2 max-w-3xl text-[0.86rem] leading-relaxed text-boss-ivory/55">
                        @if (! $isVerified)
                            {{ __('Your verification must be approved before Kayla can review course access, but you can still read the course-specific process here.') }}
                        @elseif ($courseAccessRequest?->isPending())
                            {{ __('Your request is pending. You can reopen the review popup to check what Kayla is reviewing.') }}
                        @else
                            {{ __('This popup contains the website steps, QR/code proof reminder, Kayla notes, and the request form in one place.') }}
                        @endif
                    </p>
                </div>
                <button type="button" class="pd-btn-primary mt-4 shrink-0 md:mt-0" @click="accessModalOpen = true">
                    {{ $courseAccessRequest?->isRejected() ? __('Review Kayla Note') : ($courseAccessRequest?->isPending() ? __('View Request Status') : __('Review Requirements') ) }}
                </button>
            </section>

            <div
                x-show="accessModalOpen"
                x-cloak
                class="fixed inset-0 z-[90] flex items-center justify-center bg-black/75 px-4 py-5 backdrop-blur-sm"
                role="dialog"
                aria-modal="true"
                aria-labelledby="course-access-modal-title"
                @click.self="accessModalOpen = false"
            >
                <div
                    x-show="accessModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="translate-y-4 scale-[0.98] opacity-0"
                    x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                    x-transition:leave-end="translate-y-4 scale-[0.98] opacity-0"
                    class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-[#EEB4C3]/20 bg-[#101014] shadow-2xl"
                >
                    <div class="flex shrink-0 items-start justify-between gap-4 border-b border-white/[0.06] px-5 py-4 md:px-6">
                        <div>
                            <p class="pd-kicker">{{ __('Kayla Course Access Review') }}</p>
                            <h2 id="course-access-modal-title" class="pd-heading mt-1 text-[1.6rem] text-boss-ivory">{{ $course->title }}</h2>
                            <p class="mt-1 text-[0.78rem] leading-relaxed text-boss-ivory/42">{{ __('Read each requirement before sending or resubmitting your access request.') }}</p>
                        </div>
                        <button type="button" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/[0.08] bg-white/[0.04] text-boss-ivory/50 transition hover:text-boss-ivory" @click="accessModalOpen = false" aria-label="{{ __('Close access review') }}">
                            <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.7]"><path d="M4 4l8 8M12 4l-8 8"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 space-y-4 overflow-y-auto px-5 py-5 md:px-6">
                        @if ($courseAccessRequest?->isRejected() && filled($courseAccessRequest->admin_notes))
                            <div class="rounded-xl border border-red-400/20 bg-red-400/[0.08] p-4">
                                <p class="text-[0.64rem] uppercase tracking-[0.14em] text-red-200/65">{{ __('Admin note') }}</p>
                                <p class="mt-2 whitespace-pre-line text-[0.86rem] leading-relaxed text-red-100/80">{{ $courseAccessRequest->admin_notes }}</p>
                            </div>
                        @endif

                        <div class="rounded-xl border border-[#EEB4C3]/12 bg-[#EEB4C3]/[0.045] p-4">
                            <p class="pd-kicker">{{ __('Website Verification Process') }}</p>
                            <div class="mt-4 space-y-3">
                                @forelse ($accessPhases as $index => $phase)
                                    <div class="rounded-xl border border-[#EEB4C3]/10 bg-black/12 p-3">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#EEB4C3] text-[0.68rem] font-bold text-boss-ink">{{ $index + 1 }}</span>
                                            <p class="text-[0.78rem] font-semibold text-[#EEB4C3]">{{ $phase['label'] }}</p>
                                        </div>
                                        <p class="mt-2 whitespace-pre-line text-[0.8rem] leading-relaxed text-boss-ivory/62">{{ $phase['instructions'] }}</p>
                                    </div>
                                @empty
                                    <p class="text-[0.82rem] leading-relaxed text-boss-ivory/45">{{ __('Kayla has not added extra website phase instructions for this course yet.') }}</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-xl border border-[#EEB4C3]/12 bg-[#EEB4C3]/[0.045] p-4">
                            <p class="pd-kicker">{{ __('Access Requirements From Kayla') }}</p>
                            <div class="mt-4 space-y-2">
                                @forelse ($accessRequirements as $requirement)
                                    <p class="rounded-lg border border-[#EEB4C3]/10 bg-black/12 px-3 py-2 text-[0.8rem] leading-relaxed text-boss-ivory/62">{{ $requirement }}</p>
                                @empty
                                    <p class="text-[0.82rem] leading-relaxed text-boss-ivory/45">{{ __('Request access when you are ready. Kayla will review your onboarding and verification before unlocking this course.') }}</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/[0.07] bg-white/[0.025] p-4">
                            <p class="pd-kicker">{{ __('Request Course Access') }}</p>

                            @if (! $isVerified)
                                <p class="mt-4 text-[0.84rem] leading-relaxed text-boss-ivory/48">{{ __('Verification must be approved before Kayla can review course access.') }}</p>
                            @elseif ($courseAccessRequest?->isPending())
                                <p class="mt-4 text-[0.84rem] leading-relaxed text-boss-ivory/55">{{ __('Access request sent. Kayla will review your course requirements and unlock this course when approved.') }}</p>
                                @if (filled($courseAccessRequest->member_notes))
                                    <div class="mt-3 rounded-lg border border-[#EEB4C3]/10 bg-[#EEB4C3]/[0.04] px-3 py-2">
                                        <p class="text-[0.62rem] uppercase tracking-[0.12em] text-[#EEB4C3]/55">{{ __('Your access note') }}</p>
                                        <p class="mt-1 whitespace-pre-line text-[0.78rem] leading-relaxed text-boss-ivory/60">{{ $courseAccessRequest->member_notes }}</p>
                                    </div>
                                @endif
                            @else
                                <p class="mt-4 text-[0.84rem] leading-relaxed text-boss-ivory/56">{{ __('If Kayla requested QR/code proof, upload screenshots from your Verification page under Platform codes before requesting access.') }}</p>
                                <a href="{{ route('member.verification.edit') }}" class="mt-3 inline-flex rounded-xl border border-[#EEB4C3]/20 bg-[#EEB4C3]/10 px-3 py-2 text-[0.72rem] font-semibold text-[#EEB4C3] transition-colors hover:border-[#EEB4C3]/40 hover:bg-[#EEB4C3]/15">
                                    {{ __('Upload Platform Codes') }}
                                </a>

                                <form method="POST" action="{{ route('member.courses.request-access', $course->slug) }}" class="mt-4 space-y-3">
                                    @csrf
                                    <textarea
                                        name="member_notes"
                                        rows="4"
                                        class="pd-input text-sm"
                                        placeholder="{{ __('Tell Kayla what QR/code verification steps you completed for this course.') }}"
                                    >{{ old('member_notes', $courseAccessRequest?->isRejected() ? $courseAccessRequest->member_notes : '') }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('member_notes')" />
                                    <button type="submit" class="pd-btn-primary">
                                        {{ $courseAccessRequest?->isRejected() ? __('Resubmit Access Request') : __('Request Access') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <section class="grid gap-4 lg:grid-cols-[1fr_340px]">
            <div class="space-y-4">
                <div class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="pd-kicker">{{ __('Course Path') }}</p>
                            <h2 class="pd-heading mt-2 text-[1.55rem] text-boss-ivory">{{ __('What You Will Learn') }}</h2>
                        </div>
                        @if ($course->estimated_duration)
                            <span class="rounded-full border border-white/[0.07] bg-white/[0.04] px-3 py-1.5 text-[0.72rem] text-boss-ivory/45">{{ $course->estimated_duration }}</span>
                        @endif
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @forelse ($learningPoints as $point)
                            <div class="flex gap-3 rounded-xl border border-white/[0.05] bg-white/[0.025] p-3">
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full" style="background: {{ $color }};"></span>
                                <p class="text-[0.83rem] leading-relaxed text-boss-ivory/55">{{ $point }}</p>
                            </div>
                        @empty
                            @foreach ($course->lessons->take(6) as $lesson)
                                <div class="flex gap-3 rounded-xl border border-white/[0.05] bg-white/[0.025] p-3">
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full" style="background: {{ $color }};"></span>
                                    <p class="text-[0.83rem] leading-relaxed text-boss-ivory/55">{{ $lesson->title }}</p>
                                </div>
                            @endforeach
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
                    <p class="pd-kicker">{{ __('Modules') }}</p>
                    <div class="mt-4 space-y-3">
                        @forelse ($course->modules as $module)
                            <div class="rounded-xl border border-white/[0.05] bg-white/[0.025] p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-[0.95rem] font-medium text-boss-ivory">{{ $module->title }}</h3>
                                        @if ($module->description)
                                            <p class="mt-1 text-[0.76rem] text-boss-ivory/35">{{ $module->description }}</p>
                                        @endif
                                    </div>
                                    <span class="text-[0.7rem] text-boss-ivory/30">{{ trans_choice(':count lesson|:count lessons', $module->lessons->count(), ['count' => $module->lessons->count()]) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-[0.85rem] text-boss-ivory/35">{{ __('Lessons will appear here soon.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
                    <p class="pd-kicker">{{ __('Your Progress') }}</p>
                    <div class="mt-4 flex items-end justify-between">
                        <p class="font-display text-[2.5rem] leading-none text-[#EEB4C3]">{{ $progress['percent'] }}%</p>
                        <p class="pb-1 text-[0.78rem] text-boss-ivory/35">{{ $progress['completed'] }}/{{ $progress['total'] }} {{ __('lessons') }}</p>
                    </div>
                    <div class="pd-progress-track mt-4">
                        <div class="pd-progress-bar" style="width: {{ $progress['percent'] }}%"></div>
                    </div>
                    @if ($resumeLesson)
                        <p class="mt-4 text-[0.78rem] leading-relaxed text-boss-ivory/42">{{ __('Next up:') }} <span class="text-boss-ivory">{{ $resumeLesson->title }}</span></p>
                    @endif
                </div>

                <div class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
                    <p class="pd-kicker">{{ __('Requirements') }}</p>
                    <div class="mt-4 space-y-2">
                        @forelse ($requirements as $requirement)
                            <p class="rounded-lg border border-white/[0.05] bg-white/[0.025] px-3 py-2 text-[0.78rem] text-boss-ivory/48">{{ $requirement }}</p>
                        @empty
                            <p class="text-[0.82rem] leading-relaxed text-boss-ivory/38">{{ __('No special setup needed. Move through the course at your own pace.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
                    <p class="pd-kicker">{{ __('Resources') }}</p>
                    <p class="mt-3 text-[0.86rem] text-boss-ivory">{{ trans_choice(':count resource available|:count resources available', $resourceCount, ['count' => $resourceCount]) }}</p>
                    <p class="mt-2 text-[0.78rem] leading-relaxed text-boss-ivory/35">{{ __('Guides, PDFs, and lesson links appear inside the learning room when they are relevant.') }}</p>
                </div>
            </aside>
        </section>
    </div>
</x-member-layout>

