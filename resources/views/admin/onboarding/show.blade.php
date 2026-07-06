<x-admin-layout>
    <div class="pd-admin-onboarding pd-admin-onboarding-profile mx-auto max-w-6xl space-y-6 text-boss-ivory">

        {{-- ── Header ──────────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('admin.onboarding.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[0.75rem] text-boss-ivory/40 transition hover:text-boss-ivory/70">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    {{ __('Back to Onboarding') }}
                </a>
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border border-boss-gold/25 bg-boss-gold/10 font-display text-xl text-boss-gold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="pd-heading text-[1.6rem] leading-tight text-boss-ivory">{{ $user->name }}</h1>
                        @if ($profile->stage_name)
                            <p class="text-[0.82rem] text-boss-gold/80">{{ $profile->stage_name }}</p>
                        @endif
                        <p class="text-[0.74rem] text-boss-ivory/35">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.onboarding.export-profile', $profile) }}" class="rounded-xl border border-boss-gold/20 bg-boss-gold/[0.07] px-3 py-1.5 text-[0.72rem] font-semibold text-boss-gold transition hover:bg-boss-gold/[0.13]">
                    {{ __('Download CRM Excel') }}
                </a>
                <span class="rounded-full bg-boss-gold/10 px-3 py-1 text-[0.72rem] font-semibold text-boss-gold">
                    {{ $profile->onboardingPercent() }}% complete
                </span>
                <span class="text-[0.72rem] text-boss-ivory/30">Joined {{ $user->created_at->toFormattedDateString() }}</span>
            </div>
        </div>

        {{-- ── Flash messages ──────────────────────────────────────── --}}
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif
        @if (session('warning'))
            <div class="space-y-3 rounded-xl border border-amber-300/20 bg-amber-300/10 p-4 text-sm text-amber-100">
                <p>{{ session('warning') }}</p>
                @if (session('approval_fallback_password'))
                    <div class="rounded-xl border border-amber-300/25 bg-boss-ink px-4 py-3 text-boss-ivory">
                        <p class="text-[0.65rem] uppercase tracking-[0.14em] text-amber-200/60">{{ __('Temporary password (email failed)') }}</p>
                        <p class="mt-1 text-xs text-boss-ivory/40">{{ session('approval_fallback_email') }}</p>
                        <p class="mt-2 select-all break-all font-mono text-base font-semibold tracking-wide">{{ session('approval_fallback_password') }}</p>
                    </div>
                @endif
                @if (session('manual_login_password'))
                    <div class="rounded-xl border border-amber-300/25 bg-boss-ink px-4 py-3 text-boss-ivory">
                        <p class="text-[0.65rem] uppercase tracking-[0.14em] text-amber-200/60">{{ __('Manual temporary password') }}</p>
                        <p class="mt-1 text-xs text-boss-ivory/40">{{ session('manual_login_email') }}</p>
                        <p class="mt-2 select-all break-all font-mono text-base font-semibold tracking-wide">{{ session('manual_login_password') }}</p>
                    </div>
                @endif
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        {{-- ── Onboarding progress timeline ────────────────────────── --}}
        @php
            $applicationPhotos = $profile->application
                ? collect($profile->application->photo_paths ?? [])->filter()->values()
                : collect();
        @endphp

        <section class="pd-panel-strong p-5">
            <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Onboarding Progress') }}</p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @php
                    $steps = [
                        ['label' => 'Account Created',   'done' => true,                                'icon' => '👤'],
                        ['label' => 'Info Form',          'done' => $profile->hasInformationForm(),      'icon' => '📋'],
                        ['label' => 'Docs Submitted',     'done' => $profile->hasVerificationSubmission(), 'icon' => '📄'],
                        ['label' => 'Verified',           'done' => $profile->isVerified(),              'icon' => '✅'],
                        ['label' => 'Discord Invited',    'done' => $profile->isCommunityInvited(),      'icon' => '💬'],
                        ['label' => 'Role Assigned',      'done' => $profile->isCommunityRoleAssigned(), 'icon' => '🎉'],
                    ];
                @endphp
                @foreach ($steps as $step)
                    <div class="flex flex-col items-center gap-2 rounded-xl border p-3 text-center
                        {{ $step['done'] ? 'border-green-400/20 bg-green-400/[0.06]' : 'border-white/[0.05] bg-white/[0.02]' }}">
                        <span class="text-xl">{{ $step['icon'] }}</span>
                        <p class="text-[0.65rem] leading-snug {{ $step['done'] ? 'text-green-300' : 'text-boss-ivory/30' }}">{{ $step['label'] }}</p>
                        @if ($step['done'])
                            <span class="rounded-full bg-green-400/15 px-2 py-0.5 text-[0.6rem] text-green-300">Done</span>
                        @else
                            <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.6rem] text-boss-ivory/25">Pending</span>
                        @endif
                    </div>
                @endforeach
            </div>
            @if ($profile->information_submitted_at)
                <p class="mt-3 text-[0.7rem] text-boss-ivory/28">Info form submitted {{ $profile->information_submitted_at->toFormattedDateString() }}</p>
            @endif
        </section>

        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">

            {{-- ════════════════ LEFT COLUMN ════════════════ --}}
            <div class="space-y-5">

                {{-- Identity & Contact --}}
                <section class="pd-panel-strong p-5">
                    <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Identity & Contact') }}</p>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-[0.82rem]">
                        @foreach ([
                            ['Legal Name',        $profile->legal_name],
                            ['Stage Name',        $profile->stage_name],
                            ['Date of Birth',     $profile->date_of_birth?->format('M d, Y')],
                            ['Phone',             $profile->phone],
                            ['Country',           $profile->country],
                            ['City',              $profile->city],
                            ['Nationality',       $profile->nationality],
                            ['Spoken Languages',  $profile->spoken_languages],
                            ['Social Handles',    $profile->social_handles],
                            ['With Another Agency', $profile->with_other_agency],
                            ['How Found Us',      $profile->hear_about_us],
                        ] as [$label, $value])
                            @if ($value)
                                <div>
                                    <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">{{ $label }}</p>
                                    <p class="mt-0.5 text-boss-ivory/75">{{ $value }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>

                {{-- Application Photos --}}
                @if ($profile->application && $applicationPhotos->isNotEmpty())
                    <section class="pd-panel-strong p-5">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Application Photos') }}</p>
                            <span class="rounded-full bg-white/[0.04] px-2.5 py-0.5 text-[0.68rem] text-boss-ivory/42">
                                {{ $applicationPhotos->count() }} {{ $applicationPhotos->count() === 1 ? __('photo') : __('photos') }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($applicationPhotos as $index => $path)
                                <div class="overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.025]">
                                    <a href="{{ route('admin.applications.photos.view', [$profile->application, $index]) }}" target="_blank" rel="noopener" class="block aspect-[4/5] bg-white/[0.03]">
                                        <img
                                            src="{{ route('admin.applications.photos.view', [$profile->application, $index]) }}"
                                            alt="{{ __('Application photo :number', ['number' => $index + 1]) }}"
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        >
                                    </a>
                                    <div class="flex items-center justify-between gap-2 border-t border-white/[0.06] px-3 py-2">
                                        <span class="text-[0.68rem] text-boss-ivory/38">{{ __('Photo :number', ['number' => $index + 1]) }}</span>
                                        <div class="flex shrink-0 gap-2">
                                            <a href="{{ route('admin.applications.photos.view', [$profile->application, $index]) }}" target="_blank" rel="noopener" class="text-[0.68rem] font-medium text-boss-gold transition hover:text-boss-gold-light">
                                                {{ __('View') }}
                                            </a>
                                            <a href="{{ route('admin.applications.photos.show', [$profile->application, $index]) }}" class="text-[0.68rem] text-boss-ivory/35 transition hover:text-boss-ivory/65">
                                                {{ __('Download') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Appearance & Style --}}
                @if ($profile->height || $profile->weight || $profile->hair_color || $profile->eye_color || $profile->body_type || $profile->has_tattoos_piercings)
                    <section class="pd-panel-strong p-5">
                        <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Appearance & Style') }}</p>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-[0.82rem]">
                            @foreach ([
                                ['Height',              $profile->height],
                                ['Weight',              $profile->weight],
                                ['Hair Color',          $profile->hair_color],
                                ['Eye Color',           $profile->eye_color],
                                ['Body Type',           $profile->body_type],
                                ['Tattoos & Piercings', $profile->has_tattoos_piercings],
                            ] as [$label, $value])
                                @if ($value)
                                    <div>
                                        <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">{{ $label }}</p>
                                        <p class="mt-0.5 text-boss-ivory/75">{{ $value }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Platforms & Equipment --}}
                @if ($profile->platforms || $profile->current_platforms || $profile->equipment)
                    <section class="pd-panel-strong p-5">
                        <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Platforms & Equipment') }}</p>
                        <div class="space-y-4">
                            @if ($profile->platforms)
                                <div>
                                    <p class="mb-2 text-[0.7rem] text-boss-ivory/40">{{ __('Platforms they\'d like to be on') }}</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($profile->platforms as $p)
                                            <span class="rounded-full bg-boss-gold/10 px-2.5 py-0.5 text-[0.7rem] text-boss-gold">{{ $p }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if ($profile->current_platforms)
                                <div>
                                    <p class="mb-1 text-[0.7rem] text-boss-ivory/40">{{ __('Current platforms and usernames') }}</p>
                                    <p class="text-[0.82rem] leading-relaxed text-boss-ivory/70">{{ $profile->current_platforms }}</p>
                                </div>
                            @endif
                            @if ($profile->equipment)
                                <div>
                                    <p class="mb-2 text-[0.7rem] text-boss-ivory/40">{{ __('Equipment') }}</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($profile->equipment as $item)
                                            <span class="rounded-full bg-white/[0.05] px-2.5 py-0.5 text-[0.7rem] text-boss-ivory/60">{{ $item }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                {{-- Work Preferences --}}
                @if ($profile->work_interests || $profile->comfort_levels || $profile->custom_content_ok || $profile->worn_items_ok)
                    <section class="pd-panel-strong p-5">
                        <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Work Preferences') }}</p>
                        <div class="space-y-4">
                            @if ($profile->work_interests)
                                <div>
                                    <p class="mb-2 text-[0.7rem] text-boss-ivory/40">{{ __('Work Interests') }}</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($profile->work_interests as $item)
                                            <span class="rounded-full bg-boss-gold/10 px-2.5 py-0.5 text-[0.7rem] text-boss-gold">{{ $item }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if ($profile->comfort_levels)
                                <div>
                                    <p class="mb-2 text-[0.7rem] text-boss-ivory/40">{{ __('Comfort Levels') }}</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($profile->comfort_levels as $item)
                                            <span class="rounded-full bg-white/[0.05] px-2.5 py-0.5 text-[0.7rem] text-boss-ivory/60">{{ $item }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div class="flex flex-wrap gap-6 text-[0.82rem]">
                                @if ($profile->custom_content_ok)
                                    <div>
                                        <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Custom Content</p>
                                        <p class="mt-0.5 text-boss-ivory/75">{{ $profile->custom_content_ok }}</p>
                                    </div>
                                @endif
                                @if ($profile->worn_items_ok)
                                    <div>
                                        <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Worn Items</p>
                                        <p class="mt-0.5 text-boss-ivory/75">{{ $profile->worn_items_ok }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Availability & Schedule --}}
                @if ($profile->availability || $profile->weekly_availability || $profile->availability_preference || $profile->has_private_space || $profile->goals || $profile->experience_notes)
                    <section class="pd-panel-strong p-5">
                        <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Availability & Schedule') }}</p>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-[0.82rem]">
                                @foreach ([
                                    ['Weekly Hours',      $profile->weekly_availability],
                                    ['Preferred Schedule',$profile->availability_preference],
                                    ['Private Space',     $profile->has_private_space],
                                ] as [$label, $value])
                                    @if ($value)
                                        <div>
                                            <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">{{ $label }}</p>
                                            <p class="mt-0.5 text-boss-ivory/75">{{ $value }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            @foreach ([
                                ['Availability',     $profile->availability],
                                ['Goals',            $profile->goals],
                                ['Experience Notes', $profile->experience_notes],
                            ] as [$label, $value])
                                @if ($value)
                                    <div>
                                        <p class="mb-1 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">{{ $label }}</p>
                                        <p class="whitespace-pre-line text-[0.82rem] leading-relaxed text-boss-ivory/70">{{ $value }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Payout Information --}}
                @if ($profile->payout_methods || $profile->payout_method_other || $profile->payout_country)
                    <section class="pd-panel-strong p-5">
                        <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Payout Information') }}</p>
                        <div class="space-y-3">
                            @if ($profile->payout_methods)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($profile->payout_methods as $method)
                                        <span class="rounded-full bg-boss-gold/10 px-2.5 py-0.5 text-[0.7rem] text-boss-gold">{{ $method }}</span>
                                    @endforeach
                                    @if ($profile->payout_method_other)
                                        <span class="rounded-full bg-boss-gold/10 px-2.5 py-0.5 text-[0.7rem] text-boss-gold">{{ $profile->payout_method_other }}</span>
                                    @endif
                                </div>
                            @endif
                            @if ($profile->payout_country)
                                <div class="text-[0.82rem]">
                                    <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Country / Region</p>
                                    <p class="mt-0.5 text-boss-ivory/75">{{ $profile->payout_country }}</p>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                {{-- Extra Details --}}
                @if ($profile->model_vibe || $profile->anything_else)
                    <section class="pd-panel-strong p-5">
                        <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Extra Details') }}</p>
                        <div class="space-y-4">
                            @if ($profile->model_vibe)
                                <div>
                                    <p class="mb-1 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Vibe / Niche</p>
                                    <p class="whitespace-pre-line text-[0.82rem] leading-relaxed text-boss-ivory/70">{{ $profile->model_vibe }}</p>
                                </div>
                            @endif
                            @if ($profile->anything_else)
                                <div>
                                    <p class="mb-1 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Anything Else</p>
                                    <p class="whitespace-pre-line text-[0.82rem] leading-relaxed text-boss-ivory/70">{{ $profile->anything_else }}</p>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                @if (! empty($customOnboardingAnswers))
                    <section class="pd-panel-strong p-5">
                        <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Custom Onboarding Answers') }}</p>
                        <div class="space-y-3">
                            @foreach ($customOnboardingAnswers as $answer)
                                <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">{{ $answer['label'] }}</p>
                                        @if ($answer['archived'])
                                            <span class="rounded-full bg-white/[0.05] px-2 py-0.5 text-[0.58rem] text-boss-ivory/30">{{ __('Archived') }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 whitespace-pre-line text-[0.82rem] leading-relaxed text-boss-ivory/72">{{ $answer['answer'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Fetishes & Kinks Checklist --}}
                @if ($profile->fetishes_checklist && count($profile->fetishes_checklist) > 0)
                    <section class="pd-panel-strong p-5" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="flex w-full items-center justify-between">
                            <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Fetishes & Kinks Checklist') }}</p>
                            <svg class="h-4 w-4 shrink-0 text-boss-ivory/30 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
                        </button>
                        <div x-show="open" x-cloak x-transition class="mt-4 space-y-1 max-h-96 overflow-y-auto pr-1">
                            @foreach ($profile->fetishes_checklist as $item => $answer)
                                <div class="flex items-center justify-between gap-3 rounded-md px-3 py-1.5 text-[0.76rem]
                                    {{ $answer === 'Yes' ? 'bg-emerald-400/[0.08] text-boss-ivory/72' : ($answer === 'No' ? 'bg-red-400/[0.08] text-boss-ivory/50' : 'bg-boss-gold/[0.07] text-boss-ivory/65') }}">
                                    <span>{{ $item }}</span>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[0.6rem] font-semibold
                                        {{ $answer === 'Yes' ? 'bg-emerald-400/15 text-emerald-300' : ($answer === 'No' ? 'bg-red-400/15 text-red-300' : 'bg-boss-gold/15 text-boss-gold') }}">
                                        {{ $answer }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Discord --}}
                @if ($profile->discord_username || $profile->discord_user_id || $profile->community_invite_url)
                    <section class="pd-panel-strong p-5">
                        <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Discord') }}</p>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-[0.82rem]">
                            @foreach ([
                                ['Discord Username',$profile->discord_username],
                                ['Discord User ID', $profile->discord_user_id],
                                ['Last Invite Link', $profile->community_invite_url],
                            ] as [$label, $value])
                                @if ($value)
                                    <div>
                                        <p class="text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">{{ $label }}</p>
                                        <p class="mt-0.5 break-all text-boss-ivory/75">{{ $value }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif

            </div>

            {{-- ════════════════ RIGHT COLUMN ════════════════ --}}
            <div class="space-y-5">

                {{-- Admin Actions --}}
                <section class="pd-panel-strong p-5">
                    <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Admin Actions') }}</p>

                    <div class="space-y-2">
                        @if ($profile->application?->canResendApprovalEmail())
                            <form action="{{ route('admin.applications.resend-approval-email', $profile->application) }}" method="POST" class="rounded-xl border border-green-300/15 bg-green-400/[0.06] p-3">
                                @csrf
                                <button type="submit" class="w-full rounded-xl border border-green-300/20 bg-green-400/10 px-4 py-2.5 text-sm font-semibold text-green-200 transition hover:bg-green-400/20">
                                    {{ __('Resend Application Approval Email') }}
                                </button>
                                <p class="mt-2 text-center text-[0.68rem] leading-relaxed text-green-100/45">
                                    {{ __('Creates a fresh temporary password and emails the login plus Model Information Form instructions again.') }}
                                </p>
                            </form>
                        @endif

                        {{-- Request verification --}}
                        @if ($profile->hasInformationForm() && ! $profile->isVerified() && $profile->verification_status !== \App\Models\ModelProfile::VERIFICATION_SUBMITTED)
                            <form action="{{ route('admin.onboarding.request-verification', $profile) }}" method="POST">
                                @csrf
                                <button type="submit" class="pd-btn-primary w-full text-sm">{{ __('Send Verification Request Email') }}</button>
                            </form>
                        @endif

                        {{-- Verify --}}
                        @if ($profile->canApproveVerification())
                            <form action="{{ route('admin.onboarding.verify', $profile) }}" method="POST" class="space-y-2">
                                @csrf
                                <textarea name="verification_notes" rows="2" class="pd-input w-full text-sm" placeholder="{{ __('Optional approval notes…') }}">{{ old('verification_notes', $profile->verification_notes) }}</textarea>
                                <button type="submit" class="w-full rounded-xl bg-green-500/20 px-4 py-2.5 text-sm font-medium text-green-300 transition hover:bg-green-500/30">{{ __('✓ Approve & Send Approval Email') }}</button>
                                <p class="text-center text-[0.68rem] leading-relaxed text-green-100/45">
                                    {{ __('Uses the existing verification documents. The member does not need to submit them again.') }}
                                </p>
                            </form>
                        @endif

                        {{-- Reject --}}
                        @if ($profile->hasVerificationSubmission() && ! $profile->isVerified())
                            <form action="{{ route('admin.onboarding.reject-verification', $profile) }}" method="POST" class="space-y-2">
                                @csrf
                                <textarea name="verification_notes" rows="2" class="pd-input w-full text-sm" placeholder="{{ __('Reason for resubmission (required)…') }}" required></textarea>
                                <button type="submit" class="w-full rounded-xl bg-red-500/15 px-4 py-2.5 text-sm font-medium text-red-300 transition hover:bg-red-500/25">{{ __('Request Resubmission') }}</button>
                            </form>
                        @endif

                        {{-- Community invite --}}
                        @if ($profile->isVerified() && ! $profile->community_role_assigned_at)
                            <form action="{{ route('admin.onboarding.community-invite', $profile) }}" method="POST" class="space-y-2">
                                @csrf
                                <label for="community_url" class="pd-label">{{ __('Discord invite link') }}</label>
                                <input
                                    id="community_url"
                                    name="community_url"
                                    type="url"
                                    value="{{ old('community_url', $profile->community_invite_url ?: config('paradise.community_url')) }}"
                                    placeholder="https://discord.gg/example"
                                    class="pd-input w-full text-sm"
                                    required
                                >
                                <p class="text-[0.72rem] leading-relaxed text-boss-ivory/35">{{ __('Paste the current Discord invite link before sending. If an old invite expires, paste a fresh one and resend it here.') }}</p>
                                <button type="submit" class="pd-btn-primary w-full text-sm">
                                    {{ $profile->community_invited_at ? __('Resend Discord Community Access Email') : __('Send Discord Community Access Email') }}
                                </button>
                            </form>
                        @endif

                        {{-- Mark role assigned --}}
                        @if ($profile->isVerified() && $profile->community_invited_at && ! $profile->community_role_assigned_at)
                            <form action="{{ route('admin.onboarding.community-role-assigned', $profile) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-2.5 text-sm font-medium text-boss-ivory/70 transition hover:bg-white/[0.07] hover:text-boss-ivory">{{ __('Mark Discord Role Assigned') }}</button>
                            </form>
                        @endif

                        @if ($profile->community_role_assigned_at)
                            <p class="py-2 text-center text-sm text-green-300/70">{{ __('Fully onboarded ✓') }}</p>
                        @endif
                    </div>
                    <form
                        action="{{ route('admin.models.destroy', $user) }}"
                        method="POST"
                        class="mt-5 border-t border-red-300/10 pt-4"
                        onsubmit="return confirm('{{ __('Delete this member account? This permanently removes their login, onboarding profile, uploaded files, course progress, and linked application.') }}');"
                    >
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="confirm_member_delete" value="1">
                        <button type="submit" class="w-full rounded-xl border border-red-300/15 bg-red-400/10 px-4 py-2.5 text-sm font-semibold text-red-200 transition hover:border-red-300/35 hover:bg-red-400/15">
                            {{ __('Delete member account') }}
                        </button>
                        <p class="mt-2 text-center text-[0.68rem] leading-relaxed text-boss-ivory/28">
                            {{ __('Permanent removal for this model account and its onboarding records.') }}
                        </p>
                    </form>
                </section>

                {{-- Login Access --}}
                <section class="pd-panel-strong p-5">
                    <p class="mb-3 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Login Access') }}</p>
                    <form action="{{ route('admin.models.login.update', $user) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="admin_member_name" class="pd-label">{{ __('Name') }}</label>
                            <input id="admin_member_name" name="name" type="text" value="{{ old('name', $user->name) }}" class="pd-input mt-1 w-full text-sm" required autocomplete="off">
                        </div>

                        <div>
                            <label for="admin_member_email" class="pd-label">{{ __('Login email') }}</label>
                            <input id="admin_member_email" name="email" type="email" value="{{ old('email', $user->email) }}" class="pd-input mt-1 w-full text-sm" required autocomplete="off">
                        </div>

                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
                            <div>
                                <label for="admin_member_password" class="pd-label">{{ __('New password') }}</label>
                                <input id="admin_member_password" name="password" type="password" class="pd-input mt-1 w-full text-sm" minlength="10" autocomplete="new-password" placeholder="{{ __('Leave blank to keep current') }}">
                            </div>
                            <div>
                                <label for="admin_member_password_confirmation" class="pd-label">{{ __('Confirm password') }}</label>
                                <input id="admin_member_password_confirmation" name="password_confirmation" type="password" class="pd-input mt-1 w-full text-sm" minlength="10" autocomplete="new-password">
                            </div>
                        </div>

                        <button type="submit" class="w-full rounded-xl border border-boss-gold/20 bg-boss-gold/[0.08] px-4 py-2.5 text-sm font-semibold text-boss-gold transition hover:bg-boss-gold/[0.14]">
                            {{ __('Save login details') }}
                        </button>
                        <p class="text-center text-[0.68rem] leading-relaxed text-boss-ivory/30">
                            {{ __('Use this if the member cannot receive password reset emails. Changing email or password signs out old sessions.') }}
                        </p>
                    </form>

                    <form action="{{ route('admin.models.password.generate', $user) }}" method="POST" class="mt-3 border-t border-white/[0.06] pt-3">
                        @csrf
                        <button type="submit" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-2.5 text-sm font-medium text-boss-ivory/70 transition hover:bg-white/[0.07] hover:text-boss-ivory">
                            {{ __('Generate temporary password') }}
                        </button>
                    </form>
                </section>

                {{-- Verification status --}}
                <section class="pd-panel-strong p-5">
                    <p class="mb-3 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Verification Status') }}</p>
                    <span class="rounded-full px-3 py-1 text-[0.72rem] font-medium
                        {{ $profile->isVerified() ? 'bg-green-400/10 text-green-300' : ($profile->verification_status === \App\Models\ModelProfile::VERIFICATION_SUBMITTED ? 'bg-boss-gold/10 text-boss-gold' : ($profile->verification_status === \App\Models\ModelProfile::VERIFICATION_REJECTED ? 'bg-red-400/10 text-red-300' : 'bg-white/[0.05] text-boss-ivory/40')) }}">
                        {{ $profile->verificationStatusLabel() }}
                    </span>
                    @if ($profile->verification_submitted_at)
                        <p class="mt-2 text-[0.7rem] text-boss-ivory/30">Submitted {{ $profile->verification_submitted_at->toFormattedDateString() }}</p>
                    @endif
                    @if ($profile->verification_reviewed_at)
                        <p class="text-[0.7rem] text-boss-ivory/30">
                            Reviewed {{ $profile->verification_reviewed_at->toFormattedDateString() }}
                            @if ($profile->verificationReviewer) by {{ $profile->verificationReviewer->name }}@endif
                        </p>
                    @endif
                    @if ($profile->verification_notes)
                        <div class="mt-3 rounded-xl border border-white/[0.06] bg-white/[0.03] p-3">
                            <p class="mb-1 text-[0.62rem] uppercase tracking-[0.1em] text-boss-ivory/28">Notes</p>
                            <p class="whitespace-pre-line text-[0.78rem] leading-relaxed text-boss-ivory/60">{{ $profile->verification_notes }}</p>
                        </div>
                    @endif
                </section>

                {{-- Verification Documents --}}
                @if ($profile->id_document_path || $profile->selfie_with_id_path || $profile->platform_codes_path)
                    <section class="pd-panel-strong p-5">
                        <p class="mb-4 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Verification Documents') }}</p>
                        <div class="space-y-2">
                            @foreach ([
                                ['id',     'Government ID',  '🪪'],
                                ['selfie', 'Selfie with ID', '🤳'],
                                ['codes',  'Platform Codes', '📸'],
                            ] as [$type, $docLabel, $icon])
                                @php $path = match($type) { 'id' => $profile->id_document_path, 'selfie' => $profile->selfie_with_id_path, 'codes' => $profile->platform_codes_path }; @endphp
                                @if ($path)
                                    <div class="flex items-center justify-between gap-3 rounded-xl border border-white/[0.06] bg-white/[0.025] px-4 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-base">{{ $icon }}</span>
                                            <p class="text-[0.8rem] font-medium text-boss-ivory/80">{{ $docLabel }}</p>
                                        </div>
                                        <div class="flex shrink-0 gap-2">
                                            <a href="{{ route('admin.onboarding.documents.view', [$profile, $type]) }}" target="_blank"
                                               class="rounded-lg border border-white/[0.08] bg-white/[0.04] px-3 py-1.5 text-[0.72rem] text-boss-ivory/60 transition hover:bg-white/[0.08] hover:text-boss-ivory">
                                                View
                                            </a>
                                            <a href="{{ route('admin.onboarding.documents.show', [$profile, $type]) }}"
                                               class="rounded-lg border border-boss-gold/20 bg-boss-gold/[0.07] px-3 py-1.5 text-[0.72rem] text-boss-gold transition hover:bg-boss-gold/[0.14]">
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2.5 rounded-xl border border-white/[0.04] bg-white/[0.01] px-4 py-3 opacity-40">
                                        <span class="text-base">{{ $icon }}</span>
                                        <p class="text-[0.78rem] text-boss-ivory/35">{{ $docLabel }} — not uploaded</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Verification Instructions --}}
                <section class="pd-panel-strong p-5">
                    <p class="mb-3 text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Custom Verification Instructions') }}</p>
                    <form action="{{ route('admin.onboarding.verification-instructions', $profile) }}" method="POST" class="space-y-2">
                        @csrf
                        <textarea name="verification_request_instructions" rows="3" class="pd-input w-full text-sm" placeholder="{{ __('Optional instructions shown to the member in the verification request email…') }}">{{ old('verification_request_instructions', $profile->verification_request_instructions) }}</textarea>
                        <button type="submit" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-2.5 text-sm font-medium text-boss-ivory/70 transition hover:bg-white/[0.07] hover:text-boss-ivory">{{ __('Save Instructions') }}</button>
                    </form>
                </section>

            </div>
        </div>

        {{-- Website Walkthrough Access --}}
        @if ($courses->count() > 0)
            @php
                $reviewCourseRequestId = (int) request()->query('course_request', 0);
            @endphp
            <section class="pd-onboarding-access-panel pd-panel-strong p-5">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Website Walkthrough Access') }}</p>
                        <p class="mt-1 text-[0.72rem] text-boss-ivory/30">{{ $courses->count() }} modules &nbsp;·&nbsp; {{ count($unlockedCourseIds) }} unlocked</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @foreach ($courses as $course)
                        @php
                            $isUnlocked = in_array((int) $course->id, $unlockedCourseIds, true);
                            $accessRequest = $accessRequestsByCourse->get($course->id);
                        @endphp
                        <div
                            class="pd-onboarding-access-card flex min-h-[13rem] flex-col overflow-hidden rounded-xl border transition
                                {{ $isUnlocked ? 'border-green-400/20 bg-green-400/[0.04]' : 'border-white/[0.06] bg-white/[0.02]' }}"
                            x-data="{ requestOpen: @js($accessRequest && (int) $accessRequest->id === $reviewCourseRequestId), showResubmit: false }"
                            @keydown.escape.window="requestOpen = false"
                        >
                            {{-- Card body --}}
                            <div class="flex flex-1 flex-col gap-2 p-4">
                                <div class="flex items-start justify-between gap-2">
                                    {{-- Status dot --}}
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $isUnlocked ? 'bg-green-400' : 'bg-white/20' }}"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="line-clamp-2 text-[0.8rem] font-medium leading-snug text-boss-ivory/85">{{ $course->title }}</p>
                                        @if ($course->displayPlatform())
                                            <p class="mt-0.5 text-[0.62rem] text-boss-ivory/28">{{ $course->displayPlatform() }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-auto space-y-2 pt-3">
                                    @if ($isUnlocked)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-400/12 px-2 py-0.5 text-[0.6rem] font-medium text-green-300">
                                            Unlocked
                                        </span>
                                    @elseif ($accessRequest)
                                        <span class="rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.6rem] text-boss-gold">{{ $accessRequest->statusLabel() }}</span>
                                    @else
                                        <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.6rem] text-boss-ivory/25">Locked</span>
                                    @endif

                                    @if ($accessRequest)
                                        <div class="flex flex-wrap gap-1.5">
                                            @if (filled($accessRequest->member_notes))
                                                <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.58rem] text-boss-ivory/35">Note</span>
                                            @endif
                                            @if ($accessRequest->proofFiles->isNotEmpty())
                                                <span class="rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.58rem] text-boss-gold">
                                                    {{ trans_choice(':count proof file|:count proof files', $accessRequest->proofFiles->count(), ['count' => $accessRequest->proofFiles->count()]) }}
                                                </span>
                                            @endif
                                            @if (filled($accessRequest->admin_notes))
                                                <span class="rounded-full bg-red-400/10 px-2 py-0.5 text-[0.58rem] text-red-200/70">Resubmission note</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Action footer --}}
                            <div class="space-y-2 border-t border-white/[0.04] bg-white/[0.015] px-3 py-2.5">
                                @if ($accessRequest)
                                    <button
                                        type="button"
                                        @click="requestOpen = true"
                                        class="w-full rounded-lg border border-boss-gold/20 bg-boss-gold/[0.07] py-1.5 text-[0.7rem] font-medium text-boss-gold transition hover:bg-boss-gold/[0.13]"
                                    >
                                        Review Request
                                    </button>
                                @elseif (! $isUnlocked && $profile->isVerified())
                                    <form action="{{ route('admin.onboarding.courses.unlock', [$profile, $course]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full rounded-lg bg-green-500/15 py-1.5 text-[0.7rem] font-medium text-green-300 transition hover:bg-green-500/25">
                                            Unlock Access
                                        </button>
                                    </form>
                                @elseif ($isUnlocked)
                                    <form action="{{ route('admin.onboarding.courses.lock', [$profile, $course]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full rounded-lg border border-white/[0.07] bg-white/[0.02] py-1.5 text-[0.7rem] text-boss-ivory/35 transition hover:border-red-400/20 hover:bg-red-400/[0.07] hover:text-red-300/70">
                                            Revoke Access
                                        </button>
                                    </form>
                                @else
                                    <p class="py-1 text-center text-[0.65rem] text-boss-ivory/20">Not yet requested</p>
                                @endif
                            </div>

                            @if ($accessRequest)
                                <div
                                    x-show="requestOpen"
                                    x-cloak
                                    class="fixed inset-0 z-[90] flex items-center justify-center bg-black/75 px-4 py-5 backdrop-blur-sm"
                                    role="dialog"
                                    aria-modal="true"
                                    @click.self="requestOpen = false"
                                >
                                    <div
                                        x-show="requestOpen"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="translate-y-4 scale-[0.98] opacity-0"
                                        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                                        x-transition:leave-end="translate-y-4 scale-[0.98] opacity-0"
                                        class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-boss-gold/20 bg-[#101014] shadow-2xl"
                                    >
                                        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-white/[0.06] px-5 py-4 md:px-6">
                                            <div>
                                                <p class="pd-kicker">Course Access Review</p>
                                                <h3 class="pd-heading mt-1 text-[1.35rem] leading-tight text-boss-ivory">{{ $course->title }}</h3>
                                                <p class="mt-1 text-[0.76rem] text-boss-ivory/38">
                                                    {{ $user->name }} &middot; {{ $accessRequest->statusLabel() }}
                                                </p>
                                            </div>
                                            <button type="button" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/[0.08] bg-white/[0.04] text-boss-ivory/50 transition hover:text-boss-ivory" @click="requestOpen = false" aria-label="Close request review">
                                                <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.7]"><path d="M4 4l8 8M12 4l-8 8"/></svg>
                                            </button>
                                        </div>

                                        <div class="flex-1 space-y-4 overflow-y-auto px-5 py-5 md:px-6">
                                            @if (filled($accessRequest->member_notes))
                                                <div class="rounded-xl border border-white/[0.07] bg-white/[0.025] p-4">
                                                    <p class="pd-kicker">Model Access Note</p>
                                                    <p class="mt-2 whitespace-pre-line text-[0.86rem] leading-relaxed text-boss-ivory/65">{{ $accessRequest->member_notes }}</p>
                                                </div>
                                            @else
                                                <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">
                                                    <p class="text-[0.82rem] text-boss-ivory/35">No access note was submitted.</p>
                                                </div>
                                            @endif

                                            @if ($accessRequest->proofFiles->isNotEmpty())
                                                <div class="rounded-xl border border-boss-gold/15 bg-boss-gold/[0.045] p-4">
                                                    <p class="pd-kicker">Course proof files</p>
                                                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                                        @foreach ($accessRequest->proofFiles as $file)
                                                            <div class="flex items-center justify-between gap-3 rounded-lg border border-white/[0.06] bg-black/10 px-3 py-2.5">
                                                                <div class="min-w-0">
                                                                    <p class="truncate text-[0.78rem] text-boss-ivory/70">{{ $file->original_name }}</p>
                                                                    @if ($file->displaySize())
                                                                        <p class="text-[0.64rem] text-boss-ivory/30">{{ $file->displaySize() }}</p>
                                                                    @endif
                                                                </div>
                                                                <div class="flex shrink-0 gap-2">
                                                                    <a href="{{ route('admin.onboarding.courses.proofs.view', [$profile, $course, $file]) }}" target="_blank" rel="noopener" class="text-[0.68rem] text-boss-gold transition hover:text-boss-gold-light">View</a>
                                                                    <a href="{{ route('admin.onboarding.courses.proofs.show', [$profile, $course, $file]) }}" class="text-[0.68rem] text-boss-ivory/35 transition hover:text-boss-ivory/65">Download</a>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">
                                                    <p class="pd-kicker">Course proof files</p>
                                                    <p class="mt-2 text-[0.82rem] text-boss-ivory/35">No course-specific files were uploaded yet.</p>
                                                </div>
                                            @endif

                                            @if (filled($accessRequest->admin_notes))
                                                <div class="rounded-xl border border-red-400/20 bg-red-400/[0.08] p-4">
                                                    <p class="text-[0.64rem] uppercase tracking-[0.14em] text-red-200/65">Kayla Resubmission Note</p>
                                                    <p class="mt-2 whitespace-pre-line text-[0.86rem] leading-relaxed text-red-100/78">{{ $accessRequest->admin_notes }}</p>
                                                </div>
                                            @endif

                                            <div class="rounded-xl border border-white/[0.07] bg-white/[0.025] p-4">
                                                <p class="pd-kicker">Actions</p>
                                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                                    @if (! $isUnlocked && $profile->isVerified())
                                                        <form action="{{ route('admin.onboarding.courses.unlock', [$profile, $course]) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="w-full rounded-lg bg-green-500/15 px-3 py-2 text-[0.78rem] font-medium text-green-300 transition hover:bg-green-500/25">
                                                                Approve & Unlock
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if ($isUnlocked)
                                                        <form action="{{ route('admin.onboarding.courses.lock', [$profile, $course]) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="w-full rounded-lg border border-red-400/20 bg-red-400/[0.07] px-3 py-2 text-[0.78rem] font-medium text-red-300/80 transition hover:bg-red-400/[0.12]">
                                                                Revoke Access
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if (! $isUnlocked)
                                                        <button
                                                            type="button"
                                                            @click="showResubmit = ! showResubmit"
                                                            class="w-full rounded-lg border border-white/[0.08] bg-white/[0.04] px-3 py-2 text-[0.78rem] font-medium text-boss-ivory/55 transition hover:border-boss-gold/20 hover:text-boss-gold sm:col-span-2"
                                                        >
                                                            Request Resubmission
                                                        </button>
                                                    @endif
                                                </div>

                                                @if (! $isUnlocked)
                                                    <form x-show="showResubmit" x-cloak x-transition action="{{ route('admin.onboarding.courses.resubmission', [$profile, $course]) }}" method="POST" class="mt-3 space-y-2">
                                                        @csrf
                                                        <textarea name="admin_notes" rows="3" class="pd-input w-full text-sm" placeholder="Tell the model what to fix or upload next." required></textarea>
                                                        <button type="submit" class="rounded-lg bg-boss-gold/10 px-3 py-2 text-[0.78rem] font-medium text-boss-gold transition hover:bg-boss-gold/20">
                                                            Send Resubmission Request
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</x-admin-layout>
