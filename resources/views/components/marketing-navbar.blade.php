@props([
    'user' => null,
])
@php
    $isAuthenticated = $user !== null;
    $memberRoute = $user?->dashboardRouteName() ?? 'member.dashboard';

    $primaryLinks = [
        ['route' => 'home', 'label' => __('Home')],
        ['route' => 'our-story', 'label' => marketing_content('shared.nav.our_story_label')],
        ['route' => 'success-stories', 'label' => marketing_content('shared.nav.success_stories_label')],
        ['route' => $memberRoute, 'label' => marketing_content('shared.nav.members_label'), 'auth' => true],
    ];

    $exploreLinks = [
        ['route' => 'work-from-home', 'label' => marketing_content('shared.nav.work_from_home_label')],
        ['route' => 'work-from-paradise', 'label' => marketing_content('shared.nav.work_from_paradise_label')],
        ['route' => 'perks', 'label' => marketing_content('shared.nav.perks_label')],
        ['route' => 'multistreaming', 'label' => marketing_content('shared.nav.multistreaming_label')],
    ];

    $exploreIsActive = collect($exploreLinks)->contains(fn ($link) => request()->routeIs($link['route']));

    $applyUrl = marketing_link('shared.nav.apply_url', route('home').'#apply');
@endphp

<nav
    data-pd-marketing-navbar
    class="fixed left-0 right-0 top-0 z-50 transition-all duration-300"
    x-bind:class="transparent && !scrolled && !navOpen ? 'bg-transparent' : 'bg-white/[0.97] backdrop-blur-md border-b border-boss-rose/15 shadow-sm'"
    @keydown.escape.window="navOpen = false; exploreOpen = false"
    {{ $attributes }}
