<x-admin-layout>
    <div
        class="mx-auto max-w-7xl space-y-6 text-boss-ivory"
        x-data="{ open: false, selected: null }"
        @keydown.escape.window="open = false"
    >

        {{-- ── Backdrop ──────────────────────────────────────────── --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm"
            @click="open = false"
        ></div>

        {{-- ── Slide-over panel ──────────────────────────────────── --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 z-50 flex w-full flex-col border-l border-white/[0.06] bg-[#0d0e14] shadow-2xl sm:max-w-2xl"
        >
            <template x-if="selected">
                <div class="flex h-full flex-col">

                    {{-- Panel header --}}
                    <div class="flex shrink-0 items-center justify-between border-b border-white/[0.06] px-6 py-5">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-boss-gold/25 bg-boss-gold/10 font-display text-base text-boss-gold"
                                x-text="selected.name.charAt(0).toUpperCase()"
                            ></div>
                            <div>
                                <h2 class="font-display text-lg font-semibold text-boss-ivory" x-text="selected.name"></h2>
                                <p class="text-sm text-boss-ivory/45" x-text="selected.email"></p>
                            </div>
                        </div>
                        <button
                            @click="open = false"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/[0.06] text-boss-ivory/50 transition hover:border-white/[0.12] hover:text-boss-ivory"
                            aria-label="Close"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Scrollable body --}}
                    <div class="flex-1 space-y-5 overflow-y-auto px-6 py-6">

                        {{-- Status + date --}}
                        <div class="flex flex-wrap items-center gap-3">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-medium capitalize"
                                :class="selected.status === 'pending'
                                    ? 'bg-boss-gold/10 text-boss-gold'
                                    : (selected.status === 'approved'
                                        ? 'bg-green-400/10 text-green-300'
                                        : 'bg-red-400/10 text-red-300')"
                                x-text="selected.status"
                            ></span>
                            <span class="text-xs text-boss-ivory/38">
                                Submitted <span x-text="selected.created_at"></span>
                            </span>
                            <template x-if="selected.reviewed_by">
                                <span class="text-xs text-boss-ivory/38">
                                    &middot; Reviewed by <span x-text="selected.reviewed_by"></span>
                                </span>
                            </template>
                        </div>

                        {{-- Contact details --}}
                        <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-5">
                            <p class="mb-4 text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/35">Contact Information</p>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-[0.65rem] uppercase tracking-[0.12em] text-boss-ivory/28">Email</p>
                                    <p class="mt-1 break-all text-sm text-boss-ivory/80" x-text="selected.email"></p>
                                </div>
                                <template x-if="selected.phone">
                                    <div>
                                        <p class="text-[0.65rem] uppercase tracking-[0.12em] text-boss-ivory/28">Phone</p>
                                        <p class="mt-1 text-sm text-boss-ivory/80" x-text="selected.phone"></p>
                                    </div>
                                </template>
                                <template x-if="selected.social_handle">
                                    <div>
                                        <p class="text-[0.65rem] uppercase tracking-[0.12em] text-boss-ivory/28">Social Handle</p>
                                        <p class="mt-1 text-sm text-boss-ivory/80" x-text="selected.social_handle"></p>
                                    </div>
                                </template>
                                <template x-if="selected.experience_level">
                                    <div>
                                        <p class="text-[0.65rem] uppercase tracking-[0.12em] text-boss-ivory/28">Experience Level</p>
                                        <p class="mt-1 text-sm capitalize text-boss-ivory/80" x-text="selected.experience_level"></p>
                                    </div>
                                </template>
                            </div>
                            <template x-if="selected.age_confirmed">
                                <div class="mt-4 flex items-center gap-2 border-t border-white/[0.04] pt-4">
                                    <svg class="h-3.5 w-3.5 text-boss-gold" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs text-boss-gold/80">18+ age confirmed</span>
                                </div>
                            </template>
                        </div>

                        {{-- Message / motivation --}}
                        <template x-if="selected.message">
                            <div class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-5">
                                <p class="mb-3 text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/35">Their Message</p>
                                <p class="whitespace-pre-line text-sm leading-relaxed text-boss-ivory/70" x-text="selected.message"></p>
                            </div>
                        </template>

                        {{-- Photos --}}
                        <template x-if="selected.photo_view_urls && selected.photo_view_urls.length > 0">
                            <div>
                                <p class="mb-3 text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/35">
                                    Photos (<span x-text="selected.photo_view_urls.length"></span>)
                                </p>
                                <div class="grid grid-cols-2 gap-3">
                                    <template x-for="(url, i) in selected.photo_view_urls" :key="i">
                                        <div class="overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.025]">
                                            <a :href="url" target="_blank" rel="noopener" class="block">
                                                <img
                                                    :src="url"
                                                    :alt="'Photo ' + (i + 1)"
                                                    class="h-48 w-full object-cover transition hover:opacity-90"
                                                    loading="lazy"
                                                />
                                            </a>
                                            <div class="flex items-center justify-between px-3 py-2">
                                                <span class="text-[0.65rem] text-boss-ivory/35" x-text="'Photo ' + (i + 1)"></span>
                                                <a
                                                    :href="selected.photo_download_urls[i]"
                                                    class="text-[0.65rem] text-boss-gold transition hover:text-boss-gold-light"
                                                    download
                                                >Download</a>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                    </div>

                    {{-- Footer actions --}}
                    <div class="shrink-0 border-t border-white/[0.06] px-6 py-4">
                        <template x-if="selected.status === 'pending'">
                            <div class="flex gap-3">
                                <form :action="selected.approve_url" method="POST" class="flex-1">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-boss-gold px-4 py-2.5 text-sm font-semibold text-boss-ink transition hover:opacity-90"
                                    >
                                        Approve Application
                                    </button>
                                </form>
                                <form :action="selected.reject_url" method="POST">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="rounded-xl border border-red-400/30 bg-red-400/10 px-4 py-2.5 text-sm font-semibold text-red-300 transition hover:bg-red-400/20"
                                    >
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </template>
                        <template x-if="selected.status !== 'pending'">
                            <p class="text-center text-sm text-boss-ivory/35">
                                Application
                                <span class="capitalize text-boss-ivory/60" x-text="selected.status"></span>
                                <template x-if="selected.reviewed_by">
                                    <span> &middot; Reviewed by <span class="text-boss-ivory/50" x-text="selected.reviewed_by"></span></span>
                                </template>
                            </p>
                        </template>
                    </div>

                </div>
            </template>
        </div>

        {{-- ── Page header ───────────────────────────────────────── --}}
        <header>
            <p class="pd-kicker">{{ __('Recruitment') }}</p>
            <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.6rem)]">{{ __('Applications') }}</h1>
        </header>

        {{-- ── Flash messages ────────────────────────────────────── --}}
        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">
                {{ session('status') }}
            </div>
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

        {{-- ── Applications table ────────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-panel-strong">
            <div class="overflow-x-auto">
                <table class="pd-table min-w-full">
                    <thead>
                        <tr>
                            <th class="text-left">{{ __('Applicant') }}</th>
                            <th class="text-left">{{ __('Status') }}</th>
                            <th class="text-left">{{ __('Submitted') }}</th>
                            <th class="text-right text-boss-ivory/30">{{ __('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            @php
                                $appData = [
                                    'id'                   => $application->id,
                                    'name'                 => $application->name,
                                    'email'                => $application->email,
                                    'phone'                => $application->phone,
                                    'message'              => $application->message,
                                    'experience_level'     => $application->experience_level,
                                    'social_handle'        => $application->social_handle,
                                    'age_confirmed'        => $application->age_confirmed,
                                    'status'               => $application->status,
                                    'photo_view_urls'      => collect($application->photo_paths ?? [])
                                        ->keys()
                                        ->map(fn ($i) => route('admin.applications.photos.view', [$application, $i]))
                                        ->values()
                                        ->all(),
                                    'photo_download_urls'  => collect($application->photo_paths ?? [])
                                        ->keys()
                                        ->map(fn ($i) => route('admin.applications.photos.show', [$application, $i]))
                                        ->values()
                                        ->all(),
                                    'approve_url'          => route('admin.applications.approve', $application),
                                    'reject_url'           => route('admin.applications.reject', $application),
                                    'reviewed_by'          => $application->reviewer?->name,
                                    'reviewed_at'          => $application->reviewed_at?->toFormattedDateString(),
                                    'created_at'           => $application->created_at->toFormattedDateString(),
                                ];
                            @endphp
                            <tr
                                class="cursor-pointer transition hover:bg-white/[0.025]"
                                @click="selected = {{ Js::from($appData) }}; open = true"
                            >
                                <td class="align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-boss-gold/20 bg-boss-gold/10 font-display text-[0.72rem] text-boss-gold">
                                            {{ strtoupper(substr($application->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-boss-ivory">{{ $application->name }}</div>
                                            <div class="text-[0.75rem] text-boss-ivory/38">{{ $application->email }}</div>
                                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                                @if ($application->experience_level)
                                                    <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.62rem] text-boss-ivory/35">{{ $application->experience_level }}</span>
                                                @endif
                                                @if ($application->social_handle)
                                                    <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.62rem] text-boss-ivory/35">{{ $application->social_handle }}</span>
                                                @endif
                                                @if ($application->age_confirmed)
                                                    <span class="rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.62rem] text-boss-gold">18+</span>
                                                @endif
                                                @if ($application->photo_paths)
                                                    <span class="rounded-full bg-white/[0.04] px-2 py-0.5 text-[0.62rem] text-boss-ivory/35">
                                                        {{ count($application->photo_paths) }} {{ count($application->photo_paths) === 1 ? 'photo' : 'photos' }}
                                                    </span>
                                                @endif
                                                @if ($application->profile)
                                                    <span class="rounded-full bg-green-400/10 px-2 py-0.5 text-[0.62rem] text-green-300">Onboarded</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <span class="rounded-full px-2.5 py-1 text-[0.65rem] capitalize {{ $application->status === \App\Models\ModelApplication::STATUS_PENDING ? 'bg-boss-gold/10 text-boss-gold' : ($application->status === \App\Models\ModelApplication::STATUS_APPROVED ? 'bg-green-400/10 text-green-300' : 'bg-red-400/10 text-red-300') }}">
                                        {{ __($application->status) }}
                                    </span>
                                </td>
                                <td class="align-middle text-[0.78rem] text-boss-ivory/42">
                                    {{ $application->created_at->toFormattedDateString() }}
                                </td>
                                <td class="align-middle text-right">
                                    <span class="inline-flex items-center gap-1 text-[0.72rem] text-boss-ivory/30 transition group-hover:text-boss-ivory/60">
                                        View
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
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
