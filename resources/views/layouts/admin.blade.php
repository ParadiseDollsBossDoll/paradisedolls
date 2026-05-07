@php
    $user = auth()->user();
    $initial = strtoupper(substr($user->name, 0, 1)) ?: 'A';
    $pendingLayoutApplications = \App\Models\ModelApplication::query()
        ->where('status', \App\Models\ModelApplication::STATUS_PENDING)
        ->count();
    $pendingLayoutVerification = \App\Models\ModelProfile::query()
        ->where('verification_status', \App\Models\ModelProfile::VERIFICATION_SUBMITTED)
        ->count();

    $links = [
        ['route' => 'admin.dashboard', 'label' => __('Overview'), 'active' => request()->routeIs('admin.dashboard')],
        ['route' => 'admin.applications.index', 'label' => __('Applications'), 'active' => request()->routeIs('admin.applications.*'), 'count' => $pendingLayoutApplications],
        ['route' => 'admin.onboarding.index', 'label' => __('Onboarding'), 'active' => request()->routeIs('admin.onboarding.*'), 'count' => $pendingLayoutVerification],
        ['route' => 'admin.models.progress', 'label' => __('Members'), 'active' => request()->routeIs('admin.models.progress')],
        ['route' => 'admin.courses.index', 'label' => __('Courses'), 'active' => request()->routeIs('admin.courses.*')],
        ['route' => 'admin.testimonials.index', 'label' => __('Stories'), 'active' => request()->routeIs('admin.testimonials.*')],
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
                class="fixed inset-y-0 left-0 z-40 flex w-60 flex-col border-r border-white/[0.06] bg-boss-dark transition-transform duration-300 lg:static lg:inset-auto lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            >
                <div class="border-b border-white/[0.06] px-5 py-6">
                    <p class="font-display text-[1rem] text-boss-gold">{{ config('app.name') }} Admin</p>
                    <p class="mt-1 text-[0.62rem] uppercase tracking-[0.14em] text-boss-ivory/25">{{ __('Control Panel') }}</p>
                </div>

                <nav class="flex-1 space-y-1 px-3 py-4">
                    @foreach ($links as $link)
                        <a
                            href="{{ route($link['route']) }}"
                            class="flex items-center gap-3 rounded-sm border px-4 py-3 text-[0.82rem] transition-all duration-200 {{ $link['active'] ? 'border-boss-gold/20 bg-boss-gold/10 text-boss-gold' : 'border-transparent text-boss-ivory/42 hover:border-white/[0.06] hover:bg-white/[0.035] hover:text-boss-ivory/75' }}"
                            @click="sidebarOpen = false"
                        >
                            <span class="h-1.5 w-1.5 rounded-full {{ $link['active'] ? 'bg-boss-gold' : 'bg-boss-ivory/18' }}"></span>
                            <span class="flex-1">{{ $link['label'] }}</span>
                            @if (($link['count'] ?? 0) > 0)
                                <span class="rounded-full bg-boss-gold px-2 py-0.5 text-[0.62rem] font-semibold text-boss-ink">{{ $link['count'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <div class="space-y-1 border-t border-white/[0.06] px-3 pb-5 pt-3">
                    <a href="{{ route('admin.courses.create') }}" class="flex items-center gap-3 rounded-sm border border-boss-gold/15 bg-boss-gold/[0.08] px-4 py-2.5 text-[0.75rem] text-boss-gold transition-colors hover:bg-boss-gold/15">
                        {{ __('New Course') }}
                    </a>
                    <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-sm px-4 py-2.5 text-[0.75rem] text-boss-ivory/25 transition-colors hover:text-boss-ivory/55">
                        {{ __('Main Site') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-sm px-4 py-2.5 text-left text-[0.75rem] text-boss-ivory/25 transition-colors hover:text-red-400">
                            {{ __('Sign Out') }}
                        </button>
                    </form>
                </div>
            </aside>

            <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-black/75 lg:hidden" @click="sidebarOpen = false"></div>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-white/[0.06] bg-boss-dark/95 px-5 py-4 backdrop-blur lg:px-8">
                    <button type="button" class="rounded-sm p-2 text-boss-ivory/45 lg:hidden" @click="sidebarOpen = true" aria-label="{{ __('Menu') }}">
                        <span class="block space-y-1">
                            <span class="block h-px w-5 bg-current"></span>
                            <span class="block h-px w-5 bg-current"></span>
                            <span class="block h-px w-5 bg-current"></span>
                        </span>
                    </button>
                    <p class="text-[0.78rem] text-boss-ivory/32">{{ __('Admin Dashboard') }}</p>
                    <div class="ml-auto flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full border border-boss-gold/20 bg-boss-gold/10 font-display text-[0.75rem] text-boss-gold">{{ $initial }}</div>
                        <span class="hidden text-[0.78rem] text-boss-ivory/42 sm:block">{{ $user->name }}</span>
                    </div>
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
