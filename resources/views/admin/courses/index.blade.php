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
                    $image = $course->overviewImageUrl();
                @endphp

                <article class="group flex flex-col overflow-hidden rounded-sm border border-white/[0.06] bg-[#141419] transition-all duration-200 hover:shadow-glow" style="--platform-color: {{ $color }};">
                    <div class="relative h-32 shrink-0 overflow-hidden">
                        @if ($image)
                            <img src="{{ $image }}" alt="{{ $course->title }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#141419] via-[#141419]/35 to-black/10"></div>
                        @else
                            <div class="absolute inset-0" style="background: linear-gradient(135deg, {{ $course->displayColorBackground(0.28) }}, rgba(255,255,255,0.03));"></div>
                        @endif
                        <div class="absolute bottom-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, {{ $color }}, {{ $color }}40);"></div>
                    </div>

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

                            {{-- Preview --}}
                            <a href="{{ route('admin.courses.preview', $course) }}" target="_blank"
                               class="flex h-8 w-8 shrink-0 items-center justify-center rounded-sm border border-white/[0.07] bg-white/[0.04] text-boss-ivory/40 transition-colors hover:border-boss-gold/25 hover:text-boss-gold/80"
                               title="{{ __('Preview course') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                    <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
                                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('admin.courses.edit', $course) }}"
                               class="flex h-8 w-8 shrink-0 items-center justify-center rounded-sm border border-boss-gold/20 bg-boss-gold/[0.08] transition-colors hover:bg-boss-gold/15"
                               title="{{ __('Edit course') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 text-boss-gold">
                                    <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z"/>
                                    <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z"/>
                                </svg>
                            </a>

                            <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" onsubmit="return confirm('{{ __('Delete this course?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-sm border border-white/[0.06] bg-white/[0.03] text-red-400/60 transition-colors hover:border-red-400/20 hover:bg-red-400/[0.08] hover:text-red-300"
                                    title="{{ __('Delete course') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/>
                                    </svg>
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
