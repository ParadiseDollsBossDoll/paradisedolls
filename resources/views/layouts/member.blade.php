@php
    $user = auth()->user();
    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'M';

    $coursesForLayout = \App\Models\Course::query()
        ->where('is_published', true)
        ->withCount(['publishedLessons as lessons_count'])
        ->get();

    $layoutTotalLessons = $coursesForLayout->sum('lessons_count');
    $layoutCompletedLessons = \App\Models\LessonProgress::query()
        ->where('user_id', $user->id)
        ->whereNotNull('completed_at')
        ->whereHas('lesson', fn ($query) => $query
            ->where('is_published', true)
            ->whereHas('course', fn ($courseQuery) => $courseQuery->where('is_published', true)))
        ->count();
    $layoutProgress = $layoutTotalLessons > 0 ? (int) round(($layoutCompletedLessons / $layoutTotalLessons) * 100) : 0;

    $links = [
        [
            'route'  => 'member.dashboard',
            'label'  => __('Dashboard'),
            'active' => request()->routeIs('member.dashboard'),
            'icon'   => 'dashboard',
        ],
        [
            'route'  => 'member.onboarding.edit',
            'label'  => __('Onboarding'),
            'active' => request()->routeIs('member.onboarding.*') || request()->routeIs('member.verification.*'),
            'icon'   => 'onboarding',
        ],
        [
            'route'  => 'member.courses.index',
            'label'  => __('Academy'),
            'active' => request()->routeIs('member.courses.*'),
            'icon'   => 'academy',
        ],
        [
            'route'  => 'community.show',
            'label'  => __('Community'),
            'active' => request()->routeIs('community.*'),
            'icon'   => 'community',
        ],
        [
            'route'  => 'profile.edit',
            'label'  => __('Profile'),
            'active' => request()->routeIs('profile.*'),
            'icon'   => 'profile',
        ],
    ];

    $currentLabel = collect($links)->firstWhere('active', true)['label'] ?? __('Dashboard');
    $hideSidebar = (bool) ($hideSidebar ?? false);
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
            @unless ($hideSidebar)
                <aside class="elysian-sidebar" data-member-sidebar="main" :class="sidebarOpen ? 'is-open' : ''">
                    <div class="elysian-brand">
                        <div>
                            <div class="elysian-brand-title">&#10022; PARADISEDOLLZ &#10022;</div>
                            <div class="elysian-brand-sub">{{ __('Members Area') }}</div>
                        </div>
                    </div>

                    <div class="elysian-side-profile">
                        <div class="elysian-side-profile-inner">
                            <div class="elysian-side-profile-row">
                                <div class="elysian-avatar-wrap">
                                    <div class="elysian-avatar">{{ $initials }}</div>
                                    <div class="elysian-online-dot"></div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="elysian-side-name">{{ $user->name }}</div>
                                    <div class="elysian-side-sub">{{ __('ParadiseDollz Member') }}</div>
                                </div>
                            </div>
                            <div class="mt-2.5 border-t border-white/[0.06] pt-2.5">
                                <div class="mb-1.5 flex items-center justify-between">
                                    <span class="text-[0.52rem] uppercase tracking-[0.12em] text-white/25">{{ __('Overall Progress') }}</span>
                                    <span class="text-[0.6rem] font-semibold text-[#c9a96e]">{{ $layoutProgress }}%</span>
                                </div>
                                <div class="h-1 w-full overflow-hidden rounded-full bg-white/[0.06]">
                                    <div class="h-full rounded-full bg-gradient-to-r from-[#c9a96e] to-[#e8c88a] transition-all duration-500" style="width: {{ $layoutProgress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <nav class="elysian-nav">
                        @foreach ($links as $link)
                            <a
                                href="{{ route($link['route']) }}"
                                class="elysian-nav-item {{ $link['active'] ? 'active' : '' }}"
                                @click="sidebarOpen = false"
                            >
                                @if ($link['icon'] === 'dashboard')
                                    <svg viewBox="0 0 16 16"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
                                @elseif ($link['icon'] === 'onboarding')
                                    <svg viewBox="0 0 16 16"><path d="M10 2h2a1 1 0 011 1v11a1 1 0 01-1 1H4a1 1 0 01-1-1V3a1 1 0 011-1h2"/><rect x="5" y="1" width="6" height="2" rx="1"/><path d="M5.5 8.5l2 2L11 7"/></svg>
                                @elseif ($link['icon'] === 'academy')
                                    <svg viewBox="0 0 16 16"><path d="M2 12V6l6-4 6 4v6"/><path d="M6 16v-5h4v5"/></svg>
                                @elseif ($link['icon'] === 'community')
                                    <svg viewBox="0 0 16 16"><path d="M14 10c0 1.1-.9 2-2 2H4l-3 3V4c0-1.1.9-2 2-2h9c1.1 0 2 .9 2 2v6z"/></svg>
                                @elseif ($link['icon'] === 'profile')
                                    <svg viewBox="0 0 16 16"><circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6"/></svg>
                                @endif
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="elysian-side-footer">
                        <a href="{{ route('home') }}" class="elysian-side-footer-btn">
                            <svg viewBox="0 0 16 16"><path d="M8 1L1 8h2.5v6h3.5v-4h2v4h3.5V8H15L8 1z"/></svg>
                            <span>{{ __('Main Site') }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="elysian-side-footer-btn">
                                <svg viewBox="0 0 16 16"><path d="M10 3l3 5-3 5M3 8h10"/></svg>
                                <span>{{ __('Sign Out') }}</span>
                            </button>
                        </form>
                    </div>
                </aside>

                <div x-show="sidebarOpen" x-cloak class="elysian-sidebar-backdrop" @click="sidebarOpen = false"></div>
            @endunless

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="elysian-topbar">
                    @unless ($hideSidebar)
                        <button type="button" class="elysian-mobile-toggle" @click="sidebarOpen = true" aria-label="{{ __('Menu') }}">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    @endunless
                    <span class="elysian-breadcrumb">{{ __('Members') }} / {{ $currentLabel }}</span>
                    <div class="elysian-topbar-right">
                        <div class="elysian-topbar-greeting">
                            <p>{{ __('Welcome back,') }}</p>
                            <p>{{ $user->name }}</p>
                        </div>
                        <a href="{{ route('home') }}#apply" class="hidden rounded-full border border-boss-gold/20 bg-boss-gold/10 px-4 py-2 text-[0.66rem] uppercase tracking-[0.14em] text-boss-gold transition-colors hover:bg-boss-gold hover:text-boss-ink sm:inline-flex">
                            {{ __('Refer') }}
                        </a>
                        <div class="elysian-topbar-avatar">{{ $initials }}</div>
                    </div>
                </header>

                <main class="flex-1 overflow-auto {{ $hideSidebar ? 'p-4 sm:p-5 lg:p-6 xl:p-8' : 'p-5 lg:p-8' }}">
                    @isset($header)
                        <div class="mb-7">{{ $header }}</div>
                    @endisset
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
