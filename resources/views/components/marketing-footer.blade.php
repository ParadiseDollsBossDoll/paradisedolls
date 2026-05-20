@props([
    'user' => null,
])
@php
    $isAuthenticated = $user !== null;

    $explore = [
        ['route' => 'our-story', 'label' => __('Our Story')],
        ['route' => 'work-from-home', 'label' => __('Work From Home')],
        ['route' => 'work-from-paradise', 'label' => __('Work From Paradise')],
        ['route' => 'perks', 'label' => __('Perks')],
        ['route' => 'multistreaming', 'label' => __('Multistreaming')],
        ['route' => 'success-stories', 'label' => __('Success Stories')],
    ];
@endphp

<footer class="bg-boss-dark py-16 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-12 grid grid-cols-1 gap-12 md:grid-cols-4">
            <div class="md:col-span-2">
                <img
                    src="{{ asset('images/brand/get-rich-with-paradise-dolls-logo.png') }}"
                    alt="{{ config('app.name') }}"
                    class="mb-4 h-auto w-[154px] object-contain"
                >
                <p class="max-w-sm text-[0.875rem] leading-relaxed text-white/50">
                    {{ __('A luxury feminine opportunity platform and Boss Doll Blueprint academy for remote income, community, and confident online success.') }}
                </p>
                <div class="mt-6 flex gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/10 text-[0.68rem] text-white/35 transition-colors hover:border-boss-gold/50 hover:text-boss-gold">IG</span>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/10 text-[0.68rem] text-white/35 transition-colors hover:border-boss-gold/50 hover:text-boss-gold">YT</span>
                </div>
            </div>

            <div>
                <p class="mb-5 text-[0.65rem] uppercase tracking-[0.2em] text-white/30">{{ __('Explore') }}</p>
                <ul class="space-y-3">
                    @foreach ($explore as $item)
                        <li>
                            <a href="{{ route($item['route']) }}" class="text-[0.8rem] text-white/50 transition-colors hover:text-boss-gold">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="mb-5 text-[0.65rem] uppercase tracking-[0.2em] text-white/30">{{ __('Members') }}</p>
                <ul class="space-y-3">
                    @if ($isAuthenticated)
                        @if ($user->isAdmin())
                            <li><a href="{{ route('admin.dashboard') }}" class="text-[0.8rem] text-white/50 transition-colors hover:text-boss-gold">{{ __('Dashboard') }}</a></li>
                            <li><a href="{{ route('admin.courses.index') }}" class="text-[0.8rem] text-white/50 transition-colors hover:text-boss-gold">{{ __('Academy') }}</a></li>
                        @else
                            <li><a href="{{ route('member.dashboard') }}" class="text-[0.8rem] text-white/50 transition-colors hover:text-boss-gold">{{ __('Dashboard') }}</a></li>
                            <li><a href="{{ route('member.courses.index') }}" class="text-[0.8rem] text-white/50 transition-colors hover:text-boss-gold">{{ __('Academy') }}</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('login') }}" class="text-[0.8rem] text-white/50 transition-colors hover:text-boss-gold">{{ __('Log in') }}</a></li>
                    @endif
                    <li>
                        <a href="{{ route('home') }}#apply" class="text-[0.8rem] text-white/50 transition-colors hover:text-boss-gold">{{ __('Become a doll') }}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-8 md:flex-row">
            <p class="text-[0.75rem] text-white/25">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
            <div class="flex items-center gap-6">
                <p class="text-[0.75rem] text-white/25">{{ __('Made with care for members worldwide') }}</p>
                @if ($user?->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-[0.65rem] tracking-[0.1em] text-white/10 transition-colors hover:text-white/30">{{ __('Admin') }}</a>
                @endif
            </div>
        </div>
    </div>
</footer>
