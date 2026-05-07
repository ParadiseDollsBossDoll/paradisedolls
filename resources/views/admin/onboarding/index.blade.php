<x-admin-layout>
    <div class="mx-auto max-w-full space-y-6 text-boss-ivory">
        <header class="flex flex-col justify-between gap-4 xl:flex-row xl:items-end">
            <div>
                <p class="pd-kicker">{{ __('Onboarding') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.6rem)]">{{ __('Model Onboarding') }}</h1>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="grid grid-cols-2 gap-3 xl:grid-cols-6">
            @foreach ([
                [__('Members'), $stats['members']],
                [__('Info Forms'), $stats['information_submitted']],
                [__('Verification Review'), $stats['verification_submitted']],
                [__('Verified'), $stats['verified']],
                [__('Community Invites'), $stats['community_invited']],
                [__('Roles Assigned'), $stats['role_assigned']],
            ] as $stat)
                <div class="pd-stat">
                    <p class="font-display text-[2rem] leading-none text-boss-gold">{{ $stat[1] }}</p>
                    <p class="mt-3 text-[0.68rem] uppercase tracking-[0.08em] text-boss-ivory/50">{{ $stat[0] }}</p>
                </div>
            @endforeach
        </section>

        <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-panel-strong">
            <div class="overflow-x-auto">
                <table class="pd-table min-w-[1180px]">
                    <thead>
                        <tr>
                            <th class="text-left">{{ __('Member') }}</th>
                            <th class="text-left">{{ __('Information') }}</th>
                            <th class="text-left">{{ __('Verification') }}</th>
                            <th class="text-left">{{ __('Documents') }}</th>
                            <th class="text-left">{{ __('Community') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($models as $model)
                            @php($profile = $model->modelProfile)
                            <tr>
                                <td class="align-top">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-boss-gold/20 bg-boss-gold/10 font-display text-[0.72rem] text-boss-gold">
                                            {{ strtoupper(substr($model->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-boss-ivory">{{ $model->name }}</div>
                                            <div class="text-xs text-boss-ivory/35">{{ $model->email }}</div>
                                            @if ($profile?->stage_name)
                                                <div class="mt-1 text-xs text-boss-gold/80">{{ $profile->stage_name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="align-top">
                                    @if ($profile?->hasInformationForm())
                                        <span class="rounded-full bg-green-400/10 px-2.5 py-1 text-[0.65rem] text-green-300">{{ __('Submitted') }}</span>
                                        <div class="mt-2 text-xs text-boss-ivory/35">{{ $profile->information_submitted_at->toFormattedDateString() }}</div>
                                        @if ($profile->platforms)
                                            <div class="mt-2 flex max-w-xs flex-wrap gap-1">
                                                @foreach ($profile->platforms as $platform)
                                                    <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.6rem] text-boss-ivory/35">{{ $platform }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    @else
                                        <span class="rounded-full bg-white/[0.04] px-2.5 py-1 text-[0.65rem] text-boss-ivory/35">{{ __('Pending') }}</span>
                                    @endif
                                </td>
                                <td class="align-top">
                                    @if ($profile)
                                        <span class="rounded-full px-2.5 py-1 text-[0.65rem] {{ $profile->isVerified() ? 'bg-green-400/10 text-green-300' : ($profile->verification_status === \App\Models\ModelProfile::VERIFICATION_SUBMITTED ? 'bg-boss-gold/10 text-boss-gold' : ($profile->verification_status === \App\Models\ModelProfile::VERIFICATION_REJECTED ? 'bg-red-400/10 text-red-300' : 'bg-white/[0.04] text-boss-ivory/35')) }}">
                                            {{ $profile->verificationStatusLabel() }}
                                        </span>
                                        @if ($profile->verification_submitted_at)
                                            <div class="mt-2 text-xs text-boss-ivory/35">{{ $profile->verification_submitted_at->toFormattedDateString() }}</div>
                                        @endif
                                        @if ($profile->verification_notes)
                                            <p class="mt-2 max-w-xs whitespace-pre-line text-xs text-boss-ivory/35">{{ $profile->verification_notes }}</p>
                                        @endif
                                    @else
                                        <span class="rounded-full bg-white/[0.04] px-2.5 py-1 text-[0.65rem] text-boss-ivory/35">{{ __('No profile') }}</span>
                                    @endif
                                </td>
                                <td class="align-top">
                                    @if ($profile)
                                        <div class="flex flex-col items-start gap-2">
                                            @foreach ([
                                                'id' => [$profile->id_document_path, __('Valid ID')],
                                                'selfie' => [$profile->selfie_with_id_path, __('Selfie')],
                                                'codes' => [$profile->platform_codes_path, __('Codes')],
                                            ] as $key => [$path, $label])
                                                @if ($path)
                                                    <a href="{{ route('admin.onboarding.documents.show', [$profile, $key]) }}" class="text-[0.72rem] text-boss-gold hover:text-boss-gold-light">{{ $label }}</a>
                                                @else
                                                    <span class="text-[0.72rem] text-boss-ivory/24">{{ $label }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="align-top">
                                    @if ($profile?->community_invited_at)
                                        <span class="rounded-full bg-green-400/10 px-2.5 py-1 text-[0.65rem] text-green-300">{{ __('Invited') }}</span>
                                        <div class="mt-2 text-xs text-boss-ivory/35">{{ $profile->community_invited_at->toFormattedDateString() }}</div>
                                        @if ($profile->community_role_assigned_at)
                                            <div class="mt-2 rounded-full bg-boss-gold/10 px-2.5 py-1 text-[0.65rem] text-boss-gold">{{ __('Role assigned') }}</div>
                                            <div class="mt-1 text-xs text-boss-ivory/35">{{ $profile->community_role_assigned_at->toFormattedDateString() }}</div>
                                        @endif
                                    @else
                                        <span class="rounded-full bg-white/[0.04] px-2.5 py-1 text-[0.65rem] text-boss-ivory/35">{{ __('Not sent') }}</span>
                                    @endif
                                    @if ($profile?->discord_username || $profile?->discord_user_id)
                                        <div class="mt-3 max-w-[13rem] text-xs text-boss-ivory/35">
                                            @if ($profile->discord_username)
                                                <div>{{ __('Discord') }}: {{ $profile->discord_username }}</div>
                                            @endif
                                            @if ($profile->discord_user_id)
                                                <div>{{ __('ID') }}: {{ $profile->discord_user_id }}</div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="align-top text-right">
                                    @if ($profile)
                                        <div class="flex flex-col items-end gap-2">
                                            @if ($profile->hasInformationForm() && ! $profile->isVerified() && $profile->verification_status !== \App\Models\ModelProfile::VERIFICATION_SUBMITTED)
                                                <form method="POST" action="{{ route('admin.onboarding.request-verification', $profile) }}">
                                                    @csrf
                                                    <x-secondary-button type="submit">{{ __('Request verification') }}</x-secondary-button>
                                                </form>
                                            @endif

                                            @if ($profile->verification_status === \App\Models\ModelProfile::VERIFICATION_SUBMITTED)
                                                <form method="POST" action="{{ route('admin.onboarding.verify', $profile) }}">
                                                    @csrf
                                                    <x-secondary-button type="submit">{{ __('Approve account') }}</x-secondary-button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.onboarding.reject-verification', $profile) }}" class="w-56 space-y-2">
                                                    @csrf
                                                    <textarea name="verification_notes" rows="2" placeholder="{{ __('Resubmission note') }}" class="pd-input text-xs" required></textarea>
                                                    <x-danger-button type="submit">{{ __('Reject verification') }}</x-danger-button>
                                                </form>
                                            @endif

                                            @if ($profile->isVerified() && ! $profile->community_invited_at)
                                                <form method="POST" action="{{ route('admin.onboarding.community-invite', $profile) }}">
                                                    @csrf
                                                    <x-secondary-button type="submit">{{ __('Send community access') }}</x-secondary-button>
                                                </form>
                                            @endif

                                            @if ($profile->community_invited_at && ! $profile->community_role_assigned_at)
                                                <form method="POST" action="{{ route('admin.onboarding.community-role-assigned', $profile) }}">
                                                    @csrf
                                                    <x-secondary-button type="submit">{{ __('Mark role assigned') }}</x-secondary-button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-boss-ivory/35">{{ __('No member accounts yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-2">{{ $models->links() }}</div>
    </div>
</x-admin-layout>
