<x-admin-layout>
    <div class="mx-auto max-w-7xl space-y-6 text-boss-ivory">
        <header>
            <p class="pd-kicker">{{ __('Recruitment') }}</p>
            <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.6rem)]">{{ __('Applications') }}</h1>
        </header>

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
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-panel-strong">
            <div class="overflow-x-auto">
                <table class="pd-table min-w-full">
                    <thead>
                        <tr>
                            <th class="text-left">{{ __('Applicant') }}</th>
                            <th class="text-left">{{ __('Status') }}</th>
                            <th class="text-left">{{ __('Submitted') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            <tr>
                                <td class="align-top">
                                    <div class="flex gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-boss-gold/20 bg-boss-gold/10 font-display text-[0.76rem] text-boss-gold">
                                            {{ strtoupper(substr($application->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-boss-ivory">{{ $application->name }}</div>
                                            <div class="text-boss-ivory/38">{{ $application->email }}</div>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @if ($application->experience_level)
                                                    <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.65rem] text-boss-ivory/35">{{ __('Experience') }}: {{ $application->experience_level }}</span>
                                                @endif
                                                @if ($application->social_handle)
                                                    <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.65rem] text-boss-ivory/35">{{ $application->social_handle }}</span>
                                                @endif
                                                @if ($application->age_confirmed)
                                                    <span class="rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.65rem] text-boss-gold">{{ __('18+ confirmed') }}</span>
                                                @endif
                                                @if ($application->profile)
                                                    <span class="rounded-full bg-green-400/10 px-2 py-0.5 text-[0.65rem] text-green-300">{{ __('Onboarding created') }}</span>
                                                @endif
                                            </div>
                                            @if ($application->photo_paths)
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    @foreach ($application->photo_paths as $index => $path)
                                                        <a href="{{ route('admin.applications.photos.show', [$application, $index]) }}" class="text-[0.7rem] text-boss-gold hover:text-boss-gold-light">
                                                            {{ __('Photo :n', ['n' => $loop->iteration]) }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if ($application->phone)
                                                <div class="mt-2 text-boss-ivory/38">{{ $application->phone }}</div>
                                            @endif
                                            @if ($application->message)
                                                <p class="mt-3 max-w-2xl whitespace-pre-line text-boss-ivory/48">{{ $application->message }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="align-top">
                                    <span class="rounded-full px-2.5 py-1 text-[0.65rem] capitalize {{ $application->status === \App\Models\ModelApplication::STATUS_PENDING ? 'bg-boss-gold/10 text-boss-gold' : ($application->status === \App\Models\ModelApplication::STATUS_APPROVED ? 'bg-green-400/10 text-green-300' : 'bg-red-400/10 text-red-300') }}">
                                        {{ __($application->status) }}
                                    </span>
                                </td>
                                <td class="align-top text-boss-ivory/42">{{ $application->created_at->toFormattedDateString() }}</td>
                                <td class="align-top text-right">
                                    @if ($application->status === \App\Models\ModelApplication::STATUS_PENDING)
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.applications.approve', $application) }}">
                                                @csrf
                                                <x-secondary-button type="submit">{{ __('Approve') }}</x-secondary-button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.applications.reject', $application) }}">
                                                @csrf
                                                <x-danger-button type="submit">{{ __('Reject') }}</x-danger-button>
                                            </form>
                                        </div>
                                    @elseif ($application->reviewer)
                                        <div class="text-xs text-boss-ivory/35">{{ __('Reviewed by :name', ['name' => $application->reviewer->name]) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-boss-ivory/35">{{ __('No applications yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-2">{{ $applications->links() }}</div>
    </div>
</x-admin-layout>
