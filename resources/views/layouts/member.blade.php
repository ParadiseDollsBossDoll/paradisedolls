@php
    $user = auth()->user();
    $initials = $user->initials();
    $profilePhotoUrl = $user->profilePhotoUrl();
    $canAccessCommunity = $user->canModerateCommunity() || (bool) $user->modelProfile?->hasCommunityChatAccess();

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
            'route'  => 'member.testimonials.create',
            'label'  => __('My Testimony'),
            'active' => request()->routeIs('member.testimonials.*'),
            'icon'   => 'stories',
        ],
        [
            'route'  => 'member.referrals.index',
            'label'  => __('Referrals'),
            'active' => request()->routeIs('member.referrals.*'),
            'icon'   => 'referrals',
        ],
        [
            'route'  => 'community.show',
            'label'  => __('Community Chat'),
            'active' => request()->routeIs('community.*'),
            'icon'   => 'community',
            'visible' => $canAccessCommunity,
        ],
        [
            'route'  => 'profile.edit',
            'label'  => __('Profile'),
            'active' => request()->routeIs('profile.*'),
            'icon'   => 'profile',
        ],
    ];

    $links = array_values(array_filter($links, fn ($link) => $link['visible'] ?? true));

    $currentLabel = request()->routeIs('notifications.*')
        ? __('Notifications')
        : (collect($links)->firstWhere('active', true)['label'] ?? __('Dashboard'));
    $hideSidebar = (bool) ($hideSidebar ?? false);
@endphp
@php $siteTheme = \App\Models\SiteSetting::get('theme', ['mode'=>'dark','primary'=>'#EEB4C3','primaryLight'=>'#F3C3CF']); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <script>(function(){function hRgb(h){return parseInt(h.slice(1,3),16)+' '+parseInt(h.slice(3,5),16)+' '+parseInt(h.slice(5,7),16);}function lum(h){var r=parseInt(h.slice(1,3),16)/255,g=parseInt(h.slice(3,5),16)/255,b=parseInt(h.slice(5,7),16)/255;return 0.2126*r+0.7152*g+0.0722*b;}function applyVars(s){var h=document.documentElement;h.classList.toggle('light-mode',s.mode==='light');if(s.primary){var p=s.primary,pl=s.primaryLight||p;h.style.setProperty('--pd-primary',p);h.style.setProperty('--pd-gold',p);h.style.setProperty('--pd-gold-rgb',hRgb(p));h.style.setProperty('--pd-gold-light-rgb',hRgb(pl));h.style.setProperty('--pd-gold-hover-rgb',hRgb(pl));h.style.setProperty('--pd-primary-hover',pl);h.style.setProperty('--pd-gold-light',pl);h.style.setProperty('--pd-primary-on',lum(p)>0.35?'#09070A':'#FFF8F6');}}try{applyVars(@json($siteTheme));}catch(e){applyVars({mode:'dark',primary:'#EEB4C3',primaryLight:'#F3C3CF'});}window.pdApplyTheme=function(s){applyVars(s);};}());</script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased pd-dark-surface {{ $player ? 'h-screen overflow-hidden' : 'min-h-screen' }}" x-data="{ sidebarOpen: false }">
        <div class="flex {{ $player ? 'h-screen' : 'min-h-screen' }}">
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
                                    <div class="elysian-avatar">
                                        <span>{{ $initials }}</span>
                                        @if ($profilePhotoUrl)
                                            <img src="{{ $profilePhotoUrl }}" alt="{{ __('Profile photo') }}" loading="lazy" decoding="async" onerror="this.remove()">
                                        @endif
                                    </div>
                                    <div class="elysian-online-dot"></div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="elysian-side-name">{{ $user->name }}</div>
                                    <div class="elysian-side-sub">{{ __('Paradise Dolls Member') }}</div>
                                </div>
                            </div>
                            <div class="elysian-side-progress">
                                <div class="mb-1.5 flex items-center justify-between">
                                    <span>{{ __('Academy') }}</span>
                                    <strong>{{ $layoutProgress }}%</strong>
                                </div>
                                <div class="elysian-side-progress-track" aria-label="{{ __('Academy progress :percent%', ['percent' => $layoutProgress]) }}">
                                    <div style="width: {{ $layoutProgress }}%"></div>
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
                                @elseif ($link['icon'] === 'stories')
                                    <svg viewBox="0 0 16 16"><path d="M3 2h10v12H3z"/><path d="M5.5 5h5M5.5 8h5M5.5 11h3"/></svg>
                                @elseif ($link['icon'] === 'referrals')
                                    <svg viewBox="0 0 16 16"><circle cx="5.5" cy="5" r="2.5"/><path d="M1.5 13c0-2.4 1.8-4.3 4-4.3s4 1.9 4 4.3"/><path d="M10.5 4.5h4M12.5 2.5v4"/><path d="M10.5 10.5h4M12.5 8.5v4"/></svg>
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
                <header class="elysian-topbar {{ $player ? 'elysian-topbar--course' : '' }}">
                    @unless ($hideSidebar)
                        <button type="button" class="elysian-mobile-toggle" @click="sidebarOpen = true" aria-label="{{ __('Menu') }}">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    @endunless
                    @if ($player)
                        <div class="elysian-topbar-main">
                            <span class="elysian-breadcrumb">{{ __('Members') }} / {{ $currentLabel }}</span>
                            <div class="elysian-topbar-right">
                                @include('layouts.partials.notification-bell')
                                <a href="{{ route('member.referrals.index') }}" class="elysian-topbar-refer">
                                    {{ __('Refer') }}
                                </a>
                                @include('layouts.partials.member-account-menu')
                            </div>
                        </div>
                    @else
                        <span class="elysian-breadcrumb">{{ __('Members') }} / {{ $currentLabel }}</span>
                        <div class="elysian-topbar-right">
                            @include('layouts.partials.notification-bell')
                            <a href="{{ route('member.referrals.index') }}" class="elysian-topbar-refer hidden sm:inline-flex">
                                {{ __('Refer') }}
                            </a>
                            @include('layouts.partials.member-account-menu')
                        </div>
                    @endif
                </header>

                <main class="{{ $player ? 'flex-1 overflow-hidden p-0' : ('flex-1 overflow-auto ' . ($hideSidebar ? 'p-4 sm:p-5 lg:p-6 xl:p-8' : 'p-5 lg:p-8')) }}">
                    @isset($header)
                        <div class="mb-7">{{ $header }}</div>
                    @endisset
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
