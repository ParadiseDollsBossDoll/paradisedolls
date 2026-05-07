<x-member-layout>
    @php
        $firstLesson = $course->lessons->first();
        $color = $course->displayColor();
        $bg = $course->displayColorBackground();
    @endphp

    <div class="mx-auto max-w-[1100px] space-y-5" x-data="{ active: {{ $firstLesson?->id ?? 'null' }} }">
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <header class="flex items-center gap-3">
            <a href="{{ route('member.courses.index') }}" class="rounded-xl border border-white/[0.07] bg-white/[0.05] px-3 py-2 text-[0.78rem] text-boss-ivory/45 transition-colors hover:text-boss-gold">
                <- {{ __('Courses') }}
            </a>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="rounded-full border px-2.5 py-1 text-[0.68rem]" style="background-color: {{ $bg }}; color: {{ $color }}; border-color: {{ $color }}25;">{{ $course->displayPlatform() }}</span>
                    <span class="hidden text-[0.7rem] text-boss-ivory/30 sm:inline">{{ $percent }}% {{ __('complete') }}</span>
                </div>
                <h1 class="pd-heading mt-2 truncate text-[clamp(1.4rem,3vw,2rem)] text-boss-ivory">{{ $course->title }}</h1>
            </div>
            <div class="hidden items-center gap-3 sm:flex">
                <div class="h-1.5 w-24 overflow-hidden rounded-full bg-white/[0.06]">
                    <div class="h-full rounded-full" style="width: {{ $percent }}%; background: linear-gradient(90deg, {{ $color }}, #E8C88A);"></div>
                </div>
                <span class="font-display text-[1.05rem] text-boss-gold">{{ $percent }}%</span>
            </div>
        </header>

        @if ($course->description)
            <div class="pd-panel-strong p-5">
                <p class="whitespace-pre-line text-[0.88rem] leading-relaxed text-boss-ivory/48">{{ $course->description }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[280px_1fr]">
            <aside class="order-2 overflow-hidden rounded-2xl border border-white/[0.05] bg-boss-panel lg:order-1">
                <div class="border-b border-white/[0.05] px-4 py-3.5">
                    <p class="text-[0.62rem] uppercase tracking-[0.2em] text-boss-ivory/30">
                        {{ trans_choice(':count Lesson|:count Lessons', $course->lessons->count(), ['count' => $course->lessons->count()]) }}
                    </p>
                </div>
                <div class="space-y-1 p-2">
                    @forelse ($course->lessons as $lesson)
                        @php($done = $lesson->isCompletedBy(auth()->user()))
                        <button
                            type="button"
                            class="w-full rounded-xl border px-3 py-3 text-left transition-all duration-200"
                            x-bind:class="active === {{ $lesson->id }} ? 'border-boss-gold/25 bg-boss-gold/[0.08]' : 'border-transparent hover:bg-white/[0.03]'"
                            @click="active = {{ $lesson->id }}"
                        >
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $done ? 'text-boss-ink' : 'border border-white/[0.12] text-boss-ivory/25' }}" style="{{ $done ? 'background: linear-gradient(135deg, '.$color.', #E8C88A);' : '' }}">
                                    <span class="text-[0.55rem] font-semibold">{{ $done ? 'OK' : $loop->iteration }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[0.78rem] leading-snug {{ $done ? 'text-boss-ivory/45' : 'text-boss-ivory/62' }}" x-bind:class="active === {{ $lesson->id }} ? 'text-boss-ivory' : ''">
                                        {{ $lesson->title }}
                                    </p>
                                    <div class="mt-1 flex items-center gap-2 text-[0.6rem] text-boss-ivory/22">
                                        <span>{{ __('Lesson :n', ['n' => $loop->iteration]) }}</span>
                                        @if ($lesson->duration)
                                            <span>{{ $lesson->duration }}</span>
                                        @endif
                                        @if ($lesson->has_pdf)
                                            <span class="text-boss-gold">{{ __('PDF') }}</span>
                                        @endif
                                        @if ($lesson->presentation_url)
                                            <span class="text-boss-gold">{{ __('Slides') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </button>
                    @empty
                        <p class="px-4 py-8 text-center text-[0.82rem] text-boss-ivory/30">{{ __('No lessons yet.') }}</p>
                    @endforelse
                </div>
            </aside>

            <section class="order-1 space-y-4 lg:order-2">
                @foreach ($course->lessons as $lesson)
                    @php($done = $lesson->isCompletedBy(auth()->user()))
                    <article x-show="active === {{ $lesson->id }}" x-cloak class="space-y-4">
                        <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-[#08080f]">
                            @if ($lesson->video_url)
                                <div class="aspect-video w-full">
                                    <iframe class="h-full w-full" src="{{ $lesson->video_url }}" title="{{ $lesson->title }}" allowfullscreen loading="lazy"></iframe>
                                </div>
                            @else
                                <div class="flex aspect-video flex-col items-center justify-center p-8 text-center" style="background: radial-gradient(ellipse at center, {{ $course->displayColorBackground(0.12) }}, transparent 65%);">
                                    <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border shadow-glow" style="border-color: {{ $color }}30; background-color: {{ $course->displayColorBackground(0.10) }}; color: {{ $color }};">
                                        <span class="ml-1 text-2xl">&gt;</span>
                                    </div>
                                    <p class="pd-heading max-w-sm text-[1.1rem] text-boss-ivory/65">{{ $lesson->title }}</p>
                                    <p class="mt-2 max-w-sm text-[0.75rem] leading-relaxed text-boss-ivory/25">{{ __('Video tutorial will appear here once uploaded by the admin team.') }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-white/[0.05] bg-boss-panel">
                            <div class="h-0.5" style="background: linear-gradient(90deg, {{ $color }}80, transparent);"></div>
                            <div class="p-5">
                                <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <p class="mb-2 text-[0.6rem] uppercase tracking-[0.2em] text-boss-ivory/25">{{ __('Lesson :current of :total', ['current' => $loop->iteration, 'total' => $course->lessons->count()]) }}</p>
                                        <h2 class="pd-heading text-[clamp(1.2rem,3vw,1.65rem)] text-boss-ivory">{{ $lesson->title }}</h2>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($lesson->duration)
                                            <span class="rounded-full border border-white/[0.07] bg-white/[0.04] px-3 py-1.5 text-[0.72rem] text-boss-ivory/35">{{ $lesson->duration }}</span>
                                        @endif
                                        <span class="rounded-full border border-white/[0.07] bg-white/[0.04] px-3 py-1.5 text-[0.72rem] text-boss-ivory/35">
                                            {{ $done ? __('Completed') : __('In Progress') }}
                                        </span>
                                    </div>
                                </div>

                                @if ($lesson->body)
                                    <p class="mb-5 whitespace-pre-line text-[0.88rem] leading-relaxed text-boss-ivory/50">{{ $lesson->body }}</p>
                                @else
                                    <p class="mb-5 text-[0.88rem] leading-relaxed text-boss-ivory/35">{{ __('Lesson notes will appear here when they are added.') }}</p>
                                @endif

                                <div class="flex flex-wrap items-center gap-3">
                                    <form method="POST" action="{{ route('member.lessons.progress', $lesson) }}">
                                        @csrf
                                        @method('PATCH')
                                        @if ($done)
                                            <button type="submit" name="completed" value="0" class="pd-btn-secondary">{{ __('Mark incomplete') }}</button>
                                        @else
                                            <button type="submit" name="completed" value="1" class="pd-btn-primary">{{ __('Mark complete') }}</button>
                                        @endif
                                    </form>

                                    @if ($lesson->has_pdf)
                                        @if ($lesson->pdf_url)
                                            <a href="{{ $lesson->pdf_url }}" target="_blank" rel="noopener" class="pd-btn-secondary">{{ __('PDF Guide') }}</a>
                                        @else
                                            <span class="rounded-xl border border-boss-gold/15 bg-boss-gold/[0.06] px-4 py-2.5 text-[0.78rem] text-boss-gold">{{ __('PDF Guide Included') }}</span>
                                        @endif
                                    @endif

                                    @if ($lesson->presentation_url)
                                        <a href="{{ $lesson->presentation_url }}" target="_blank" rel="noopener" class="pd-btn-secondary">{{ __('Visual Presentation') }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        </div>

        <section id="course-chat" class="overflow-hidden rounded-2xl border border-white/[0.05] bg-boss-panel">
            <div class="border-b border-white/[0.05] px-5 py-4">
                <h3 class="pd-heading text-[1.25rem] text-boss-ivory">{{ __('Course chat') }}</h3>
                <p class="mt-1 text-[0.78rem] text-boss-ivory/35">{{ __('Discussion visible to all members in this course.') }}</p>
            </div>
            <div class="max-h-96 space-y-4 overflow-y-auto px-5 py-5">
                @forelse ($messages as $message)
                    <div class="rounded-2xl border border-white/[0.05] bg-white/[0.025] p-4 text-sm">
                        <div class="flex flex-wrap items-baseline gap-2">
                            <span class="font-medium text-boss-ivory">{{ $message->user->name }}</span>
                            <span class="text-xs text-boss-ivory/25">{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-2 whitespace-pre-line text-boss-ivory/55">{{ $message->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-boss-ivory/35">{{ __('No messages yet. Start the conversation below.') }}</p>
                @endforelse
            </div>
            <div class="border-t border-white/[0.05] px-5 py-5">
                <form method="POST" action="{{ route('member.courses.chat.store', $course->slug) }}" class="space-y-3">
                    @csrf
                    <div>
                        <x-input-label for="body" :value="__('Your message')" />
                        <textarea id="body" name="body" rows="3" required class="pd-input mt-2">{{ old('body') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('body')" />
                    </div>
                    <x-primary-button>{{ __('Post message') }}</x-primary-button>
                </form>
            </div>
        </section>
    </div>
</x-member-layout>
