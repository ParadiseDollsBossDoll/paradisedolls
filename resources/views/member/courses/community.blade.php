<x-member-layout>
    @php
        $color = $course->displayColor();
    @endphp

    <div class="mx-auto max-w-5xl space-y-5">
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <header class="rounded-2xl border border-white/[0.05] bg-boss-panel p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <a href="{{ route('member.courses.learn.show', $course->slug) }}" class="text-[0.72rem] text-boss-ivory/35 transition-colors hover:text-[#EEB4C3]"><- {{ __('Learning Room') }}</a>
                        <span class="text-boss-ivory/12">/</span>
                        <span class="rounded-full border px-2.5 py-1 text-[0.65rem]" style="background-color: {{ $course->displayColorBackground() }}; color: {{ $color }}; border-color: {{ $color }}25;">{{ $course->displayPlatform() }}</span>
                    </div>
                    <h1 class="pd-heading text-[clamp(1.7rem,4vw,2.6rem)] text-boss-ivory">{{ __('Course Community') }}</h1>
                    <p class="mt-2 text-[0.85rem] text-boss-ivory/40">{{ $course->title }}</p>
                </div>

                <div class="min-w-[180px]">
                    <div class="mb-1 flex items-center justify-between text-[0.68rem] text-boss-ivory/35">
                        <span>{{ __('Course progress') }}</span>
                        <span class="text-[#EEB4C3]">{{ $progress['percent'] }}%</span>
                    </div>
                    <div class="pd-progress-track">
                        <div class="pd-progress-bar" style="width: {{ $progress['percent'] }}%"></div>
                    </div>
                </div>
            </div>
        </header>

        <section class="overflow-hidden rounded-2xl border border-white/[0.05] bg-boss-panel">
            <div class="border-b border-white/[0.05] px-5 py-4">
                <h2 class="pd-heading text-[1.25rem] text-boss-ivory">{{ __('Discussion') }}</h2>
                <p class="mt-1 text-[0.78rem] text-boss-ivory/35">{{ __('Visible only to members enrolled in this course.') }}</p>
            </div>

            <div class="max-h-[560px] space-y-4 overflow-y-auto px-5 py-5">
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

