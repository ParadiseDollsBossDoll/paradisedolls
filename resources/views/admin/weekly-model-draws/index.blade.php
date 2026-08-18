<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Admin') }}</p>
                <h1 class="font-display text-3xl text-boss-ivory">{{ __('Weekly Model Draws') }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-boss-ivory/55">{{ __('Create weekly earning draws, prepare qualified names for Wheel of Names, and keep winner/prize history after the live OBS spin.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-[1400px] space-y-6 text-boss-ivory">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">{{ session('status') }}</div>
        @endif

        <section class="rounded-lg border border-white/[0.08] bg-boss-panel p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="pd-label">{{ __('New weekly draw') }}</p>
                    <p class="mt-1 text-sm text-boss-ivory/45">{{ __('Set the earning week and optional qualification threshold. Add entries and prizes on the next screen.') }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.weekly-draws.store') }}" class="mt-5 grid gap-3 md:grid-cols-[minmax(0,1.2fr)_150px_150px_150px] md:items-end">
                @csrf
                <label class="min-w-0"><span class="pd-label">{{ __('Title') }}</span><input class="pd-input mt-2" name="title" value="{{ old('title', __('Weekly Model Draw')) }}" required></label>
                <label><span class="pd-label">{{ __('Week starts') }}</span><input class="pd-input mt-2" type="date" name="week_start" value="{{ old('week_start', $defaultWeekStart) }}" required></label>
                <label><span class="pd-label">{{ __('Week ends') }}</span><input class="pd-input mt-2" type="date" name="week_end" value="{{ old('week_end', $defaultWeekEnd) }}" required></label>
                <label><span class="pd-label">{{ __('Qualify at $') }}</span><input class="pd-input mt-2" type="number" name="qualification_threshold" step="0.01" min="0" value="{{ old('qualification_threshold') }}" placeholder="{{ __('Optional') }}"></label>
                <label class="md:col-span-3"><span class="pd-label">{{ __('Notes') }}</span><input class="pd-input mt-2" name="notes" value="{{ old('notes') }}" placeholder="{{ __('Optional internal notes') }}"></label>
                <button class="pd-btn-primary h-[43px] rounded-lg px-4 text-xs">{{ __('Create draw') }}</button>
            </form>
            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-red-400/20 bg-red-400/10 px-4 py-3 text-sm text-red-100">
                    {{ $errors->first() }}
                </div>
            @endif
        </section>

        <section class="rounded-lg border border-white/[0.08] bg-boss-panel">
            <div class="border-b border-white/[0.06] p-5">
                <p class="pd-label">{{ __('Draw history') }}</p>
            </div>
            <div class="divide-y divide-white/[0.06]">
                @forelse ($draws as $draw)
                    <a href="{{ route('admin.weekly-draws.show', $draw) }}" class="grid gap-4 p-5 transition hover:bg-white/[0.03] lg:grid-cols-[minmax(0,1fr)_repeat(4,auto)] lg:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="truncate font-display text-xl">{{ $draw->title }}</h2>
                                <span class="rounded-full bg-white/[0.06] px-2.5 py-1 text-[0.62rem] uppercase tracking-[0.18em] text-boss-ivory/55">{{ $draw->status }}</span>
                            </div>
                            <p class="mt-1 text-sm text-boss-ivory/40">{{ $draw->week_start->format('M j') }} - {{ $draw->week_end->format('M j, Y') }}</p>
                            @if ($draw->winnerEntry)
                                <p class="mt-2 text-sm text-boss-gold">{{ __('Winner: :name', ['name' => $draw->winnerEntry->model_name]) }}@if($draw->winningPrize) · {{ $draw->winningPrize->name }}@endif</p>
                            @endif
                        </div>
                        <div class="text-sm text-boss-ivory/55"><span class="block text-lg font-semibold text-boss-ivory">{{ $draw->entries_count }}</span>{{ __('entries') }}</div>
                        <div class="text-sm text-boss-ivory/55"><span class="block text-lg font-semibold text-boss-ivory">{{ $draw->qualified_entries_count }}</span>{{ __('qualified') }}</div>
                        <div class="text-sm text-boss-ivory/55"><span class="block text-lg font-semibold text-boss-ivory">{{ $draw->prizes_count }}</span>{{ __('prizes') }}</div>
                        <div class="text-right text-xs font-semibold uppercase tracking-[0.18em] text-boss-gold">{{ __('Open') }}</div>
                    </a>
                @empty
                    <div class="p-10 text-center text-sm text-boss-ivory/40">{{ __('No weekly draws created yet.') }}</div>
                @endforelse
            </div>
            @if ($draws->hasPages())
                <div class="border-t border-white/[0.06] p-5">{{ $draws->links() }}</div>
            @endif
        </section>
    </div>
</x-admin-layout>
