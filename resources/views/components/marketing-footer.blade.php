@props([
    'user' => null,
])
@php
    $isAuthenticated = $user !== null;

    $explore = [
        ['route' => 'our-story', 'label' => marketing_content('shared.nav.our_story_label')],
        ['route' => 'work-from-home', 'label' => marketing_content('shared.nav.work_from_home_label')],
        ['route' => 'work-from-paradise', 'label' => marketing_content('shared.nav.work_from_paradise_label')],
        ['route' => 'perks', 'label' => marketing_content('shared.nav.perks_label')],
        ['route' => 'multistreaming', 'label' => marketing_content('shared.nav.multistreaming_label')],
        ['route' => 'success-stories', 'label' => marketing_content('shared.nav.success_stories_label')],
    ];

    $socialLabels = marketing_items('shared.footer.social_labels');
    $applyUrl = marketing_link('shared.nav.apply_url', route('home').'#apply');
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
                    {{ marketing_content('shared.footer.description') }}
                </p>
                <div class="mt-6 flex gap-3">
                    @foreach ($socialLabels as $label)
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/10 text-[0.68rem] text-white/35 transition-colors hover:border-boss-gold/50 hover:text-boss-gold">{{ $label }}</span>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="mb-5 text-[0.65rem] uppercase tracking-[0.2em] text-white/30">{{ marketing_content('shared.footer.explore_label') }}</p>
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
                <p class="mb-5 text-[0.65rem] uppercase tracking-[0.2em] text-white/30">{{ marketing_content('shared.footer.members_label') }}</p>
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
                        <li><a href="{{ route('login') }}" class="text-[0.8rem] text-white/50 transition-colors hover:text-boss-gold">{{ marketing_content('shared.nav.login_label') }}</a></li>
                    @endif
                    <li>
                        <a href="{{ $applyUrl }}" class="text-[0.8rem] text-white/50 transition-colors hover:text-boss-gold">{{ marketing_content('shared.nav.apply_label') }}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-8 md:flex-row">
            <p class="text-[0.75rem] text-white/25">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
            <div class="flex items-center gap-6">
                <p class="text-[0.75rem] text-white/25">{{ marketing_content('shared.footer.made_with_care') }}</p>
                @if ($user?->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-[0.65rem] tracking-[0.1em] text-white/10 transition-colors hover:text-white/30">{{ __('Admin') }}</a>
                @endif
            </div>
        </div>
    </div>
</footer>
