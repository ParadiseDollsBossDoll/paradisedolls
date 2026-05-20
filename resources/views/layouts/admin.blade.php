@php
    $user = auth()->user();
    $initials = $user->initials();
    $profilePhotoUrl = $user->profilePhotoUrl();

    [$pendingLayoutApplications, $pendingLayoutVerification, $referralActionCount] =
        \Illuminate\Support\Facades\Cache::remember('admin_sidebar_counts', 60, fn () => [
            \App\Models\ModelApplication::query()
                ->where('status', \App\Models\ModelApplication::STATUS_PENDING)
                ->count(),
            \App\Models\ModelProfile::query()
                ->where('verification_status', \App\Models\ModelProfile::VERIFICATION_SUBMITTED)
                ->count(),
            \App\Models\ModelReferral::query()
                ->where(function ($query) {
                    $query
                        ->where(function ($leadQuery) {
                            $leadQuery
                                ->where('status', \App\Models\ModelReferral::STATUS_REFERRED)
                                ->whereNull('model_application_id');
                        })
                        ->orWhere('reward_status', \App\Models\ModelReferral::REWARD_ELIGIBLE);
                })
                ->count(),
        ]);

    $links = [
        [
            'route'  => 'admin.dashboard',
            'label'  => __('Overview'),
            'active' => request()->routeIs('admin.dashboard'),
            'icon'   => 'overview',
            'count'  => 0,
        ],
        [
            'route'  => 'admin.applications.index',
            'label'  => __('Applications'),
            'active' => request()->routeIs('admin.applications.*'),
            'icon'   => 'applications',
            'count'  => $pendingLayoutApplications,
        ],
        [
            'route'  => 'admin.referrals.index',
            'label'  => __('Referrals'),
            'active' => request()->routeIs('admin.referrals.*'),
            'icon'   => 'referrals',
            'count'  => $referralActionCount,
        ],
        [
            'route'  => 'admin.onboarding.index',
            'label'  => __('Onboarding'),
            'active' => request()->routeIs('admin.onboarding.*'),
            'icon'   => 'onboarding',
            'count'  => $pendingLayoutVerification,
        ],
        [
            'route'  => 'admin.models.progress',
            'label'  => __('Members'),
            'active' => request()->routeIs('admin.models.progress'),
            'icon'   => 'members',
            'count'  => 0,
        ],
        [
            'route'  => 'admin.courses.index',
            'label'  => __('Courses'),
            'active' => request()->routeIs('admin.courses.*'),
            'icon'   => 'courses',
            'count'  => 0,
        ],
        [
            'route'  => 'admin.testimonials.index',
            'label'  => __('Testimonials'),
            'active' => request()->routeIs('admin.testimonials.*'),
            'icon'   => 'stories',
            'count'  => 0,
        ],
        [
            'route'  => 'community.show',
            'label'  => __('Community Chat'),
            'active' => request()->routeIs('community.*'),
            'icon'   => 'community',
            'count'  => 0,
        ],
    ];

    $currentLabel = request()->routeIs('notifications.*')
        ? __('Notifications')
        : (request()->routeIs('profile.*')
        ? __('Profile')
        : (collect($links)->firstWhere('active', true)['label'] ?? __('Overview')));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <script>(function(){function applyVars(s){var h=document.documentElement;h.classList.toggle('light-mode',s.mode==='light');if(s.primary){h.style.setProperty('--pd-primary',s.primary);h.style.setProperty('--pd-gold',s.primary);}if(s.primaryLight){h.style.setProperty('--pd-primary-hover',s.primaryLight);h.style.setProperty('--pd-gold-light',s.primaryLight);}}try{var s=JSON.parse(localStorage.getItem('pd-theme-v2')||'null');if(!s){var old=localStorage.getItem('pd-theme');s={mode:old==='light'?'light':'dark',primary:'#EEB4C3',primaryLight:'#F3C3CF'};}applyVars(s);}catch(e){applyVars({mode:'light',primary:'#EEB4C3',primaryLight:'#F3C3CF'});}window.pdApplyTheme=function(s){applyVars(s);localStorage.setItem('pd-theme-v2',JSON.stringify(s));};window.pdToggleTheme=function(){try{var s=JSON.parse(localStorage.getItem('pd-theme-v2')||'{}');s.mode=document.documentElement.classList.toggle('light-mode')?'light':'dark';localStorage.setItem('pd-theme-v2',JSON.stringify(s));}catch(e){};};}());</script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased pd-dark-surface min-h-screen" x-data="{ sidebarOpen: false }">
        <div class="flex min-h-screen">

            {{-- Sidebar --}}
            <aside class="elysian-sidebar" :class="sidebarOpen ? 'is-open' : ''">

                <div class="elysian-brand">
                    <div>
                        <div class="elysian-brand-title">&#10022; PARADISEDOLLZ &#10022;</div>
                        <div class="elysian-brand-sub">{{ __('Admin Panel') }}</div>
                    </div>
                </div>

                <a href="{{ route('profile.edit') }}" class="elysian-side-profile group" title="{{ __('Edit your profile') }}" @click="sidebarOpen = false">
                    <div class="elysian-side-profile-inner">
                        <div class="elysian-side-profile-row">
                            <div class="elysian-avatar-wrap">
                                <div class="elysian-avatar">
                                    <span>{{ $initials }}</span>
                                    @if ($profilePhotoUrl)
                                        <img src="{{ $profilePhotoUrl }}" alt="{{ __('Profile photo') }}" onerror="this.remove()">
                                    @endif
                                </div>
                                <div class="elysian-online-dot"></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="elysian-side-name">{{ $user->name }}</div>
                                <div class="elysian-side-sub flex items-center justify-between gap-1">
                                    <span>{{ __('Administrator') }}</span>
                                    <span class="text-[0.55rem] opacity-0 transition-opacity group-hover:opacity-60">{{ __('Edit profile →') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <nav class="elysian-nav">
                    @foreach ($links as $link)
                        <a
                            href="{{ route($link['route']) }}"
                            class="elysian-nav-item {{ $link['active'] ? 'active' : '' }}"
                            @click="sidebarOpen = false"
                        >
                            @if ($link['icon'] === 'overview')
                                <svg viewBox="0 0 16 16"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
                            @elseif ($link['icon'] === 'applications')
                                <svg viewBox="0 0 16 16"><path d="M10 2h2a1 1 0 011 1v11a1 1 0 01-1 1H4a1 1 0 01-1-1V3a1 1 0 011-1h2"/><rect x="5" y="1" width="6" height="2" rx="1"/><path d="M5 7h6M5 10h4"/></svg>
                            @elseif ($link['icon'] === 'referrals')
                                <svg viewBox="0 0 16 16"><circle cx="5" cy="5" r="2.5"/><circle cx="11.5" cy="4.5" r="2"/><path d="M1.5 13c0-2.5 1.8-4.5 4-4.5 1.4 0 2.6.7 3.3 1.8"/><path d="M9.5 10.5h4M11.5 8.5v4"/></svg>
                            @elseif ($link['icon'] === 'onboarding')
                                <svg viewBox="0 0 16 16"><circle cx="7" cy="5" r="3"/><path d="M2 13c0-2.8 2.2-5 5-5"/><path d="M11 10l1.5 1.5L15 9"/></svg>
                            @elseif ($link['icon'] === 'members')
                                <svg viewBox="0 0 16 16"><circle cx="5.5" cy="5" r="2.5"/><path d="M1 13c0-2.5 2-4.5 4.5-4.5S10 10.5 10 13"/><circle cx="11.5" cy="5.5" r="2"/><path d="M10 12.5c.2-1.4 1.3-2.5 2.7-2.5 1.5 0 2.8 1.1 2.8 2.5"/></svg>
                            @elseif ($link['icon'] === 'courses')
                                <svg viewBox="0 0 16 16"><path d="M2 12V6l6-4 6 4v6"/><path d="M6 16v-5h4v5"/></svg>
                            @elseif ($link['icon'] === 'stories')
                                <svg viewBox="0 0 16 16"><path d="M8 1l1.85 3.75L14 5.75l-3 2.9.7 4.1L8 10.75l-3.7 2 .7-4.1L2 5.75l4.15-.5z"/></svg>
                            @elseif ($link['icon'] === 'community')
                                <svg viewBox="0 0 16 16"><path d="M14 10c0 1.1-.9 2-2 2H4l-3 3V4c0-1.1.9-2 2-2h9c1.1 0 2 .9 2 2v6z"/></svg>
                            @endif
                            <span class="flex-1">{{ $link['label'] }}</span>
                            @if ($link['count'] > 0)
                                <span class="ml-auto flex h-4 min-w-4 items-center justify-center rounded-full bg-[#EEB4C3] px-1 text-[0.52rem] font-bold text-[#09070A]">{{ $link['count'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <div class="elysian-side-footer">
                    <a href="{{ route('admin.courses.create') }}" class="elysian-side-footer-btn" style="color: rgba(238,180,195,0.7);" @click="sidebarOpen = false">
                        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="7"/><path d="M8 5v6M5 8h6"/></svg>
                        <span>{{ __('New Course') }}</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="elysian-side-footer-btn {{ request()->routeIs('profile.*') ? 'active' : '' }}" @click="sidebarOpen = false">
                        <svg viewBox="0 0 16 16"><circle cx="8" cy="5.5" r="3"/><path d="M2.5 14c0-3 2.5-5.5 5.5-5.5s5.5 2.5 5.5 5.5"/></svg>
                        <span>{{ __('My Profile') }}</span>
                    </a>
                    <a href="{{ route('home') }}" class="elysian-side-footer-btn" @click="sidebarOpen = false">
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

            {{-- Mobile backdrop --}}
            <div x-show="sidebarOpen" x-cloak class="elysian-sidebar-backdrop" @click="sidebarOpen = false"></div>

            {{-- Main content --}}
            <div class="flex min-w-0 flex-1 flex-col">

                <header class="elysian-topbar">
                    <button type="button" class="elysian-mobile-toggle" @click="sidebarOpen = true" aria-label="{{ __('Menu') }}">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <span class="elysian-breadcrumb">{{ __('Admin') }} / {{ $currentLabel }}</span>
                    <div class="elysian-topbar-right">
                        @include('layouts.partials.notification-bell')
                        <div class="elysian-topbar-greeting">
                            <p>{{ __('Admin Panel') }}</p>
                            <p>{{ $user->name }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="elysian-topbar-avatar" title="{{ __('Edit your profile') }}">
                            <span>{{ $initials }}</span>
                            @if ($profilePhotoUrl)
                                <img src="{{ $profilePhotoUrl }}" alt="{{ __('Profile photo') }}" onerror="this.remove()">
                            @endif
                        </a>
                    </div>
                </header>

                <main class="flex-1 p-5 lg:p-8">
                    @isset($header)
                        <div class="mb-7">{{ $header }}</div>
                    @endisset
                    {{ $slot }}
                </main>

            </div>
        </div>
    </body>
</html>
