@php
    $user = auth()->user();
    $serverInitials = collect(explode(' ', $communityState['server']['name']))->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
    $profilePhotoUrl = $user->profilePhotoUrl();

    if ($user->isAdmin()) {
        [$pendingApps, $pendingVerif, $pendingReferrals] = \Illuminate\Support\Facades\Cache::remember(
            'admin_sidebar_counts', 60,
            fn () => [
                \App\Models\ModelApplication::where('status', \App\Models\ModelApplication::STATUS_PENDING)->count(),
                \App\Models\ModelProfile::where('verification_status', \App\Models\ModelProfile::VERIFICATION_SUBMITTED)->count(),
                \App\Models\ModelReferral::query()
                    ->where(function ($q) {
                        $q->where(function ($lead) {
                            $lead->where('status', \App\Models\ModelReferral::STATUS_REFERRED)->whereNull('model_application_id');
                        })->orWhere('reward_status', \App\Models\ModelReferral::REWARD_ELIGIBLE);
                    })->count(),
            ]
        );

        $sidebarLinks = [
            ['href' => route('admin.dashboard'),          'label' => __('Overview'),       'icon' => 'overview',      'active' => false, 'count' => 0],
            ['href' => route('admin.applications.index'), 'label' => __('Applications'),   'icon' => 'applications',  'active' => false, 'count' => $pendingApps],
            ['href' => route('admin.referrals.index'),    'label' => __('Referrals'),      'icon' => 'referrals',     'active' => false, 'count' => $pendingReferrals],
            ['href' => route('admin.onboarding.index'),   'label' => __('Onboarding'),     'icon' => 'onboarding',    'active' => false, 'count' => $pendingVerif],
            ['href' => route('admin.models.progress'),    'label' => __('Members'),        'icon' => 'members',       'active' => false, 'count' => 0],
            ['href' => route('admin.courses.index'),      'label' => __('Courses'),        'icon' => 'courses',       'active' => false, 'count' => 0],
            ['href' => route('admin.testimonials.index'), 'label' => __('Testimonials'),   'icon' => 'stories',       'active' => false, 'count' => 0],
            ['href' => route('community.show'),           'label' => __('Community Chat'), 'icon' => 'community',     'active' => true,  'count' => 0],
        ];
        $sidebarSubtitle  = __('Admin Panel');
        $sidebarRole      = __('Administrator');
        $sidebarProgress  = null;
    } else {
        $coursesForLayout = \App\Models\Course::where('is_published', true)
            ->withCount(['publishedLessons as lessons_count'])
            ->get();
        $totalLessons     = $coursesForLayout->sum('lessons_count');
        $doneLessons      = \App\Models\LessonProgress::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->whereHas('lesson', fn ($q) => $q->where('is_published', true)
                ->whereHas('course', fn ($c) => $c->where('is_published', true)))
            ->count();
        $sidebarProgress  = $totalLessons > 0 ? (int) round(($doneLessons / $totalLessons) * 100) : 0;

        $sidebarLinks = [
            ['href' => route('member.dashboard'),          'label' => __('Dashboard'),    'icon' => 'dashboard',  'active' => false, 'count' => 0],
            ['href' => route('member.onboarding.edit'),    'label' => __('Onboarding'),   'icon' => 'onboarding', 'active' => false, 'count' => 0],
            ['href' => route('member.courses.index'),      'label' => __('Academy'),      'icon' => 'academy',    'active' => false, 'count' => 0],
            ['href' => route('member.testimonials.create'),'label' => __('My Testimony'), 'icon' => 'stories',    'active' => false, 'count' => 0],
            ['href' => route('member.referrals.index'),    'label' => __('Referrals'),    'icon' => 'referrals',  'active' => false, 'count' => 0],
            ['href' => route('community.show'),            'label' => __('Community Chat'),'icon' => 'community', 'active' => true,  'count' => 0],
            ['href' => route('profile.edit'),              'label' => __('Profile'),      'icon' => 'profile',    'active' => false, 'count' => 0],
        ];
        $sidebarSubtitle = __('Members Area');
        $sidebarRole     = __('Paradise Dolls Member');
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('Community Chat').' - '.config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-hidden bg-[#080808] text-[#f0ede8] font-sans antialiased">
        <div x-data="communityChat(@js($communityState))" x-init="init()" class="flex h-screen min-h-0">
            {{-- Transparent backdrop: intercepts all pointer/hover events from chat when pinned panel is open --}}
            <div x-show="pinnedPanelOpen" x-cloak class="fixed inset-0 z-[80]" @click="pinnedPanelOpen = false" aria-hidden="true"></div>
            <aside class="elysian-sidebar" :class="shellDrawerOpen ? 'is-open' : ''">
                <div class="elysian-brand">
                    <div>
                        <div class="elysian-brand-title">&#10022; PARADISEDOLLZ &#10022;</div>
                        <div class="elysian-brand-sub">{{ $sidebarSubtitle }}</div>
                    </div>
                </div>

                <div class="elysian-side-profile">
                    <div class="elysian-side-profile-inner">
                        <div class="elysian-side-profile-row">
                            <div class="elysian-avatar-wrap">
                                <div class="elysian-avatar">
                                    <span>{{ $user->initials() }}</span>
                                    @if ($profilePhotoUrl)
                                        <img src="{{ $profilePhotoUrl }}" alt="{{ __('Profile photo') }}" onerror="this.remove()">
                                    @endif
                                </div>
                                <div class="elysian-online-dot"></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="elysian-side-name">{{ $user->name }}</div>
                                <div class="elysian-side-sub">{{ $sidebarRole }}</div>
                            </div>
                        </div>
                        @if ($sidebarProgress !== null)
                            <div class="mt-2.5 border-t border-white/[0.06] pt-2.5">
                                <div class="mb-1.5 flex items-center justify-between">
                                    <span class="text-[0.52rem] uppercase tracking-[0.12em] text-white/25">{{ __('Overall Progress') }}</span>
                                    <span class="text-[0.6rem] font-semibold text-[#c9a96e]">{{ $sidebarProgress }}%</span>
                                </div>
                                <div class="h-1 w-full overflow-hidden rounded-full bg-white/[0.06]">
                                    <div class="h-full rounded-full bg-gradient-to-r from-[#c9a96e] to-[#e8c88a]" style="width: {{ $sidebarProgress }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <nav class="elysian-nav">
                    @foreach ($sidebarLinks as $link)
                        <a href="{{ $link['href'] }}" class="elysian-nav-item {{ $link['active'] ? 'active' : '' }}">
                            @if ($link['icon'] === 'dashboard' || $link['icon'] === 'overview')
                                <svg viewBox="0 0 16 16"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
                            @elseif ($link['icon'] === 'onboarding')
                                <svg viewBox="0 0 16 16"><path d="M10 2h2a1 1 0 011 1v11a1 1 0 01-1 1H4a1 1 0 01-1-1V3a1 1 0 011-1h2"/><rect x="5" y="1" width="6" height="2" rx="1"/><path d="M5.5 8.5l2 2L11 7"/></svg>
                            @elseif ($link['icon'] === 'academy' || $link['icon'] === 'courses')
                                <svg viewBox="0 0 16 16"><path d="M2 12V6l6-4 6 4v6"/><path d="M6 16v-5h4v5"/></svg>
                            @elseif ($link['icon'] === 'community')
                                <svg viewBox="0 0 16 16"><path d="M14 10c0 1.1-.9 2-2 2H4l-3 3V4c0-1.1.9-2 2-2h9c1.1 0 2 .9 2 2v6z"/></svg>
                            @elseif ($link['icon'] === 'profile')
                                <svg viewBox="0 0 16 16"><circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6"/></svg>
                            @elseif ($link['icon'] === 'applications')
                                <svg viewBox="0 0 16 16"><path d="M10 2h2a1 1 0 011 1v11a1 1 0 01-1 1H4a1 1 0 01-1-1V3a1 1 0 011-1h2"/><rect x="5" y="1" width="6" height="2" rx="1"/><path d="M5 7h6M5 10h4"/></svg>
                            @elseif ($link['icon'] === 'members')
                                <svg viewBox="0 0 16 16"><circle cx="5.5" cy="5" r="2.5"/><path d="M1 13c0-2.5 2-4.5 4.5-4.5S10 10.5 10 13"/><circle cx="11.5" cy="5.5" r="2"/><path d="M10 12.5c.2-1.4 1.3-2.5 2.7-2.5 1.5 0 2.8 1.1 2.8 2.5"/></svg>
                            @elseif ($link['icon'] === 'stories')
                                <svg viewBox="0 0 16 16"><path d="M3 2h10v12H3z"/><path d="M5.5 5h5M5.5 8h5M5.5 11h3"/></svg>
                            @elseif ($link['icon'] === 'referrals')
                                <svg viewBox="0 0 16 16"><circle cx="5" cy="5" r="2.5"/><circle cx="11.5" cy="4.5" r="2"/><path d="M1.5 13c0-2.5 1.8-4.5 4-4.5 1.4 0 2.6.7 3.3 1.8"/><path d="M9.5 10.5h4M11.5 8.5v4"/></svg>
                            @endif
                            <span class="flex-1">{{ $link['label'] }}</span>
                            @if ($link['count'] > 0)
                                <span class="ml-auto flex h-4 min-w-4 items-center justify-center rounded-full bg-[#c9a96e] px-1 text-[0.52rem] font-bold text-[#080808]">{{ $link['count'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <div class="elysian-side-footer">
                    @if ($user->isAdmin())
                        <a href="{{ route('admin.courses.create') }}" class="elysian-side-footer-btn" style="color: rgba(201,169,110,0.7);" @click="shellDrawerOpen = false">
                            <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="7"/><path d="M8 5v6M5 8h6"/></svg>
                            <span>{{ __('New Course') }}</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="elysian-side-footer-btn" @click="shellDrawerOpen = false">
                            <svg viewBox="0 0 16 16"><circle cx="8" cy="5.5" r="3"/><path d="M2.5 14c0-3 2.5-5.5 5.5-5.5s5.5 2.5 5.5 5.5"/></svg>
                            <span>{{ __('My Profile') }}</span>
                        </a>
                    @endif
                    <a href="{{ route('home') }}" class="elysian-side-footer-btn" @click="shellDrawerOpen = false">
                        <svg viewBox="0 0 16 16"><path d="M8 1L1 8h2.5v6h3.5v-4h2v4h3.5V8H15L8 1z"/></svg>
                        <span>{{ __('Main Site') }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="elysian-side-footer-btn">
                            <svg viewBox="0 0 16 16"><path d="M10 3l3 5-3 5M3 8h10"></path></svg>
                            <span>{{ __('Sign Out') }}</span>
                        </button>
                    </form>
                </div>
            </aside>

            <div x-show="shellDrawerOpen" x-cloak class="elysian-sidebar-backdrop" @click="shellDrawerOpen = false"></div>

            <main class="flex min-w-0 flex-1 flex-col overflow-hidden">
                <header class="elysian-topbar">
                    <button type="button" class="elysian-mobile-toggle" @click="shellDrawerOpen = true" aria-label="{{ __('Open menu') }}">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <span class="elysian-breadcrumb">{{ __('Members') }} / {{ __('Community Chat') }}</span>
                    <div class="elysian-topbar-right">
                        <div class="elysian-topbar-greeting">
                            <p>{{ __('Welcome back,') }}</p>
                            <p>{{ $user->name }}</p>
                        </div>
                        <div class="elysian-topbar-avatar">
                            <span>{{ $user->initials() }}</span>
                            @if ($profilePhotoUrl)
                                <img src="{{ $profilePhotoUrl }}" alt="{{ __('Profile photo') }}" onerror="this.remove()">
                            @endif
                        </div>
                    </div>
                </header>

                <div class="flex min-h-0 flex-1 overflow-hidden">
                    <div x-show="channelDrawerOpen" x-cloak class="fixed inset-0 z-40 bg-black/70 xl:hidden" @click="channelDrawerOpen = false"></div>
                    <div x-show="membersDrawerOpen" x-cloak class="fixed inset-0 z-40 bg-black/70 xl:hidden" @click="membersDrawerOpen = false"></div>

                    <section class="fixed inset-y-0 left-0 z-50 flex w-[90vw] max-w-[360px] shrink-0 -translate-x-full border-r border-white/5 bg-[#100c0f] transition-transform xl:static xl:z-auto xl:w-[270px] xl:translate-x-0" :class="channelDrawerOpen ? 'translate-x-0' : '-translate-x-full xl:translate-x-0'">
                        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
                            <div class="border-b border-black/40 px-4 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-display text-[1.42rem] font-semibold leading-[1.12] text-[#f0ede8] whitespace-normal break-words" x-text="server.name"></div>
                                        <span class="mt-2 inline-flex rounded-full bg-[#2a211b] px-3 py-1 text-[0.62rem] uppercase tracking-[0.14em] text-[#c9a96e]" x-text="server.subtitle"></span>
                                    </div>
                                    <button type="button" class="rounded-lg p-2 text-white/40 transition hover:bg-white/5 hover:text-white xl:hidden" @click="channelDrawerOpen = false">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="community-scroll flex-1 overflow-y-auto px-3 py-4">
                                <div class="flex items-center justify-between gap-3 px-2 pb-3">
                                    <div class="text-[0.58rem] uppercase tracking-[0.14em] text-white/25">{{ __('Channels') }}</div>
                                    <button
                                        x-show="features.can_manage_channels"
                                        x-cloak
                                        type="button"
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-[#c9a96e]/18 bg-white/[0.03] text-[#e7cfaa] transition hover:border-[#c9a96e]/35 hover:bg-[#2a211b] hover:text-[#f4dfb8]"
                                        @click="openChannelModal()"
                                        aria-label="{{ __('Add channel') }}"
                                    >
                                        <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 stroke-current fill-none stroke-[1.8]">
                                            <path d="M8 3v10M3 8h10"></path>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Global channels (non-course) --}}
                                <div x-show="globalChannels().length" class="mb-1 flex items-center gap-2 px-2">
                                    <div class="text-[0.58rem] uppercase tracking-[0.14em] text-white/25">{{ __('Global Community') }}</div>
                                </div>
                                <div class="space-y-1">
                                    <template x-for="channel in globalChannels()" :key="channel.id">
                                        <div class="group flex items-center gap-1">
                                            <button
                                                type="button"
                                                class="flex min-w-0 flex-1 items-start gap-3 rounded-lg px-3 py-2 text-left text-[0.82rem] transition"
                                                :class="selectedChannel?.slug === channel.slug ? 'bg-white/8 text-[#f0ede8] font-semibold' : channel.can_access ? 'text-white/40 hover:bg-white/5 hover:text-white/70' : 'text-white/28 hover:bg-white/[0.03] hover:text-white/45'"
                                                @click="handleChannelClick(channel)"
                                            >
                                                <span class="shrink-0 font-display text-[1rem] leading-none text-white/55">#</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="truncate" x-text="channel.name"></span>
                                                        <span x-show="channel.unread_count" x-cloak class="rounded-full bg-[#ef4444] px-1.5 py-0.5 text-[0.48rem] font-bold text-white" x-text="channel.unread_count"></span>
                                                    </div>
                                                    <div class="mt-0.5 truncate text-[0.58rem] uppercase tracking-[0.12em]" :class="channel.can_access ? 'text-white/25' : 'text-[#c9a96e]/65'" x-text="channel.permission_summary"></div>
                                                </div>
                                                <div x-show="channel.is_locked || !channel.can_access" x-cloak class="group/lock relative ml-auto mt-0.5 shrink-0">
                                                    <svg viewBox="0 0 12 12" class="h-3.5 w-3.5 stroke-white/30 fill-none stroke-[1.8]">
                                                        <rect x="2" y="5" width="8" height="6" rx="1"></rect>
                                                        <path d="M4 5V3.5a2 2 0 0 1 4 0V5"></path>
                                                    </svg>
                                                    <span class="pointer-events-none absolute bottom-full right-0 mb-2 rounded-full border border-[#c9a96e]/18 bg-[#161114] px-2.5 py-1 text-[0.52rem] uppercase tracking-[0.14em] text-[#e7cfaa] opacity-0 shadow-[0_10px_24px_rgba(0,0,0,0.28)] transition duration-150 group-hover/lock:opacity-100">
                                                        {{ __('Lock') }}
                                                    </span>
                                                </div>
                                            </button>
                                            <div x-show="channel.can_manage" x-cloak class="hidden items-center gap-1 group-hover:flex">
                                                <div x-show="features.can_manage_channels" class="group/up relative">
                                                    <button type="button" class="rounded p-1 text-white/30 transition hover:bg-white/5 hover:text-white" @click.stop="moveChannel(channel, 'up')" aria-label="{{ __('Move up') }}">
                                                        &#8593;
                                                    </button>
                                                    <span class="pointer-events-none absolute bottom-full right-0 mb-2 rounded-full border border-[#c9a96e]/18 bg-[#161114] px-2.5 py-1 text-[0.52rem] uppercase tracking-[0.14em] text-[#e7cfaa] opacity-0 shadow-[0_10px_24px_rgba(0,0,0,0.28)] transition duration-150 group-hover/up:opacity-100">
                                                        {{ __('Move up') }}
                                                    </span>
                                                </div>
                                                <div x-show="features.can_manage_channels" class="group/down relative">
                                                    <button type="button" class="rounded p-1 text-white/30 transition hover:bg-white/5 hover:text-white" @click.stop="moveChannel(channel, 'down')" aria-label="{{ __('Move down') }}">
                                                        &#8595;
                                                    </button>
                                                    <span class="pointer-events-none absolute bottom-full right-0 mb-2 rounded-full border border-[#c9a96e]/18 bg-[#161114] px-2.5 py-1 text-[0.52rem] uppercase tracking-[0.14em] text-[#e7cfaa] opacity-0 shadow-[0_10px_24px_rgba(0,0,0,0.28)] transition duration-150 group-hover/down:opacity-100">
                                                        {{ __('Move down') }}
                                                    </span>
                                                </div>
                                                <div class="group/edit relative">
                                                    <button type="button" class="rounded p-1 text-white/30 transition hover:bg-white/5 hover:text-[#c9a96e]" @click.stop="openChannelModal(channel)" aria-label="{{ __('Edit') }}">
                                                        &#9998;
                                                    </button>
                                                    <span class="pointer-events-none absolute bottom-full right-0 mb-2 rounded-full border border-[#c9a96e]/18 bg-[#161114] px-2.5 py-1 text-[0.52rem] uppercase tracking-[0.14em] text-[#e7cfaa] opacity-0 shadow-[0_10px_24px_rgba(0,0,0,0.28)] transition duration-150 group-hover/edit:opacity-100">
                                                        {{ __('Edit') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Academy course chats — flat list with search --}}
                                <div x-show="hasCourseChannels()" x-cloak class="mt-5">
                                    <div class="mb-2 flex items-center gap-2 px-2">
                                        <svg viewBox="0 0 16 16" class="h-3 w-3 shrink-0 fill-none stroke-[#c9a96e]/60 stroke-[1.6]"><path d="M2 12V6l6-4 6 4v6"/><path d="M6 16v-5h4v5"/></svg>
                                        <div class="text-[0.58rem] uppercase tracking-[0.14em] text-[#c9a96e]/60">{{ __('My Course Chats') }}</div>
                                    </div>

                                    <div class="mb-2 px-1">
                                        <div class="flex items-center gap-2 rounded-xl border border-white/[0.07] bg-white/[0.03] px-3 py-2">
                                            <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 shrink-0 fill-none stroke-white/25 stroke-[1.8]"><circle cx="7" cy="7" r="4"/><path d="M10 10l3 3"/></svg>
                                            <input
                                                x-model="courseChannelSearch"
                                                type="text"
                                                class="w-full border-0 bg-transparent py-0 text-[0.76rem] text-white placeholder:text-white/20 focus:outline-none focus:ring-0"
                                                placeholder="{{ __('Search course chats…') }}"
                                            >
                                            <button x-show="courseChannelSearch" x-cloak type="button" class="shrink-0 text-white/30 hover:text-white/60" @click="courseChannelSearch = ''">
                                                <svg viewBox="0 0 12 12" class="h-3 w-3 fill-none stroke-current stroke-[1.8]"><path d="M2 2l8 8M10 2l-8 8"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-0.5">
                                        <template x-for="channel in filteredCourseChannels()" :key="channel.id">
                                            <div class="group flex items-center gap-1">
                                                <button
                                                    type="button"
                                                    class="flex min-w-0 flex-1 items-center gap-2.5 rounded-lg px-3 py-2 text-left text-[0.82rem] transition"
                                                    :class="selectedChannel?.slug === channel.slug ? 'bg-white/8 text-[#f0ede8] font-semibold' : channel.can_access ? 'text-white/40 hover:bg-white/5 hover:text-white/70' : 'text-white/25 hover:bg-white/[0.03] hover:text-white/40'"
                                                    @click="handleChannelClick(channel)"
                                                >
                                                    <span class="shrink-0 font-display text-[1rem] leading-none text-white/40">#</span>
                                                    <span class="min-w-0 flex-1 truncate" x-text="channel.course_name || channel.name"></span>
                                                    <span x-show="channel.unread_count" x-cloak class="ml-auto shrink-0 rounded-full bg-[#ef4444] px-1.5 py-0.5 text-[0.44rem] font-bold text-white" x-text="channel.unread_count"></span>
                                                </button>
                                                <div x-show="channel.can_manage" x-cloak class="hidden items-center gap-1 group-hover:flex">
                                                    <button type="button" class="rounded p-1 text-white/30 transition hover:bg-white/5 hover:text-[#c9a96e]" @click.stop="openChannelModal(channel)" aria-label="{{ __('Edit') }}">
                                                        &#9998;
                                                    </button>
                                                </div>
                                            </div>
                                        </template>

                                        <div x-show="hasCourseChannels() && filteredCourseChannels().length === 0" x-cloak class="px-3 py-3 text-[0.72rem] text-white/25">
                                            {{ __('No course chats found.') }}
                                        </div>
                                    </div>
                                </div>

                                <div x-show="archivedChannels.length && features.can_moderate_messages" x-cloak class="mt-6">
                                    <div class="px-2 pb-3 text-[0.58rem] uppercase tracking-[0.14em] text-white/25">{{ __('Archived') }}</div>
                                    <div class="space-y-2">
                                        <template x-for="channel in archivedChannels" :key="`archived-${channel.id}`">
                                            <div class="rounded-2xl border border-white/8 bg-white/[0.03] px-3 py-3">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="truncate text-[0.76rem] font-semibold text-[#f0ede8]" x-text="channel.name"></div>
                                                        <div class="mt-1 text-[0.58rem] uppercase tracking-[0.12em] text-white/30" x-text="channel.permission_summary"></div>
                                                    </div>
                                                    <button type="button" class="rounded-full border border-[#c9a96e]/20 px-3 py-1 text-[0.58rem] uppercase tracking-[0.12em] text-[#e7cfaa] transition hover:border-[#c9a96e]/35 hover:text-[#f4dfb8]" @click="restoreChannel(channel)">{{ __('Restore') }}</button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                            </div>

                            <div class="border-t border-black/40 bg-black/25 p-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative shrink-0">
                                        <div class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full border border-[#c9a96e]/25 bg-[linear-gradient(135deg,rgba(201,169,110,0.28),rgba(201,169,110,0.08))] font-display text-[0.58rem] text-[#e8c88a]">
                                            <template x-if="user.profile_photo_url">
                                                <img :src="user.profile_photo_url" alt="" class="h-full w-full object-cover">
                                            </template>
                                            <template x-if="!user.profile_photo_url">
                                                <span x-text="user.initials"></span>
                                            </template>
                                        </div>
                                        <div class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-[#0b0b15] bg-[#4ade80]"></div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-[0.72rem] font-semibold text-[#f0ede8]" x-text="user.name"></div>
                                        <div class="text-[0.57rem] text-white/35">{{ __('Online') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="flex min-w-0 flex-1 flex-col overflow-hidden bg-[#080808]">
                        <div class="relative flex items-center gap-3 border-b border-white/5 bg-[#0a0a0f]/92 px-5 py-4 backdrop-blur" :class="pinnedPanelOpen ? 'z-[90]' : 'z-10'">
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-white/30 transition hover:bg-white/5 hover:text-white xl:hidden" @click="channelDrawerOpen = true">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 7h16M4 12h16M4 17h16"></path>
                                </svg>
                            </button>
                            <span class="shrink-0 font-display text-[1.1rem] leading-none text-white/35">#</span>
                            <span class="text-[0.95rem] font-bold text-[#f0ede8]" x-text="selectedChannel?.name || 'general'"></span>
                            <div x-show="selectedChannel?.permission_summary" x-cloak class="group relative shrink-0">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#c9a96e]/18 bg-[#1b1714] text-[#e7cfaa] transition group-hover:border-[#c9a96e]/35 group-hover:text-[#f4dfb8]">
                                    <svg viewBox="0 0 16 16" class="h-4 w-4 stroke-current fill-none stroke-[1.5]">
                                        <circle cx="5.25" cy="5" r="2.25"></circle>
                                        <path d="M1.75 12c0-2 1.55-3.75 3.5-3.75S8.75 10 8.75 12"></path>
                                        <circle cx="11.5" cy="5.5" r="1.75"></circle>
                                        <path d="M9.75 11.5c.2-1.55 1.42-2.75 3-2.75 1.03 0 1.95.52 2.5 1.33"></path>
                                    </svg>
                                </span>
                                <span class="pointer-events-none absolute left-1/2 top-full z-10 mt-2 -translate-x-1/2 whitespace-nowrap rounded-full border border-[#c9a96e]/18 bg-[#161114] px-3 py-1 text-[0.58rem] uppercase tracking-[0.16em] text-[#e7cfaa] opacity-0 shadow-[0_10px_24px_rgba(0,0,0,0.28)] transition duration-150 group-hover:opacity-100" x-text="selectedChannel?.permission_summary"></span>
                            </div>
                            <span x-show="selectedChannel?.is_locked" x-cloak class="rounded-full border border-white/10 bg-white/[0.03] px-3 py-1 text-[0.58rem] uppercase tracking-[0.16em] text-white/45">{{ __('Locked') }}</span>
                            <div class="h-4 w-px bg-white/10"></div>
                            <span class="min-w-0 flex-1 truncate text-[0.76rem] text-white/35" x-text="selectedChannel?.description || 'General community chat & discussion'"></span>
                            <div class="ml-auto hidden items-center gap-2 md:flex">

                                <div class="flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-3 py-1.5">
                                    <svg viewBox="0 0 16 16" class="h-4 w-4 shrink-0 stroke-current fill-none stroke-[1.8] text-white/25"><circle cx="7" cy="7" r="4"></circle><path d="M10 10l3 3"></path></svg>
                                    <input x-model="searchQuery" @input="handleSearchInput()" type="text" class="w-48 border-0 bg-transparent px-0 py-0 text-[0.74rem] text-white placeholder:text-white/20 focus:outline-none focus:ring-0" placeholder="{{ __('Search messages') }}">
                                </div>

                                <div class="h-4 w-px bg-white/10"></div>

                                {{-- Mobile: open drawer overlay --}}
                                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-white/30 transition hover:bg-white/5 hover:text-white xl:hidden" @click="membersDrawerOpen = !membersDrawerOpen" title="{{ __('Members') }}">
                                    <svg viewBox="0 0 16 16" class="h-[17px] w-[17px] fill-none stroke-current stroke-[1.5]" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="5.5" cy="4.5" r="2.5"/>
                                        <path d="M0.5 14a5 5 0 0 1 10 0"/>
                                        <circle cx="12.5" cy="4" r="2" opacity=".45"/>
                                        <path d="M11 13.5a3.5 3.5 0 0 1 4.5 0" opacity=".45"/>
                                    </svg>
                                </button>
                                {{-- Desktop: toggle members sidebar --}}
                                <button type="button" class="hidden xl:flex h-8 w-8 items-center justify-center rounded-lg transition" :class="membersOpen ? 'bg-white/[0.08] text-[#c9a96e]' : 'text-white/30 hover:bg-white/[0.05] hover:text-white'" @click="membersOpen = !membersOpen" :title="membersOpen ? '{{ __('Hide members') }}' : '{{ __('Show members') }}'">
                                    <svg viewBox="0 0 16 16" class="h-[17px] w-[17px] fill-none stroke-current stroke-[1.5]" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="5.5" cy="4.5" r="2.5"/>
                                        <path d="M0.5 14a5 5 0 0 1 10 0"/>
                                        <circle cx="12.5" cy="4" r="2" opacity=".45"/>
                                        <path d="M11 13.5a3.5 3.5 0 0 1 4.5 0" opacity=".45"/>
                                    </svg>
                                </button>

                                {{-- Pinned messages toggle + floating panel --}}
                                <div class="relative">
                                    <button type="button"
                                        x-show="pinnedMessages().length > 0" x-cloak
                                        class="flex h-8 w-8 items-center justify-center rounded-lg transition"
                                        :class="pinnedPanelOpen ? 'bg-white/[0.08] text-[#c9a96e]' : 'text-white/30 hover:bg-white/[0.05] hover:text-white'"
                                        @click="pinnedPanelOpen = !pinnedPanelOpen"
                                        title="{{ __('Pinned messages') }}">
                                        <svg viewBox="0 0 16 16" class="h-[15px] w-[15px] fill-none stroke-current stroke-[1.5]" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M8 2l2 2-3.5 3.5 1.5 1.5-4.5 4.5H2v-1.5l4.5-4.5L5 6.5z"/>
                                            <path d="M13 1l2 2"/>
                                            <path d="M1 15l3-3"/>
                                        </svg>
                                    </button>

                                    {{-- Floating pinned panel --}}
                                    <div x-show="pinnedPanelOpen" x-cloak
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                        class="absolute right-0 top-full z-[91] mt-2 w-80 overflow-hidden rounded-2xl border border-white/[0.08] bg-[#111015] shadow-[0_20px_50px_rgba(0,0,0,0.65)] will-change-transform"
                                        @keydown.escape.window="pinnedPanelOpen = false">

                                        {{-- Panel header --}}
                                        <div class="flex items-center justify-between border-b border-white/[0.06] px-4 py-2.5">
                                            <div class="flex items-center gap-2">
                                                <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.5] text-[#c9a96e]" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M8 2l2 2-3.5 3.5 1.5 1.5-4.5 4.5H2v-1.5l4.5-4.5L5 6.5z"/>
                                                    <path d="M1 15l3-3"/>
                                                </svg>
                                                <span class="text-[0.64rem] font-semibold uppercase tracking-[0.16em] text-white/50">{{ __('Pinned Messages') }}</span>
                                                <span class="rounded-full bg-[#c9a96e]/15 px-2 py-0.5 text-[0.56rem] font-semibold text-[#c9a96e]" x-text="pinnedMessages().length"></span>
                                            </div>
                                            <button type="button" @click="pinnedPanelOpen = false" class="flex h-6 w-6 items-center justify-center rounded-lg text-white/30 transition hover:text-white">
                                                <svg viewBox="0 0 16 16" class="h-3 w-3 fill-none stroke-current stroke-[2]"><path d="M2 2l12 12M14 2L2 14"></path></svg>
                                            </button>
                                        </div>

                                        {{-- Empty state --}}
                                        <template x-if="!pinnedMessages().length">
                                            <div class="px-4 py-8 text-center">
                                                <svg viewBox="0 0 16 16" class="mx-auto mb-2 h-6 w-6 fill-none stroke-current stroke-[1.2] text-white/15" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M8 2l2 2-3.5 3.5 1.5 1.5-4.5 4.5H2v-1.5l4.5-4.5L5 6.5z"/>
                                                    <path d="M1 15l3-3"/>
                                                </svg>
                                                <p class="text-[0.75rem] text-white/28">{{ __('No pinned messages yet.') }}</p>
                                            </div>
                                        </template>

                                        {{-- Pinned message list --}}
                                        <div x-show="pinnedMessages().length" class="max-h-[340px] space-y-1.5 overflow-y-auto p-2 community-scroll">
                                            <template x-for="msg in pinnedMessages()" :key="`panel-pinned-${msg.id}`">
                                                <div class="group relative flex w-full items-start gap-3 rounded-xl border border-white/[0.07] bg-white/[0.025] px-3 py-3 text-left transition hover:border-white/[0.12] hover:bg-white/[0.045]"
                                                    :class="isHighlightedMessage(msg.id) ? 'bg-[#c9a96e]/10' : ''">
                                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/[0.08] font-display text-[0.56rem] font-bold text-white" :style="`background: radial-gradient(circle at top, ${msg.user.accent}, #151014 70%)`">
                                                        <template x-if="msg.user.profile_photo_url">
                                                            <img :src="msg.user.profile_photo_url" alt="" class="h-full w-full object-cover">
                                                        </template>
                                                        <template x-if="!msg.user.profile_photo_url">
                                                            <span x-text="msg.user.initials"></span>
                                                        </template>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pr-[5.75rem] md:pr-0 md:group-hover:pr-[5.75rem]">
                                                        <div class="flex min-w-0 items-center gap-1.5 leading-none">
                                                            <span class="min-w-0 truncate text-[0.76rem] font-semibold text-[#f0ede8]" x-text="msg.user.name"></span>
                                                            <span class="shrink-0 whitespace-nowrap text-[0.56rem] font-semibold text-white/28" :title="formatFullTimestamp(msg.created_at)" x-text="formatMessageTime(msg.created_at)"></span>
                                                        </div>
                                                        <p class="mt-1 line-clamp-2 text-[0.74rem] leading-[1.35] text-white/58" x-text="msg.message || msg.attachment?.name || 'Attachment'"></p>
                                                    </div>
                                                    <div class="pointer-events-auto absolute right-2.5 top-2.5 flex items-center gap-1 opacity-100 transition-opacity md:pointer-events-none md:opacity-0 md:group-hover:pointer-events-auto md:group-hover:opacity-100 md:group-focus-within:pointer-events-auto md:group-focus-within:opacity-100">
                                                        <button type="button"
                                                            @click.stop="jumpToMessage(msg.id); pinnedPanelOpen = false"
                                                            class="rounded-lg border border-white/8 bg-[#222127] px-2.5 py-1.5 text-[0.68rem] font-semibold text-white/72 shadow-[0_6px_14px_rgba(0,0,0,0.22)] transition hover:border-[#c9a96e]/30 hover:bg-[#2b261f] hover:text-[#f4dfb8]">
                                                            {{ __('Jump') }}
                                                        </button>
                                                        <button type="button"
                                                            x-show="canUnpinPinnedMessage(msg)" x-cloak
                                                            @click.stop="unpinPinnedMessage(msg)"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-white/8 bg-[#222127] text-white/45 shadow-[0_6px_14px_rgba(0,0,0,0.22)] transition hover:border-red-300/25 hover:bg-red-500/10 hover:text-red-200"
                                                            title="{{ __('Remove pinned message') }}"
                                                            aria-label="{{ __('Remove pinned message') }}">
                                                            <svg viewBox="0 0 16 16" class="h-3 w-3 fill-none stroke-current stroke-[2]"><path d="M3 3l10 10M13 3L3 13"></path></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div x-show="connectionLabel()" x-cloak class="border-b border-[#c9a96e]/10 bg-[#17120f] px-5 py-2 text-[0.72rem] text-[#e7cfaa]">
                            <span x-text="connectionLabel()"></span>
                        </div>

                        <div x-ref="messageScroller" @scroll="handleScroller()" class="community-scroll flex-1 overflow-y-auto px-5 py-5">
                            <div class="mx-auto max-w-5xl">
                                <div class="mb-5 border-b border-white/5 pb-5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[12px] bg-[#1d1a12] text-[#c9a96e]">
                                            <span class="font-display text-[1.3rem] leading-none">#</span>
                                        </div>
                                        <div class="min-w-0">
                                            <h1 class="text-[1.15rem] font-semibold leading-tight text-[#f0ede8]">
                                                {{ __('Welcome to') }} <span x-text="selectedChannel ? `#${selectedChannel.name}` : '#general'"></span>
                                            </h1>
                                            <p class="mt-0.5 text-[0.74rem] leading-5 text-white/30" x-text="selectedChannel ? `This is the beginning of the #${selectedChannel.name} channel for ${server.name}.` : 'This is the beginning of your community channel.'"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-5 flex justify-center" x-show="hasMoreMessages">
                                    <button type="button" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[0.68rem] uppercase tracking-[0.18em] text-white/55 transition hover:border-[#c9a96e]/25 hover:text-white" @click="loadOlderMessages()" :disabled="loadingOlder">
                                        <span x-text="loadingOlder ? 'Loading...' : 'Load older messages'"></span>
                                    </button>
                                </div>

                                <div x-show="channelNotice" x-cloak class="mb-3 rounded-lg border px-3 py-2 text-[0.76rem]" :class="channelNotice?.tone === 'success' ? 'border-emerald-400/15 bg-emerald-500/6 text-emerald-200' : channelNotice?.tone === 'warning' ? 'border-[#c9a96e]/15 bg-[#2a211b]/70 text-[#eed9b4]' : channelNotice?.tone === 'muted' ? 'border-white/6 bg-white/[0.025] text-white/50' : 'border-red-400/14 bg-red-500/6 text-red-200'">
                                    <span x-text="channelNotice?.message"></span>
                                </div>

                                <div x-show="searchNotice" x-cloak class="mb-3 rounded-lg border px-3 py-2 text-[0.76rem]" :class="searchNotice?.tone === 'muted' ? 'border-white/6 bg-white/[0.025] text-white/50' : 'border-red-400/14 bg-red-500/6 text-red-200'">
                                    <span x-text="searchNotice?.message"></span>
                                </div>


                                <div x-show="activeSearchQuery && searchResults.length" x-cloak class="mb-4 rounded-2xl border border-white/8 bg-white/[0.03] p-3">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <p class="text-[0.68rem] uppercase tracking-[0.18em] text-white/35" x-text="searchResultSummary()"></p>
                                        <button type="button" class="text-[0.65rem] uppercase tracking-[0.14em] text-[#c9a96e] transition hover:text-[#f4dfb8]" @click="clearSearch()">{{ __('Clear search') }}</button>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="result in searchResults.slice(0, 8)" :key="`search-result-${result.id}`">
                                            <button type="button" class="max-w-full rounded-full border border-white/10 bg-black/20 px-3 py-1.5 text-left text-[0.72rem] text-white/70 transition hover:border-[#c9a96e]/25 hover:text-white" @click="jumpToMessage(result.id)">
                                                <span class="font-semibold text-[#f0ede8]" x-text="result.label"></span>
                                                <span class="mx-1 text-white/20">-</span>
                                                <span class="truncate" x-text="result.excerpt"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div x-show="loadingMessages" x-cloak class="rounded-xl border border-white/6 bg-white/[0.025] px-4 py-3.5">
                                    <p class="text-[0.88rem] font-medium text-[#f4dfb8]">{{ __('Loading conversation...') }}</p>
                                    <p class="mt-0.5 text-[0.74rem] text-white/35">{{ __('Pulling the latest messages and channel activity now.') }}</p>
                                </div>

                                <div x-show="!loadingMessages && messages.length === 0" x-cloak class="rounded-xl border border-white/6 bg-white/[0.025] px-4 py-3.5">
                                    <p class="text-[0.88rem] font-medium text-[#f4dfb8]" x-text="messageStateCopy().title"></p>
                                    <p class="mt-0.5 max-w-2xl text-[0.74rem] leading-5 text-white/38" x-text="messageStateCopy().body"></p>
                                </div>

                                <div x-show="!loadingMessages && messages.length" x-cloak class="space-y-0.5" id="messages-list">
                                    <template x-for="(message, index) in messages" :key="message.id">
                                        <div :data-message-id="message.id" class="scroll-mt-24">
                                            <template x-if="showDateDivider(index)">
                                                <div class="my-4 flex items-center gap-3 px-1 select-none">
                                                    <div class="h-px flex-1 bg-white/[0.05]"></div>
                                                    <span class="text-[0.6rem] uppercase tracking-[0.18em] text-white/25" x-text="formatMessageDate(message.created_at)"></span>
                                                    <div class="h-px flex-1 bg-white/[0.05]"></div>
                                                </div>
                                            </template>

                                            <template x-if="shouldShowUnreadDivider(index)">
                                                <div class="my-4 flex items-center gap-3">
                                                    <div class="h-px flex-1 bg-[#c9a96e]/25"></div>
                                                    <span class="rounded-full border border-[#c9a96e]/25 bg-[#2a211b] px-3 py-1 text-[0.64rem] uppercase tracking-[0.16em] text-[#eed9b4]" x-text="unreadDividerLabel()"></span>
                                                    <div class="h-px flex-1 bg-[#c9a96e]/25"></div>
                                                </div>
                                            </template>

                                            {{-- System message (pin events) --}}
                                            <template x-if="message._isSystem">
                                                <div class="flex items-center gap-2 px-3 py-1 my-0.5 select-none">
                                                    <svg viewBox="0 0 16 16" class="h-3 w-3 shrink-0 fill-none stroke-current stroke-[1.5] text-white/22" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M8 2l2 2-3.5 3.5 1.5 1.5-4.5 4.5H2v-1.5l4.5-4.5L5 6.5z"/>
                                                        <path d="M1 15l3-3"/>
                                                    </svg>
                                                    <span class="text-[0.74rem] text-white/32 leading-snug">
                                                        <span class="font-medium text-white/48" x-text="message._actorName"></span>
                                                        <template x-if="message._systemType === 'pin'">
                                                            <span> {{ __('pinned a message to this channel.') }}
                                                                <button type="button" @click="pinnedPanelOpen = true" class="text-white/50 underline underline-offset-2 decoration-white/25 transition hover:text-white/75 hover:decoration-white/50">{{ __('See all pinned messages.') }}</button>
                                                            </span>
                                                        </template>
                                                    </span>
                                                </div>
                                            </template>

                                            {{-- Normal message --}}
                                            <template x-if="!message._isSystem">
                                            <article class="group rounded-lg px-2 transition duration-500" :class="`${isGrouped(index) ? 'pt-0.5 pb-0' : 'pt-3 pb-0 mt-2'} ${isHighlightedMessage(message.id) ? 'bg-[#c9a96e]/12 ring-1 ring-[#c9a96e]/30 shadow-[0_0_0_1px_rgba(201,169,110,0.12)]' : 'hover:bg-white/[0.02]'}`">
                                                {{-- Discord-style reply indicator --}}
                                                <template x-if="message.reply_to">
                                                    <div class="mb-0.5 flex items-end gap-3">
                                                        <div class="w-10 shrink-0 flex justify-end">
                                                            <div class="h-[10px] w-[18px] border-t border-l border-white/[0.12] rounded-tl-[5px]"></div>
                                                        </div>
                                                        <button type="button" @click="jumpToMessage(message.reply_to.id)" class="flex min-w-0 flex-1 items-center gap-1.5 pb-0.5 text-left text-[0.72rem] text-white/38 transition-colors hover:text-white/60 truncate">
                                                            <span class="shrink-0 font-semibold text-white/55" x-text="message.reply_to.user_name"></span>
                                                            <span class="truncate" x-text="message.reply_to.message || 'Attachment'"></span>
                                                        </button>
                                                    </div>
                                                </template>
                                                <div class="flex items-start gap-3">
                                                    <div class="w-10 shrink-0">
                                                        <template x-if="!isGrouped(index)">
                                                            <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border border-white/10 font-display text-[0.66rem] font-bold text-white" :style="`background: radial-gradient(circle at top, ${message.user.accent}, #151014 70%)`">
                                                                <template x-if="message.user.profile_photo_url">
                                                                    <img :src="message.user.profile_photo_url" alt="" class="h-full w-full object-cover">
                                                                </template>
                                                                <template x-if="!message.user.profile_photo_url">
                                                                    <span x-text="message.user.initials"></span>
                                                                </template>
                                                            </div>
                                                        </template>
                                                        <template x-if="isGrouped(index)">
                                                            <div class="flex h-5 items-center justify-end pr-0.5 opacity-0 transition-opacity duration-100 group-hover:opacity-100">
                                                                <span class="text-[0.58rem] tabular-nums leading-none text-white/18" x-text="formatMessageTime(message.created_at)"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <template x-if="!isGrouped(index)">
                                                            <div class="mb-0.5 flex items-baseline gap-2">
                                                                <span class="text-[0.83rem] font-semibold text-[#f0ede8]" x-text="message.user.name"></span>
                                                                <span class="text-[0.62rem] text-white/20" :title="formatFullTimestamp(message.created_at)" x-text="formatMessageTime(message.created_at)"></span>
                                                            </div>
                                                        </template>

                                                        <div class="relative">
                                                            <div class="absolute right-0 top-0 hidden -translate-y-8 items-center gap-1 rounded-xl border border-white/8 bg-[#151317] p-1 shadow-[0_12px_28px_rgba(0,0,0,0.34)] group-hover:flex">
                                                                <button type="button" class="rounded-lg px-2 py-1 text-sm text-white/65 transition hover:bg-white/5 hover:text-white" @click="quickReact(message, '❤️')">❤</button>
                                                                <button type="button" class="rounded-lg px-2 py-1 text-sm text-white/65 transition hover:bg-white/5 hover:text-white" @click="quickReact(message, '🔥')">🔥</button>
                                                                <button type="button" class="rounded-lg px-2 py-1 text-sm text-white/65 transition hover:bg-white/5 hover:text-white" @click="quickReact(message, '👍')">👍</button>
                                                                <button type="button" class="rounded-lg px-2 py-1 text-[0.62rem] uppercase tracking-[0.16em] text-white/55 transition hover:bg-white/5 hover:text-white" @click="reply(message)">{{ __('Reply') }}</button>
                                                                <button type="button" x-show="message.can_pin" x-cloak class="rounded-lg px-2 py-1 text-[0.62rem] uppercase tracking-[0.16em] text-[#c9a96e] transition hover:bg-white/5" @click="togglePin(message)">{{ __('Pin') }}</button>
                                                                <button type="button" x-show="message.can_delete" x-cloak class="rounded-lg px-2 py-1 text-[0.62rem] uppercase tracking-[0.16em] text-red-300 transition hover:bg-white/5" @click="deleteMessage(message)">{{ __('Delete') }}</button>
                                                            </div>

                                                            <div x-show="message.message" x-cloak class="text-[0.82rem] leading-relaxed text-white/80" x-html="renderMessage(message.message)"></div>

                                                            <template x-if="message.attachment">
                                                                <div class="mt-3 overflow-hidden rounded-2xl border border-white/8 bg-[#121014]">
                                                                    <template x-if="message.attachment.is_image">
                                                                        <img :src="message.attachment.preview_url || message.attachment.url" :alt="message.attachment.name" loading="lazy" decoding="async" class="max-h-[320px] w-full object-cover">
                                                                    </template>
                                                                    <template x-if="!message.attachment.is_image">
                                                                        <a :href="message.attachment.url" target="_blank" class="flex items-center justify-between gap-4 px-4 py-4 text-sm text-white/78 transition hover:bg-white/[0.04]">
                                                                            <span class="truncate" x-text="message.attachment.name"></span>
                                                                            <span class="shrink-0 text-white/32">{{ __('Open') }}</span>
                                                                        </a>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>

                                                        <div class="mt-2 flex flex-wrap items-center gap-2" x-show="message.reactions?.length">
                                                            <template x-for="reaction in message.reactions" :key="`${message.id}-${reaction.emoji}`">
                                                                <button type="button" class="rounded-full border px-3 py-1 text-[0.74rem] transition" :class="reaction.reacted ? 'border-[#c9a96e]/35 bg-[#2a221d] text-[#f4dfb8]' : 'border-white/8 bg-white/[0.03] text-white/55 hover:text-white'" @click="quickReact(message, reaction.emoji)">
                                                                    <span x-text="`${reaction.emoji} ${reaction.count}`"></span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </article>
                                            </template>{{-- end x-if="!message._isSystem" --}}
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div x-show="!(selectedChannel?.is_locked && !features.can_moderate_messages)" x-cloak class="border-t border-white/5 px-4 py-3">
                            <div class="mx-auto max-w-5xl">
                                <div x-show="replyTo" x-cloak class="mb-2 flex items-center justify-between rounded-lg border border-[#c9a96e]/14 bg-[#1b1714] px-3 py-2 text-[0.76rem] text-[#ead2ab]">
                                    <div class="min-w-0 flex items-center gap-1.5 truncate">
                                        <span class="shrink-0 font-medium">{{ __('Replying to') }}</span>
                                        <span class="truncate text-white/65" x-text="replyTo?.user?.name"></span>
                                        <span class="shrink-0 text-white/20">—</span>
                                        <span class="truncate text-white/42" x-text="replyTo?.message || replyTo?.attachment?.name"></span>
                                    </div>
                                    <button type="button" class="ml-3 shrink-0 text-white/35 transition hover:text-white" @click="replyTo = null">
                                        <svg viewBox="0 0 16 16" class="h-3 w-3 stroke-current fill-none stroke-[2]"><path d="M3 3l10 10M13 3L3 13"></path></svg>
                                    </button>
                                </div>

                                <div x-show="attachmentPreview || attachmentName" x-cloak class="mb-2 flex items-center justify-between rounded-lg border border-white/8 bg-white/[0.03] px-3 py-2 text-[0.76rem] text-white/65">
                                    <div class="flex min-w-0 items-center gap-2.5">
                                        <template x-if="attachmentPreview">
                                            <img :src="attachmentPreview" alt="" class="h-9 w-9 rounded-lg object-cover">
                                        </template>
                                        <span class="truncate" x-text="attachmentName"></span>
                                    </div>
                                    <button type="button" class="ml-3 shrink-0 text-white/35 transition hover:text-white" @click="clearAttachment()">
                                        <svg viewBox="0 0 16 16" class="h-3 w-3 stroke-current fill-none stroke-[2]"><path d="M3 3l10 10M13 3L3 13"></path></svg>
                                    </button>
                                </div>

                                <div x-show="selectedChannel?.is_private" x-cloak class="mb-2 flex items-center gap-2 text-[0.72rem] text-[#c9a96e]/65">
                                    <svg viewBox="0 0 16 16" class="h-3 w-3 shrink-0 fill-none stroke-current stroke-[1.8]"><rect x="3" y="7" width="10" height="7" rx="1.5"></rect><path d="M5 7V5a3 3 0 0 1 6 0v2"></path></svg>
                                    <span>{{ __('Private channel — visible to approved members only.') }}</span>
                                </div>

                                <div x-show="selectedChannel?.is_locked && !features.can_moderate_messages" x-cloak class="mb-2 flex items-center gap-2 text-[0.72rem] text-white/38">
                                    <svg viewBox="0 0 16 16" class="h-3 w-3 shrink-0 fill-none stroke-current stroke-[1.8]"><rect x="3" y="7" width="10" height="7" rx="1.5"></rect><path d="M5 7V5a3 3 0 0 1 6 0v2"></path></svg>
                                    <span>{{ __('This channel is locked — read only.') }}</span>
                                </div>

                                <div x-show="composerNotice" x-cloak class="mb-3 rounded-xl border px-4 py-3 text-sm" :class="composerNotice?.tone === 'warning' ? 'border-[#c9a96e]/18 bg-[#2a211b] text-[#eed9b4]' : 'border-red-400/16 bg-red-500/8 text-red-100'">
                                    <span x-text="composerNotice?.message"></span>
                                </div>

                                <div x-show="members?.typing?.length" x-cloak class="mb-3 flex items-center gap-2 pl-2 text-[0.78rem] text-white/45">
                                    <span class="inline-flex items-center gap-1 text-[#c9a96e]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#c9a96e] animate-pulse"></span>
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#c9a96e]/70 animate-pulse" style="animation-delay: 120ms"></span>
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#c9a96e]/50 animate-pulse" style="animation-delay: 240ms"></span>
                                    </span>
                                    <span x-text="typingIndicatorText()"></span>
                                </div>

                                <form
                                    @submit.prevent="sendMessage()"
                                    @dragover="handleComposerDragOver($event)"
                                    @dragleave="handleComposerDragLeave($event)"
                                    @drop="handleComposerDrop($event)"
                                    class="rounded-xl border border-white/8 bg-white/[0.04] px-3 pt-2.5 pb-2 transition"
                                    :class="dragActive ? 'border-[#c9a96e]/30 bg-[#1a1511]' : ''"
                                >
                                    <div x-show="dragActive" x-cloak class="mb-2 rounded-lg border border-dashed border-[#c9a96e]/30 bg-[#2a211b]/60 px-3 py-2 text-[0.74rem] text-[#eed9b4]">
                                        {{ __('Drop a file here to attach it.') }}
                                    </div>
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#c9a96e]/20 bg-[linear-gradient(135deg,rgba(201,169,110,0.22),rgba(201,169,110,0.06))] font-display text-[0.48rem] text-[#e8c88a]">
                                            <template x-if="user.profile_photo_url">
                                                <img :src="user.profile_photo_url" alt="" class="h-full w-full object-cover">
                                            </template>
                                            <template x-if="!user.profile_photo_url">
                                                <span x-text="user.initials"></span>
                                            </template>
                                        </div>
                                        <textarea
                                            x-ref="composerInput"
                                            x-model="draft"
                                            rows="1"
                                            class="h-7 max-h-32 min-h-0 flex-1 resize-none border-0 bg-transparent py-0.5 text-[0.82rem] text-white placeholder:text-white/22 focus:outline-none focus:ring-0"
                                            :placeholder="composerPlaceholder()"
                                            :disabled="!canUseComposer()"
                                            @keydown.enter.prevent="handleComposerEnter($event)"
                                            @input="handleDraftInput()"
                                        ></textarea>
                                        <button type="button" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-white/35 transition hover:bg-white/5 hover:text-white/70 disabled:cursor-not-allowed disabled:opacity-40" @click="$refs.attachmentInput.click()" :disabled="!canUseComposer()" aria-label="{{ __('Attach file') }}">
                                            <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 stroke-current fill-none stroke-[1.8]">
                                                <path d="M6.5 8.5 10.8 4.2a2.2 2.2 0 1 1 3.1 3.1L7.8 13.4A4 4 0 1 1 2.1 7.7l6.1-6.1"></path>
                                            </svg>
                                        </button>
                                        <button type="submit" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-white/18 transition" :class="sendingMessage || (!draft.trim() && !attachmentFile) || !canUseComposer() ? 'opacity-30' : 'bg-[linear-gradient(135deg,#6c5431,#d4af6c)] text-[#07070c] shadow-[0_3px_10px_rgba(255,140,0,0.25)]'" :disabled="sendingMessage || (!draft.trim() && !attachmentFile) || !canUseComposer()">
                                            <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 stroke-current fill-none stroke-[1.8]"><path d="M14 2 1 8l5 3 2 5 6-14zM6 11l3-3"></path></svg>
                                        </button>
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-1 pl-[34px]">
                                        <button type="button" class="rounded-full border border-white/8 px-2 py-0.5 text-[0.6rem] text-white/40 transition hover:border-[#c9a96e]/20 hover:text-white/70" @click="insertEmoji('❤️')">❤</button>
                                        <button type="button" class="rounded-full border border-white/8 px-2 py-0.5 text-[0.6rem] text-white/40 transition hover:border-[#c9a96e]/20 hover:text-white/70" @click="insertEmoji('🔥')">🔥</button>
                                        <button type="button" class="rounded-full border border-white/8 px-2 py-0.5 text-[0.6rem] text-white/40 transition hover:border-[#c9a96e]/20 hover:text-white/70" @click="insertEmoji('👍')">👍</button>
                                        <span x-show="selectedChannel?.slowmode_seconds" x-cloak class="ml-auto text-[0.6rem] uppercase tracking-[0.1em] text-white/22" x-text="`${selectedChannel.slowmode_seconds}s slowmode`"></span>
                                    </div>
                                    <input x-ref="attachmentInput" type="file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip" class="hidden" @change="handleAttachment($event)">
                                </form>
                            </div>
                        </div>
                    </section>

                    <aside x-show="membersOpen || membersDrawerOpen" class="fixed inset-y-0 right-0 z-50 w-[86vw] max-w-[220px] translate-x-full border-l border-white/5 bg-[#100c0f] p-3 transition-transform xl:static xl:z-auto xl:w-[220px] xl:translate-x-0" :class="membersDrawerOpen ? 'translate-x-0' : 'translate-x-full xl:translate-x-0'">
                        <div class="flex items-center justify-between">
                            <div class="text-[0.58rem] uppercase tracking-[0.14em] text-white/25">{{ __('Online') }} - <span x-text="members.online.length"></span></div>
                            <button type="button" class="rounded-lg p-2 text-white/40 transition hover:bg-white/5 hover:text-white xl:hidden" @click="membersDrawerOpen = false">x</button>
                        </div>
                        <div class="mt-3 space-y-2">
                            <template x-for="member in members.online" :key="`online-${member.id}`">
                                <div class="group flex items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-white/5">
                                    <div class="relative shrink-0">
                                        <div class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full border border-white/8 font-display text-[0.58rem] font-bold text-white" :style="`background: radial-gradient(circle at top, ${member.accent}, #151014 70%)`">
                                            <template x-if="member.profile_photo_url">
                                                <img :src="member.profile_photo_url" alt="" class="h-full w-full object-cover">
                                            </template>
                                            <template x-if="!member.profile_photo_url">
                                                <span x-text="member.initials"></span>
                                            </template>
                                        </div>
                                        <div class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-[#0b0b15] bg-[#4ade80]"></div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-[0.78rem] font-semibold text-[#f0ede8]"><span x-text="member.name"></span><span x-show="member.is_self" x-cloak class="text-[0.6rem] text-white/35"> ({{ __('You') }})</span></div>
                                        <div class="text-[0.56rem] uppercase tracking-[0.12em] text-white/24" x-text="member.role"></div>
                                    </div>
                                    <button type="button" x-show="memberCanBeTimedOut(member)" x-cloak class="hidden rounded-full border border-[#c9a96e]/20 px-2.5 py-1 text-[0.52rem] uppercase tracking-[0.12em] text-[#e7cfaa] transition hover:border-[#c9a96e]/35 hover:text-[#f4dfb8] group-hover:block" @click="timeoutMember(member)">{{ __('Timeout') }}</button>
                                </div>
                            </template>
                        </div>

                        <div class="mt-5 text-[0.58rem] uppercase tracking-[0.14em] text-white/25">{{ __('Offline') }} - <span x-text="members.offline_count ?? members.offline.length"></span></div>
                        <div class="mt-3 space-y-2">
                            <template x-for="member in members.offline" :key="`offline-${member.id}`">
                                <div class="group flex items-center gap-3 rounded-lg px-2 py-2 opacity-50 transition hover:bg-white/5">
                                    <div class="relative shrink-0">
                                        <div class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full border border-white/8 bg-white/[0.03] font-display text-[0.58rem] font-bold text-white/40">
                                            <template x-if="member.profile_photo_url">
                                                <img :src="member.profile_photo_url" alt="" class="h-full w-full object-cover">
                                            </template>
                                            <template x-if="!member.profile_photo_url">
                                                <span x-text="member.initials"></span>
                                            </template>
                                        </div>
                                        <div class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-[#0b0b15] bg-slate-500"></div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-[0.78rem] text-white/70" x-text="member.name"></div>
                                        <div class="text-[0.56rem] uppercase tracking-[0.12em] text-white/24" x-text="member.role"></div>
                                    </div>
                                    <button type="button" x-show="memberCanBeTimedOut(member)" x-cloak class="hidden rounded-full border border-[#c9a96e]/20 px-2.5 py-1 text-[0.52rem] uppercase tracking-[0.12em] text-[#e7cfaa] transition hover:border-[#c9a96e]/35 hover:text-[#f4dfb8] group-hover:block" @click="timeoutMember(member)">{{ __('Timeout') }}</button>
                                </div>
                            </template>
                        </div>
                    </aside>
                </div>
            </main>

            <div x-show="channelModalOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-black/70 px-4 py-6" @click.self="closeChannelModal()">
                <div class="w-full max-w-md rounded-2xl border border-white/8 bg-[linear-gradient(180deg,rgba(17,15,18,0.99),rgba(10,10,12,0.99))] p-5 shadow-[0_24px_60px_rgba(0,0,0,0.45)]">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[0.6rem] uppercase tracking-[0.2em] text-white/30">{{ __('Admin Controls') }}</p>
                            <h3 class="mt-1 text-[1.1rem] font-semibold text-[#f4dfb8]" x-text="channelForm.id ? 'Edit channel' : 'Create channel'"></h3>
                        </div>
                        <button type="button" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-white/[0.04] text-white/40 transition hover:text-white" @click="closeChannelModal()">
                            <svg viewBox="0 0 16 16" class="h-3 w-3 fill-none stroke-current stroke-[2]"><path d="M2 2l12 12M14 2L2 14"></path></svg>
                        </button>
                    </div>

                    <form class="mt-4 space-y-3" @submit.prevent="submitChannelForm()">
                        <div>
                            <label class="mb-1.5 block text-[0.6rem] uppercase tracking-[0.16em] text-white/35">{{ __('Channel name') }}</label>
                            <input x-model="channelForm.name" type="text" class="w-full rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-[0.82rem] text-white placeholder:text-white/22 focus:border-[#d8ae64]/35 focus:outline-none focus:ring-0" placeholder="general">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[0.6rem] uppercase tracking-[0.16em] text-white/35">{{ __('Description') }}</label>
                            <textarea x-model="channelForm.description" rows="2" class="w-full resize-none rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-[0.82rem] text-white placeholder:text-white/22 focus:border-[#d8ae64]/35 focus:outline-none focus:ring-0" placeholder="{{ __('What is this channel for?') }}"></textarea>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-[0.6rem] uppercase tracking-[0.16em] text-white/35">{{ __('Category') }}</label>
                                <input x-model="channelForm.category" type="text" class="w-full rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-[0.82rem] text-white placeholder:text-white/22 focus:border-[#d8ae64]/35 focus:outline-none focus:ring-0" placeholder="Channels">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[0.6rem] uppercase tracking-[0.16em] text-white/35">{{ __('Slowmode seconds') }}</label>
                                <input x-model.number="channelForm.slowmode_seconds" type="number" min="0" max="300" class="w-full rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-[0.82rem] text-white focus:border-[#d8ae64]/35 focus:outline-none focus:ring-0">
                            </div>
                        </div>
                        <div class="grid gap-2 md:grid-cols-2">
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-white/8 bg-white/[0.03] px-3 py-2 text-[0.78rem] text-white/65 transition hover:bg-white/[0.05]">
                                <input x-model="channelForm.is_private" type="checkbox" class="rounded border-white/20 bg-transparent text-[#d8ae64] focus:ring-[#d8ae64]/40">
                                <span>{{ __('Private channel') }}</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-white/8 bg-white/[0.03] px-3 py-2 text-[0.78rem] text-white/65 transition hover:bg-white/[0.05]">
                                <input x-model="channelForm.is_locked" type="checkbox" class="rounded border-white/20 bg-transparent text-[#d8ae64] focus:ring-[#d8ae64]/40">
                                <span>{{ __('Lock channel') }}</span>
                            </label>
                        </div>

                        <div x-show="features.can_manage_channels" x-cloak class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-[0.6rem] uppercase tracking-[0.16em] text-white/35">{{ __('Access mode') }}</label>
                                <select x-model="channelForm.access_mode" class="w-full rounded-xl border border-white/10 bg-[#0e0c10] px-3 py-2 text-[0.82rem] text-white focus:border-[#d8ae64]/35 focus:outline-none focus:ring-0">
                                    <template x-for="option in channelAccessOptions.access_modes" :key="option.value">
                                        <option :value="option.value" x-text="option.label" class="bg-[#140f12]"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[0.6rem] uppercase tracking-[0.16em] text-white/35">{{ __('Denied behavior') }}</label>
                                <select x-model="channelForm.denied_behavior" class="w-full rounded-xl border border-white/10 bg-[#0e0c10] px-3 py-2 text-[0.82rem] text-white focus:border-[#d8ae64]/35 focus:outline-none focus:ring-0">
                                    <template x-for="option in channelAccessOptions.denied_behaviors" :key="option.value">
                                        <option :value="option.value" x-text="option.label" class="bg-[#140f12]"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div x-show="features.can_manage_channels && channelForm.access_mode === 'roles'" x-cloak>
                            <label class="mb-1.5 block text-[0.6rem] uppercase tracking-[0.16em] text-white/35">{{ __('Allowed roles') }}</label>
                            <div class="grid gap-2 md:grid-cols-3">
                                <template x-for="role in channelAccessOptions.roles" :key="role.value">
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-white/8 bg-white/[0.03] px-3 py-2 text-[0.78rem] text-white/65 transition hover:bg-white/[0.05]">
                                        <input x-model="channelForm.allowed_roles" :value="role.value" type="checkbox" class="rounded border-white/20 bg-transparent text-[#d8ae64] focus:ring-[#d8ae64]/40">
                                        <span x-text="role.label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div x-show="features.can_manage_channels && channelForm.access_mode === 'invite'" x-cloak>
                            <label class="mb-1.5 block text-[0.6rem] uppercase tracking-[0.16em] text-white/35">{{ __('Invited members') }}</label>
                            <div class="max-h-36 space-y-1.5 overflow-y-auto rounded-xl border border-white/8 bg-white/[0.03] p-2">
                                <template x-for="member in memberDirectory" :key="member.id">
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-white/6 bg-black/15 px-3 py-1.5 text-[0.78rem] text-white/65 transition hover:bg-white/[0.05]">
                                        <input x-model="channelForm.invited_user_ids" :value="member.id" type="checkbox" class="rounded border-white/20 bg-transparent text-[#d8ae64] focus:ring-[#d8ae64]/40">
                                        <span class="min-w-0 flex-1 truncate" x-text="member.name"></span>
                                        <span class="text-[0.55rem] uppercase tracking-[0.1em] text-white/24" x-text="member.role"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div x-show="!features.can_manage_channels" x-cloak class="rounded-xl border border-white/8 bg-white/[0.03] px-3 py-2.5 text-[0.75rem] text-white/50">
                            {{ __('Moderators can update channel details here, but only admins can change channel visibility and invite access rules.') }}
                        </div>

                        <div class="mt-1 flex flex-wrap items-center justify-between gap-2 border-t border-white/[0.06] pt-3">
                            <div class="flex items-center gap-1.5">
                                {{-- Archive — amber/warning (action is reversible) --}}
                                <button type="button" x-show="channelForm.id" x-cloak
                                    class="flex items-center gap-1.5 rounded-xl border border-amber-500/18 bg-amber-500/[0.07] px-3 py-1.5 text-[0.67rem] font-semibold uppercase tracking-[0.14em] text-amber-300/70 transition hover:border-amber-400/30 hover:bg-amber-500/14 hover:text-amber-200"
                                    @click="archiveChannelFromModal()">
                                    <svg viewBox="0 0 16 16" class="h-3 w-3 shrink-0 fill-none stroke-current stroke-[1.6]" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="3" width="14" height="3" rx="0.5"/>
                                        <path d="M2 6v7a1 1 0 001 1h10a1 1 0 001-1V6"/>
                                        <path d="M6.5 9.5h3"/>
                                    </svg>
                                    {{ __('Archive') }}
                                </button>
                                {{-- Delete — red/danger (action is permanent) --}}
                                <button type="button" x-show="features.can_manage_channels && channelForm.id" x-cloak
                                    class="flex items-center gap-1.5 rounded-xl border border-red-500/20 bg-red-500/[0.07] px-3 py-1.5 text-[0.67rem] font-semibold uppercase tracking-[0.14em] text-red-300/70 transition hover:border-red-400/32 hover:bg-red-500/14 hover:text-red-200"
                                    @click="deleteChannelFromModal()">
                                    <svg viewBox="0 0 16 16" class="h-3 w-3 shrink-0 fill-none stroke-current stroke-[1.6]" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 4h12"/>
                                        <path d="M5 4V2.5a.5.5 0 01.5-.5h5a.5.5 0 01.5.5V4"/>
                                        <rect x="3" y="4" width="10" height="10" rx="1"/>
                                        <path d="M6.5 7v4M9.5 7v4"/>
                                    </svg>
                                    {{ __('Delete') }}
                                </button>
                            </div>
                            <div class="ml-auto flex items-center gap-2">
                                <button type="button" class="rounded-xl border border-white/[0.08] px-4 py-1.5 text-[0.67rem] font-semibold uppercase tracking-[0.14em] text-white/40 transition hover:border-white/14 hover:text-white/75" @click="closeChannelModal()">{{ __('Cancel') }}</button>
                                <button type="submit" class="flex items-center gap-1.5 rounded-xl bg-[linear-gradient(135deg,#6c5431,#d4af6c)] px-4 py-1.5 text-[0.67rem] font-semibold uppercase tracking-[0.14em] text-[#17120f] shadow-[0_3px_12px_rgba(201,169,110,0.2)] transition hover:brightness-110">
                                    <svg viewBox="0 0 16 16" class="h-3 w-3 shrink-0 fill-none stroke-current stroke-[2]" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 8l4 4 8-8"/>
                                    </svg>
                                    <span x-text="channelForm.id ? '{{ __('Save changes') }}' : '{{ __('Create channel') }}'"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        {{-- Custom confirm dialog (replaces window.confirm) --}}
        <div x-show="confirmDialog.open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/65 px-4" @keydown.escape.window="confirmDialogCancel()">
            <div class="w-full max-w-sm rounded-2xl border border-white/8 bg-[linear-gradient(180deg,rgba(20,17,21,0.99),rgba(12,11,14,0.99))] p-5 shadow-[0_24px_60px_rgba(0,0,0,0.55)]" @click.stop>
                <h3 class="text-[0.95rem] font-semibold text-[#f0ede8]" x-text="confirmDialog.title"></h3>
                <p class="mt-1.5 text-[0.78rem] leading-[1.5] text-white/48" x-text="confirmDialog.message"></p>
                <div class="mt-5 flex items-center justify-end gap-2.5">
                    <button type="button" class="rounded-xl border border-white/10 bg-white/[0.04] px-4 py-1.5 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-white/50 transition hover:text-white" @click="confirmDialogCancel()">{{ __('Cancel') }}</button>
                    <button type="button" class="rounded-xl px-4 py-1.5 text-[0.68rem] font-semibold uppercase tracking-[0.16em] transition" :class="confirmDialog.confirmTone === 'warning' ? 'border border-amber-400/22 bg-amber-500/12 text-amber-200 hover:bg-amber-500/22' : 'border border-red-400/20 bg-red-500/12 text-red-200 hover:bg-red-500/22'" @click="confirmDialogAccept()" x-text="confirmDialog.confirmText"></button>
                </div>
            </div>
        </div>

        {{-- Custom timeout dialog (replaces window.prompt for member timeouts) --}}
        <div x-show="timeoutDialog.open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/65 px-4" @keydown.escape.window="closeTimeoutDialog()">
            <div class="w-full max-w-sm rounded-2xl border border-white/8 bg-[linear-gradient(180deg,rgba(20,17,21,0.99),rgba(12,11,14,0.99))] p-5 shadow-[0_24px_60px_rgba(0,0,0,0.55)]" @click.stop>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[0.6rem] uppercase tracking-[0.18em] text-white/28">{{ __('Moderation') }}</p>
                        <h3 class="mt-1 text-[1rem] font-semibold text-[#f0ede8]">{{ __('Timeout member') }}</h3>
                        <p class="mt-0.5 text-[0.76rem] text-white/42">{{ __('Silence') }} <span class="font-semibold text-white/65" x-text="timeoutDialog.member?.name"></span> {{ __('from posting.') }}</p>
                    </div>
                    <button type="button" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-white/[0.04] text-white/35 transition hover:text-white" @click="closeTimeoutDialog()">
                        <svg viewBox="0 0 16 16" class="h-3 w-3 fill-none stroke-current stroke-[2]"><path d="M2 2l12 12M14 2L2 14"></path></svg>
                    </button>
                </div>
                <div class="mt-4 space-y-3">
                    <div>
                        <label class="mb-1.5 block text-[0.6rem] uppercase tracking-[0.16em] text-white/35">{{ __('Duration (minutes)') }}</label>
                        <input x-model="timeoutDialog.duration" type="number" min="1" max="10080" class="w-full rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-[0.82rem] text-white focus:border-[#d8ae64]/35 focus:outline-none focus:ring-0">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[0.6rem] uppercase tracking-[0.16em] text-white/35">{{ __('Reason (optional)') }}</label>
                        <input x-model="timeoutDialog.reason" type="text" class="w-full rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-[0.82rem] text-white placeholder:text-white/22 focus:border-[#d8ae64]/35 focus:outline-none focus:ring-0" placeholder="{{ __('e.g. Spam, harassment') }}">
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end gap-2.5">
                    <button type="button" class="rounded-xl border border-white/10 bg-white/[0.04] px-4 py-1.5 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-white/50 transition hover:text-white" @click="closeTimeoutDialog()">{{ __('Cancel') }}</button>
                    <button type="button" class="rounded-xl border border-amber-400/22 bg-amber-500/12 px-4 py-1.5 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-amber-200 transition hover:bg-amber-500/22" @click="submitTimeoutDialog()">{{ __('Apply timeout') }}</button>
                </div>
            </div>
        </div>

        </div>
    </body>
</html>