>
    <button
        x-cloak
        x-show="navOpen"
        x-transition.opacity
        type="button"
        class="fixed inset-0 top-16 -z-10 bg-boss-dark/25 backdrop-blur-[2px] lg:hidden"
        aria-label="{{ __('Close menu') }}"
        @click="navOpen = false"
    ></button>

    <div class="mx-auto max-w-[90rem] px-4 sm:px-6 lg:px-8">
        <div class="pd-marketing-navbar-row flex h-16 items-center justify-between md:h-20">
            <a
                href="{{ route('home') }}"
                class="pd-marketing-navbar-logo inline-flex items-center transition-opacity duration-300 hover:opacity-85"
                aria-label="{{ config('app.name') }}"
            >
                <img
                    src="{{ asset('images/brand/get-rich-with-paradise-dolls-logo.png') }}"
                    alt="{{ config('app.name') }}"
                    class="h-auto w-[102px] object-contain sm:w-[122px] lg:w-[132px]"
                >
            </a>

            <div class="pd-marketing-navbar-links hidden items-center lg:flex" aria-label="{{ __('Primary navigation') }}">
                @foreach ($primaryLinks as $link)
                    @if (($link['auth'] ?? false) && ! $isAuthenticated)
                        @continue
                    @endif

                    <a
                        href="{{ route($link['route']) }}"
                        title="{{ $link['label'] }}"
                        class="pd-marketing-navbar-link uppercase transition-colors duration-200 hover:text-boss-gold {{ request()->routeIs($link['route']) ? 'text-boss-gold' : '' }}"
                        x-bind:class="transparent && !scrolled && !navOpen ? 'text-white/90' : 'text-boss-dark'"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach

                <div class="relative" @click.outside="exploreOpen = false">
                    <button
                        type="button"
                        class="pd-marketing-navbar-link gap-1.5 uppercase transition-colors duration-200 hover:text-boss-gold {{ $exploreIsActive ? 'text-boss-gold' : '' }}"
                        x-bind:class="transparent && !scrolled && !navOpen ? 'text-white/90' : 'text-boss-dark'"
                        x-bind:aria-expanded="exploreOpen.toString()"
                        aria-controls="marketing-explore-menu"
                        @click="exploreOpen = !exploreOpen"
                    >
                        <span>{{ __('Explore') }}</span>
                        <svg class="h-3 w-3 transition-transform duration-200" x-bind:class="exploreOpen ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M4 6.25 8 10l4-3.75" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div
                        id="marketing-explore-menu"
                        x-cloak
                        x-show="exploreOpen"
                        x-transition.origin.top.right
                        class="absolute right-0 top-full mt-2 w-64 overflow-hidden rounded-md border border-boss-rose/20 bg-white p-2 text-boss-dark shadow-2xl shadow-black/15"
                    >
                        @foreach ($exploreLinks as $link)
                            <a
                                href="{{ route($link['route']) }}"
                                class="flex min-h-11 items-center rounded px-3 py-2.5 text-sm font-medium transition-colors hover:bg-boss-muted hover:text-boss-gold {{ request()->routeIs($link['route']) ? 'bg-boss-muted text-boss-gold' : '' }}"
                                @click="exploreOpen = false"
                            >
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pd-marketing-navbar-actions flex items-center gap-3">
                <x-language-selector
                    tone="plain"
                    class="hidden lg:inline-flex shrink-0"
                    x-bind:class="transparent && !scrolled && !navOpen ? 'border-white/35 bg-black/10 text-white hover:border-white/60' : 'border-boss-rose/25 bg-white/80 text-boss-dark hover:border-boss-rose/45'"
                />

                @unless ($isAuthenticated)
                    <a
                        href="{{ route('login') }}"
                        title="{{ marketing_content('shared.nav.login_label') }}"
                        class="pd-marketing-navbar-action pd-marketing-navbar-login hidden rounded-md border border-current px-5 py-2.5 uppercase transition-all duration-300 hover:border-[#EEB4C3] hover:bg-[#EEB4C3] hover:text-white lg:inline-flex"
                        x-bind:class="transparent && !scrolled && !navOpen ? 'text-white' : 'text-boss-dark'"
                    >
                        {{ marketing_content('shared.nav.login_label') }}
                    </a>
                @endunless

                <a
                    href="{{ $applyUrl }}"
                    title="{{ marketing_content('shared.nav.apply_label') }}"
                    class="pd-marketing-navbar-action pd-marketing-navbar-apply hidden rounded-md bg-[#EEB4C3] px-5 py-2.5 uppercase text-boss-dark transition-colors hover:bg-[#e0a0b5] sm:inline-flex"
                >
                    {{ marketing_content('shared.nav.apply_label') }}
                </a>

                <button
                    type="button"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-md transition-colors hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#EEB4C3] lg:hidden"
                    @click="navOpen = !navOpen"
                    x-bind:aria-expanded="navOpen.toString()"
                    aria-controls="marketing-mobile-menu"
                    aria-label="{{ __('Menu') }}"
                >
                    <span x-show="!navOpen" class="block space-y-1.5" x-bind:class="transparent && !scrolled ? 'text-white' : 'text-boss-dark'">
                        <span class="block h-px w-5 bg-current"></span>
                        <span class="block h-px w-5 bg-current"></span>
                        <span class="block h-px w-5 bg-current"></span>
                    </span>
                    <span x-cloak x-show="navOpen" class="relative block h-5 w-5 text-boss-dark">
                        <span class="absolute left-0 top-1/2 block h-px w-5 rotate-45 bg-current"></span>
                        <span class="absolute left-0 top-1/2 block h-px w-5 -rotate-45 bg-current"></span>
                    </span>
                </button>
            </div>
        </div>

        <div
            id="marketing-mobile-menu"
            x-cloak
            x-show="navOpen"
            x-transition.origin.top
            class="max-h-[calc(100dvh-4rem)] overflow-y-auto border-t border-boss-pink/70 bg-white pb-[max(1rem,env(safe-area-inset-bottom))] shadow-2xl lg:hidden md:max-h-[calc(100dvh-5rem)]"
        >
            <div class="flex items-center justify-between border-b border-boss-pink/60 px-4 py-3 sm:px-6">
                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-boss-dark/55">{{ __('Explore Paradise Dolls') }}</p>
                <x-language-selector />
            </div>

            <div class="grid px-2 py-2 sm:grid-cols-2 sm:gap-x-2 sm:px-4">
                @foreach (array_merge($primaryLinks, $exploreLinks) as $link)
                    @if (($link['auth'] ?? false) && ! $isAuthenticated)
                        @continue
                    @endif

                    <a
                        href="{{ route($link['route']) }}"
                        class="flex min-h-12 items-center rounded-md px-3 py-3 text-sm font-medium text-boss-dark transition-colors hover:bg-boss-cream hover:text-boss-gold {{ request()->routeIs($link['route']) ? 'bg-boss-muted text-boss-gold' : '' }}"
                        @click="navOpen = false"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>

            <div @class(['grid gap-3 border-t border-boss-pink/60 px-4 pt-4 sm:px-6', 'sm:grid-cols-2' => ! $isAuthenticated])>
                @unless ($isAuthenticated)
                    <a
                        href="{{ route('login') }}"
                        class="block w-full border border-boss-gold py-3 text-center text-[0.7rem] uppercase tracking-[0.14em] text-boss-gold transition-colors hover:bg-boss-gold hover:text-white"
                        @click="navOpen = false"
                    >
                        {{ marketing_content('shared.nav.login_label') }}
                    </a>
                @endunless

                <a
                    href="{{ $applyUrl }}"
                    class="block w-full rounded-md bg-[#EEB4C3] py-3 text-center text-[0.7rem] uppercase tracking-[0.14em] text-white transition-colors hover:bg-[#e0a0b5]"
                    @click="navOpen = false"
                >
                    {{ marketing_content('shared.nav.apply_label') }}
                </a>
            </div>
        </div>
    </div>
</nav>
