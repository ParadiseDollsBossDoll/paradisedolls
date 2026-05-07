@php
    $user = auth()->user();
    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'M';

    $coursesForLayout = \App\Models\Course::query()
        ->where('is_published', true)
        ->withCount('lessons')
        ->get();

    $layoutTotalLessons = $coursesForLayout->sum('lessons_count');
    $layoutCompletedLessons = \App\Models\LessonProgress::query()
        ->where('user_id', $user->id)
        ->whereNotNull('completed_at')
        ->whereHas('lesson.course', fn ($query) => $query->where('is_published', true))
        ->count();
    $layoutProgress = $layoutTotalLessons > 0 ? (int) round(($layoutCompletedLessons / $layoutTotalLessons) * 100) : 0;

    $links = [
        ['route' => 'member.dashboard', 'label' => __('Dashboard'), 'active' => request()->routeIs('member.dashboard')],
        ['route' => 'member.onboarding.edit', 'label' => __('Onboarding'), 'active' => request()->routeIs('member.onboarding.*') || request()->routeIs('member.verification.*')],
        ['route' => 'member.courses.index', 'label' => __('Academy'), 'active' => request()->routeIs('member.courses.*')],
        ['route' => 'profile.edit', 'label' => __('Profile'), 'active' => request()->routeIs('profile.*')],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased pd-dark-surface min-h-screen" x-data="{ sidebarOpen: false }">
        <div class="flex min-h-screen">
            <aside
                class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-white/[0.06] bg-boss-panel transition-transform duration-300 lg:static lg:inset-auto lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            >
                <div class="border-b border-white/[0.06] px-5 py-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-boss-gold/25 bg-boss-gold/10 font-display text-[0.72rem] text-boss-gold shadow-glow">
                            PD
                        </div>
                        <div>
                            <p class="font-display text-[1rem] leading-none text-boss-ivory">{{ config('app.name') }}</p>
                            <p class="mt-1 text-[0.58rem] uppercase tracking-[0.15em] text-boss-ivory/25">{{ __('Academy') }}</p>
                        </div>
                    </div>
                </div>

                <div class="border-b border-white/[0.06] px-4 py-4">
                    <div class="rounded-2xl border border-boss-gold/10 bg-boss-gold/[0.05] p-3">
                        <div class="mb-3 flex items-center gap-3">
                            <div class="relative">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full border border-boss-gold/30 bg-boss-gold/10 font-display text-[0.78rem] text-boss-gold-light">
                                    {{ $initials }}
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-boss-panel bg-green-400"></span>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-[0.85rem] font-medium text-boss-ivory">{{ $user->name }}</p>
                                <p class="truncate text-[0.65rem] text-boss-ivory/35">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[0.6rem] uppercase tracking-[0.12em] text-boss-ivory/30">{{ __('Overall Progress') }}</span>
                            <span class="font-display text-[0.78rem] text-boss-gold">{{ $layoutProgress }}%</span>
                        </div>
                        <div class="pd-progress-track mt-2">
                            <div class="pd-progress-bar" style="width: {{ $layoutProgress }}%"></div>
                        </div>
                    </div>
                </div>

                <nav class="flex-1 space-y-1 px-3 py-4">
                    @foreach ($links as $link)
                        <a
                            href="{{ route($link['route']) }}"
                            class="relative flex items-center gap-3 rounded-xl border px-3.5 py-3 text-[0.83rem] transition-all duration-200 {{ $link['active'] ? 'border-boss-gold/20 bg-boss-gold/[0.08] text-boss-gold-light' : 'border-transparent text-boss-ivory/42 hover:border-white/[0.06] hover:bg-white/[0.035] hover:text-boss-ivory/75' }}"
                            @click="sidebarOpen = false"
                        >
                            @if ($link['active'])
                                <span class="absolute left-0 top-1/2 h-5 w-0.5 -translate-y-1/2 rounded-full bg-gradient-to-b from-boss-gold to-boss-gold-light"></span>
                            @endif
                            <span class="h-1.5 w-1.5 rounded-full {{ $link['active'] ? 'bg-boss-gold' : 'bg-boss-ivory/18' }}"></span>
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="space-y-1 border-t border-white/[0.06] px-3 pb-5 pt-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-[0.75rem] text-boss-ivory/24 transition-colors hover:text-boss-ivory/55">
                        {{ __('Main Site') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-left text-[0.75rem] text-boss-ivory/24 transition-colors hover:text-red-400">
                            {{ __('Sign Out') }}
                        </button>
                    </form>
                </div>
            </aside>

            <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-black/80 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-white/[0.04] bg-boss-ink/90 px-5 py-4 backdrop-blur lg:px-8">
                    <button type="button" class="rounded-lg border border-white/[0.06] bg-white/[0.04] p-2 text-boss-ivory/45 lg:hidden" @click="sidebarOpen = true" aria-label="{{ __('Menu') }}">
                        <span class="block space-y-1">
                            <span class="block h-px w-4 bg-current"></span>
                            <span class="block h-px w-4 bg-current"></span>
                            <span class="block h-px w-4 bg-current"></span>
                        </span>
                    </button>
                    <div class="flex-1">
                        <p class="text-[0.72rem] text-boss-ivory/35">{{ __('Welcome back,') }}</p>
                        <p class="font-display text-[0.9rem] text-boss-gold">{{ $user->name }}</p>
                    </div>
                    <a href="{{ route('home') }}#apply" class="hidden rounded-full border border-boss-gold/20 bg-boss-gold/10 px-4 py-2 text-[0.66rem] uppercase tracking-[0.14em] text-boss-gold transition-colors hover:bg-boss-gold hover:text-boss-ink sm:inline-flex">
                        {{ __('Refer') }}
                    </a>
                </header>

                <main class="flex-1 overflow-auto p-5 lg:p-8">
                    @isset($header)
                        <div class="mb-7">{{ $header }}</div>
                    @endisset
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
