@php
    $user = auth()->user();
    $profilePhotoUrl = $user->profilePhotoUrl();
    $links = [
        ['route' => 'chatter.dashboard', 'label' => __('Time Tracker'), 'active' => request()->routeIs('chatter.*'), 'icon' => 'clock'],
        ['route' => 'profile.edit', 'label' => __('Profile'), 'active' => request()->routeIs('profile.*'), 'icon' => 'profile'],
    ];
    $currentLabel = request()->routeIs('notifications.*') ? __('Notifications') : (collect($links)->firstWhere('active', true)['label'] ?? __('Time Tracker'));
    $siteTheme = \App\Models\SiteSetting::get('theme', ['mode'=>'dark','primary'=>'#EEB4C3','primaryLight'=>'#F3C3CF']);
    $hasOpenShift = $user->chatterShifts()->whereNull('clocked_out_at')->exists();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @include('partials.google-tag-manager-head')
    <script>(function(){function hRgb(h){return parseInt(h.slice(1,3),16)+' '+parseInt(h.slice(3,5),16)+' '+parseInt(h.slice(5,7),16);}function lum(h){var r=parseInt(h.slice(1,3),16)/255,g=parseInt(h.slice(3,5),16)/255,b=parseInt(h.slice(5,7),16)/255;return 0.2126*r+0.7152*g+0.0722*b;}function applyVars(s){var h=document.documentElement;h.classList.toggle('light-mode',s.mode==='light');if(s.primary){var p=s.primary,pl=s.primaryLight||p;h.style.setProperty('--pd-primary',p);h.style.setProperty('--pd-gold',p);h.style.setProperty('--pd-gold-rgb',hRgb(p));h.style.setProperty('--pd-primary-on',lum(p)>0.35?'#09070A':'#FFF8F6');}}applyVars(@json($siteTheme));}());</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen pd-dark-surface font-sans antialiased" x-data="{ sidebarOpen: false, logoutOpen: false }">
@include('partials.google-tag-manager-body')

<div class="flex min-h-screen">
    <aside class="elysian-sidebar" :class="sidebarOpen ? 'is-open' : ''">
        <div class="elysian-brand">
            <div>
                <img src="{{ asset('images/brand/get-rich-with-paradise-dolls-logo.png') }}" alt="{{ config('app.name') }}" class="h-auto w-[122px] object-contain">
                <div class="elysian-brand-sub">{{ __('Chatter Workspace') }}</div>
            </div>
        </div>
        <a href="{{ route('profile.edit') }}" class="elysian-side-profile">
            <div class="elysian-side-profile-inner">
                <div class="elysian-side-profile-row">
                    <div class="elysian-avatar-wrap">
                        <div class="elysian-avatar"><span>{{ $user->initials() }}</span>@if($profilePhotoUrl)<img src="{{ $profilePhotoUrl }}" alt="{{ __('Profile photo') }}">@endif</div>
                        <div class="elysian-online-dot"></div>
                    </div>
                    <div class="min-w-0"><div class="elysian-side-name">{{ $user->name }}</div><div class="elysian-side-sub">{{ __('Chatter') }}</div></div>
                </div>
            </div>
        </a>
        <nav class="elysian-nav">
            @foreach($links as $link)
                <a href="{{ route($link['route']) }}" class="elysian-nav-item {{ $link['active'] ? 'active' : '' }}" @click="sidebarOpen=false">
                    @if($link['icon']==='clock')
                        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6.5"/><path d="M8 4v4l3 2"/></svg>
                    @else
                        <svg viewBox="0 0 16 16"><circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6"/></svg>
                    @endif
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>
        <div class="elysian-side-footer">
            @if($hasOpenShift)
                <button type="button" class="elysian-side-footer-btn" @click="logoutOpen = true">
                    <svg viewBox="0 0 16 16"><path d="M10 3l3 5-3 5M3 8h10"/></svg>
                    <span>{{ __('Sign Out') }}</span>
                </button>
            @else
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="elysian-side-footer-btn"><svg viewBox="0 0 16 16"><path d="M10 3l3 5-3 5M3 8h10"/></svg><span>{{ __('Sign Out') }}</span></button></form>
            @endif
        </div>
    </aside>
    <div x-show="sidebarOpen" x-cloak class="elysian-sidebar-backdrop" @click="sidebarOpen=false"></div>
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="elysian-topbar">
            <button type="button" class="elysian-mobile-toggle" @click="sidebarOpen=true" aria-label="{{ __('Menu') }}"><span></span><span></span><span></span></button>
            <span class="elysian-breadcrumb">{{ __('Chatter') }} / {{ $currentLabel }}</span>
            <div class="elysian-topbar-right">@include('layouts.partials.notification-bell')<a href="{{ route('profile.edit') }}" class="elysian-topbar-avatar"><span>{{ $user->initials() }}</span>@if($profilePhotoUrl)<img src="{{ $profilePhotoUrl }}" alt="{{ __('Profile photo') }}">@endif</a></div>
        </header>
        <main class="flex-1 overflow-auto p-4 sm:p-5 lg:p-8">{{ $slot }}</main>
    </div>
</div>

@if($hasOpenShift)
    <div x-show="logoutOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4" @keydown.escape.window="logoutOpen = false">
        <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="logoutOpen = false"></div>
        <section class="relative w-full max-w-lg overflow-hidden rounded-lg border border-white/[0.09] bg-boss-panel shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="chatter-logout-title">
            <div class="border-b border-white/[0.07] px-6 py-5">
                <p class="pd-kicker">{{ __('Active shift') }}</p>
                <h2 id="chatter-logout-title" class="mt-2 font-display text-2xl text-boss-ivory">{{ __('Clock out and sign out?') }}</h2>
                <p class="mt-3 text-sm leading-6 text-boss-ivory/55">{{ __('You are currently clocked in. Signing out will clock you out now, end any active break, and save your worked time.') }}</p>
            </div>
            <div class="flex flex-col-reverse gap-3 px-6 py-5 sm:flex-row sm:justify-end">
                <button type="button" class="pd-btn-secondary rounded-lg px-5 py-3 text-xs" @click="logoutOpen = false">{{ __('Keep working') }}</button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="pd-btn-primary w-full rounded-lg px-5 py-3 text-xs sm:w-auto">{{ __('Clock Out & Sign Out') }}</button>
                </form>
            </div>
        </section>
    </div>
@endif
</body>
</html>
