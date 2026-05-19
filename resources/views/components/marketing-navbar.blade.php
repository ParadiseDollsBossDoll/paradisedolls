@props([
    'user' => null,
])
@php
    $isAuthenticated = $user !== null;
    $memberRoute = $user?->isAdmin()
        ? 'admin.dashboard'
        : 'member.dashboard';

    $links = [
        ['route' => 'our-story', 'label' => __('Our Story')],
        ['route' => 'work-from-home', 'label' => __('Work From Home')],
        ['route' => 'work-from-paradise', 'label' => __('Work From Paradise')],
        ['route' => 'perks', 'label' => __('Perks')],
        ['route' => 'multistreaming', 'label' => __('Multistreaming')],
        ['route' => 'success-stories', 'label' => __('Success Stories')],
        ['route' => $memberRoute, 'label' => __('Members'), 'auth' => true],
    ];
@endphp

<nav
    class="fixed left-0 right-0 top-0 z-50 transition-all duration-300"
    x-bind:class="transparent && !scrolled && !navOpen ? 'bg-transparent' : 'bg-white/[0.97] backdrop-blur-md border-b border-boss-rose/15 shadow-sm'"
    {{ $attributes }}
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between md:h-20">
            <a
                href="{{ route('home') }}"
                class="font-display text-[0.82rem] uppercase tracking-[0.28em] transition-colors duration-300"
                x-bind:class="transparent && !scrolled && !navOpen ? 'text-white' : 'text-boss-gold'"
            >
                {{ config('app.name') }}
            </a>

            <div class="hidden items-center gap-7 lg:flex">
                @foreach ($links as $link)
                    @if (($link['auth'] ?? false) && ! $isAuthenticated)
                        @continue
                    @endif

                    <a
                        href="{{ route($link['route']) }}"
                        class="text-[0.65rem] uppercase tracking-[0.14em] transition-colors duration-200 hover:text-boss-gold {{ request()->routeIs($link['route']) ? 'text-boss-gold' : '' }}"
                        x-bind:class="transparent && !scrolled && !navOpen ? 'text-white/90' : 'text-boss-dark'"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                @unless ($isAuthenticated)
                    <a
                        href="{{ route('login') }}"
                        class="hidden rounded-md border border-current px-6 py-2.5 text-[0.65rem] uppercase tracking-[0.14em] transition-all duration-300 hover:border-[#EEB4C3] hover:bg-[#EEB4C3] hover:text-white md:inline-flex"
                        x-bind:class="transparent && !scrolled && !navOpen ? 'text-white' : 'text-boss-dark'"
                    >
                        {{ __('Log in') }}
                    </a>
                @endunless

                <a
                    href="{{ route('home') }}#apply"
                    class="hidden rounded-md bg-[#EEB4C3] px-4 py-2 text-[0.65rem] uppercase tracking-[0.14em] text-white transition-colors hover:bg-[#e0a0b5] md:inline-flex"
                >
                    {{ __('Become A Doll') }}
                </a>

                <button
                    type="button"
                    class="rounded-sm p-2 lg:hidden"
                    @click="navOpen = !navOpen"
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

        <div x-cloak x-show="navOpen" x-transition class="border-t border-boss-pink bg-white py-4 lg:hidden">
            @foreach ($links as $link)
                @if (($link['auth'] ?? false) && ! $isAuthenticated)
                    @continue
                @endif

                <a
                    href="{{ route($link['route']) }}"
                    class="block px-4 py-3 text-[0.7rem] uppercase tracking-[0.14em] text-boss-dark transition-colors hover:bg-boss-cream hover:text-boss-gold"
                    @click="navOpen = false"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach

            <div @class(['grid gap-3 px-4 pt-4', 'sm:grid-cols-2' => ! $isAuthenticated])>
                @unless ($isAuthenticated)
                    <a
                        href="{{ route('login') }}"
                        class="block w-full border border-boss-gold py-3 text-center text-[0.7rem] uppercase tracking-[0.14em] text-boss-gold transition-colors hover:bg-boss-gold hover:text-white"
                        @click="navOpen = false"
                    >
                        {{ __('Log in') }}
                    </a>
                @endunless

                <a
                    href="{{ route('home') }}#apply"
                    class="block w-full rounded-md bg-[#EEB4C3]py-3 text-center text-[0.7rem] uppercase tracking-[0.14em] text-white transition-colors hover:bg-[#e0a0b5]"
                    @click="navOpen = false"
                >
                    {{ __('Become A Doll') }}
                </a>
            </div>
        </div>
    </div>
</nav>
