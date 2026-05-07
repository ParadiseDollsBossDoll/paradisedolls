<x-admin-layout>
    <div class="mx-auto max-w-6xl space-y-6 text-boss-ivory">
        <header class="flex items-start justify-between gap-4">
            <div>
                <p class="pd-kicker">{{ __('Admin') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(1.8rem,4vw,2.5rem)]">{{ __('Courses') }}</h1>
                <p class="mt-2 text-[0.82rem] text-boss-ivory/35">
                    {{ trans_choice(':count total course|:count total courses', $courses->total(), ['count' => $courses->total()]) }}
                </p>
            </div>
            <a href="{{ route('admin.courses.create') }}" class="pd-btn-primary shrink-0">{{ __('New Course') }}</a>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        @if ($courses->isEmpty())
            <div class="rounded-sm border border-white/[0.06] bg-[#141419] py-20 text-center">
                <p class="text-[0.9rem] text-boss-ivory/35">{{ __('No courses yet.') }}</p>
                <a href="{{ route('admin.courses.create') }}" class="mt-4 inline-flex text-[0.82rem] text-boss-gold hover:text-boss-gold-light">{{ __('Create your first course') }} -></a>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($courses as $course)
                @php
                    $stats = $courseStats[$course->id] ?? ['started' => 0, 'finished' => 0, 'messages' => 0];
                    $color = $course->displayColor();
                    $bg = $course->displayColorBackground();
                @endphp

                <article class="group flex flex-col overflow-hidden rounded-sm border border-white/[0.06] bg-[#141419] transition-all duration-200 hover:shadow-glow" style="--platform-color: {{ $color }};">
                    <div class="h-1 w-full shrink-0" style="background: linear-gradient(90deg, {{ $color }}, {{ $color }}40);"></div>

                    <div class="flex flex-1 flex-col gap-3 p-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border px-2 py-0.5 text-[0.65rem]" style="background-color: {{ $bg }}; color: {{ $color }}; border-color: {{ $color }}30;">
                                {{ $course->displayPlatform() }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[0.65rem] {{ $course->is_published ? 'border-green-400/20 bg-green-400/[0.08] text-green-300' : 'border-white/[0.06] bg-white/[0.04] text-boss-ivory/35' }}">
                                <span class="inline-block h-1.5 w-1.5 rounded-full {{ $course->is_published ? 'bg-green-300' : 'bg-boss-ivory/30' }}"></span>
                                {{ $course->is_published ? __('Live') : __('Draft') }}
                            </span>
                            <span class="ml-auto text-[0.65rem] text-boss-ivory/30">
                                {{ trans_choice(':count lesson|:count lessons', $course->lessons_count, ['count' => $course->lessons_count]) }}
                            </span>
                        </div>

                        <h2 class="pd-heading line-clamp-2 text-[1.05rem] leading-snug text-boss-ivory">{{ $course->title }}</h2>
                        <p class="line-clamp-2 flex-1 text-[0.75rem] leading-relaxed text-boss-ivory/35">{{ $course->description ?: __('No description provided.') }}</p>

                        <div class="grid grid-cols-3 gap-2 border-t border-white/[0.05] pt-3">
                            <div class="rounded-sm bg-white/[0.02] py-2 text-center">
                                <span class="block text-[0.78rem] font-semibold text-boss-ivory">{{ $stats['started'] }}</span>
                                <span class="text-[0.58rem] text-boss-ivory/30">{{ __('started') }}</span>
                            </div>
                            <div class="rounded-sm bg-white/[0.02] py-2 text-center">
                                <span class="block text-[0.78rem] font-semibold text-boss-ivory">{{ $stats['finished'] }}</span>
                                <span class="text-[0.58rem] text-boss-ivory/30">{{ __('finished') }}</span>
                            </div>
                            <div class="rounded-sm bg-white/[0.02] py-2 text-center">
                                <span class="block text-[0.78rem] font-semibold text-boss-ivory">{{ $stats['messages'] }}</span>
                                <span class="text-[0.58rem] text-boss-ivory/30">{{ __('messages') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <form method="POST" action="{{ route('admin.courses.visibility', $course) }}" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_published" value="{{ $course->is_published ? 0 : 1 }}">
                                <button type="submit" class="flex w-full items-center justify-center rounded-sm border px-3 py-1.5 text-[0.7rem] transition-colors {{ $course->is_published ? 'border-green-400/20 bg-green-400/[0.08] text-green-300 hover:bg-green-400/[0.12]' : 'border-white/[0.07] bg-white/[0.04] text-boss-ivory/42 hover:border-boss-gold/25 hover:text-boss-gold' }}">
                                    {{ $course->is_published ? __('Published') : __('Publish') }}
                                </button>
                            </form>

                            <a href="{{ route('admin.courses.edit', $course) }}" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-sm border border-boss-gold/20 bg-boss-gold/[0.08] text-[0.7rem] text-boss-gold transition-colors hover:bg-boss-gold/15" title="{{ __('Edit course') }}">
                                E
                            </a>

                            <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" onsubmit="return confirm('{{ __('Delete this course?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-sm border border-white/[0.06] bg-white/[0.03] text-[0.7rem] text-red-400/60 transition-colors hover:border-red-400/20 hover:bg-red-400/[0.08] hover:text-red-300" title="{{ __('Delete course') }}">
                                    X
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="px-2">{{ $courses->links() }}</div>
    </div>
</x-admin-layout>
