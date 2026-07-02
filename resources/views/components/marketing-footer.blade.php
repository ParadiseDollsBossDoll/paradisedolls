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

    $whatsappUrl = marketing_link('shared.footer.whatsapp_url', 'https://api.whatsapp.com/send?phone=447346924436');
    if (preg_match('/^https?:\/\/wa\.me\/447346924436\/?$/i', $whatsappUrl)) {
        $whatsappUrl = 'https://api.whatsapp.com/send?phone=447346924436';
    }

    $facebookUrl = marketing_link('shared.footer.facebook_url', 'https://www.facebook.com/share/19BBXuqjvS/?mibextid=wwXIfr');
    $facebookUrl = str_replace('19BXuqjvS', '19BBXuqjvS', $facebookUrl);
    if (
        str_contains($facebookUrl, 'facebook.com/people/Paradise-Dolls/61590818625550')
        || str_contains($facebookUrl, 'share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F19BBXuqjvS')
    ) {
        $facebookUrl = 'https://www.facebook.com/share/19BBXuqjvS/?mibextid=wwXIfr';
    }

    $socialLinks = collect([
        [
            'key' => 'tiktok',
            'label' => 'TikTok',
            'url' => marketing_link('shared.footer.tiktok_url', 'https://www.tiktok.com/@paradisedollsstreaming'),
        ],
        [
            'key' => 'snapchat',
            'label' => 'Snapchat',
            'url' => marketing_link('shared.footer.snapchat_url', 'https://snapchat.com/t/XDWG3Kkz'),
        ],
        [
            'key' => 'instagram',
            'label' => 'Instagram',
            'url' => marketing_link('shared.footer.instagram_url', 'https://www.instagram.com/barbiebossdoll/'),
        ],
        [
            'key' => 'whatsapp',
            'label' => 'WhatsApp',
            'url' => $whatsappUrl,
        ],
        [
            'key' => 'telegram',
            'label' => 'Telegram',
            'url' => marketing_link('shared.footer.telegram_url', 'https://t.me/paradisedolls26'),
        ],
        [
            'key' => 'facebook',
            'label' => 'Facebook',
            'url' => $facebookUrl,
        ],
    ])->filter(fn ($item) => filled($item['url']))->values();
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
                <div class="mt-6 flex flex-wrap gap-3">
                    @foreach ($socialLinks as $social)
                        <a
                            href="{{ $social['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="{{ __('Open :platform', ['platform' => $social['label']]) }}"
                            title="{{ $social['label'] }}"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/[0.03] text-white/48 transition-colors hover:border-boss-gold/55 hover:bg-boss-gold/10 hover:text-boss-gold focus:outline-none focus:ring-2 focus:ring-boss-gold/40"
                        >
                            <span class="inline-flex items-center justify-center" style="height: 18px; width: 18px;">
                                @switch($social['key'])
                                    @case('tiktok')
                                        <svg style="height: 18px; width: 18px;" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M16.6 3c.25 1.66 1.18 3.06 2.49 3.98A6.2 6.2 0 0 0 22 8.03v3.42a9.1 9.1 0 0 1-5.26-1.68v6.55c0 3.1-2.52 5.68-5.75 5.68A5.72 5.72 0 0 1 5.2 16.3c0-3.24 2.58-5.72 5.82-5.72.5 0 .96.06 1.41.18v3.54a3.08 3.08 0 0 0-1.44-.36 2.34 2.34 0 0 0-2.38 2.34 2.37 2.37 0 0 0 2.38 2.36c1.35 0 2.35-1.05 2.35-2.45V3h3.26Z"/>
                                        </svg>
                                        @break

                                    @case('snapchat')
                                        <svg style="height: 17px; width: 17px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M8 10.2V7.6C8 5 9.75 3.4 12 3.4s4 1.6 4 4.2v2.6"/>
                                            <path d="M6.3 11.2c1.35.25 1.55.8.55 1.55-.58.43-1.15.62-1.85.75.55 1.45 1.8 2.35 3.18 2.75.45.13.66.48.78.9.18.62.88.52 1.4.32.52-.2 1.08-.34 1.64-.34s1.12.14 1.64.34c.52.2 1.22.3 1.4-.32.12-.42.33-.77.78-.9 1.38-.4 2.63-1.3 3.18-2.75-.7-.13-1.27-.32-1.85-.75-1-.75-.8-1.3.55-1.55"/>
                                            <path d="M8 9.5c-1.05-.55-1.8-.45-2.25.28M16 9.5c1.05-.55 1.8-.45 2.25.28"/>
                                        </svg>
                                        @break

                                    @case('instagram')
                                        <svg style="height: 18px; width: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <rect x="4" y="4" width="16" height="16" rx="4.5"/>
                                            <circle cx="12" cy="12" r="3.7"/>
                                            <circle cx="17.2" cy="6.8" r="0.8" fill="currentColor" stroke="none"/>
                                        </svg>
                                        @break

                                    @case('whatsapp')
                                        <svg style="height: 17px; width: 17px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M5.4 18.6 4 22l3.55-1.32A9 9 0 1 0 3 12a8.95 8.95 0 0 0 2.4 6.6Z"/>
                                            <path d="M9.1 8.7c.18-.42.35-.48.66-.48h.5c.18 0 .43.06.65.54l.78 1.74c.12.3.08.54-.1.76l-.42.52c-.2.25-.17.48.02.75.45.64 1.05 1.26 1.75 1.78.3.22.55.25.82.02l.6-.5c.2-.18.45-.22.74-.08l1.62.78c.48.23.56.47.52.78-.08.68-.68 1.35-1.32 1.48-.9.18-2.98-.28-5.18-2.48-2.18-2.18-2.76-4.2-2.6-5.1.08-.42.26-.7.46-1.08Z"/>
                                        </svg>
                                        @break

                                    @case('telegram')
                                        <svg style="height: 17px; width: 17px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M21 4.5 3.9 11.1c-.82.32-.8 1.46.04 1.75l4.2 1.42 1.62 5.02c.24.75 1.2.95 1.72.36l2.4-2.7 4.28 3.15c.68.5 1.65.12 1.8-.72L22 5.6c.14-.82-.28-1.4-1-1.1Z"/>
                                            <path d="m8.25 14.2 8.98-5.62-6.95 7.66"/>
                                        </svg>
                                        @break

                                    @case('facebook')
                                        <svg style="height: 17px; width: 17px;" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M14.4 8.3V6.75c0-.74.5-.91.85-.91h2.16V2.1L14.44 2C11.15 2 10.4 4.45 10.4 6.02V8.3H7.8v3.9h2.6V22h4v-9.8h3.02l.47-3.9H14.4Z"/>
                                        </svg>
                                        @break
                                @endswitch
                            </span>
                        </a>
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
