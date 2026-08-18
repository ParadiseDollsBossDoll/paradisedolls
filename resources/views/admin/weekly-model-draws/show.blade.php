<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Weekly Model Draw') }}</p>
                <h1 class="font-display text-3xl text-boss-ivory">{{ $draw->title }}</h1>
                <p class="mt-2 text-sm text-boss-ivory/55">{{ $draw->week_start->format('M j') }} - {{ $draw->week_end->format('M j, Y') }} · {{ __('Live spin happens externally in OBS using Wheel of Names.') }}</p>
            </div>
            <a href="{{ route('admin.weekly-draws.index') }}" class="pd-btn-secondary rounded-lg px-4 py-2.5 text-xs">{{ __('Draw history') }}</a>
        </div>
    </x-slot>

    @php
        $money = fn (?int $cents): string => $cents === null ? '' : number_format($cents / 100, 2);
        $statusLabels = [
            \App\Models\WeeklyModelDraw::STATUS_DRAFT => __('Draft'),
            \App\Models\WeeklyModelDraw::STATUS_READY => __('Ready for live draw'),
            \App\Models\WeeklyModelDraw::STATUS_COMPLETED => __('Completed'),
        ];
    @endphp

    <div class="mx-auto max-w-[1500px] space-y-6 text-boss-ivory">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg border border-red-400/20 bg-red-400/10 px-4 py-3 text-sm text-red-100">{{ $errors->first() }}</div>
        @endif

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-white/[0.08] bg-boss-panel p-4"><p class="pd-label">{{ __('Entries') }}</p><p class="mt-2 font-display text-3xl">{{ $stats['entries'] }}</p></div>
            <div class="rounded-lg border border-white/[0.08] bg-boss-panel p-4"><p class="pd-label">{{ __('Qualified') }}</p><p class="mt-2 font-display text-3xl text-boss-gold">{{ $stats['qualified'] }}</p></div>
            <div class="rounded-lg border border-white/[0.08] bg-boss-panel p-4"><p class="pd-label">{{ __('Total earnings') }}</p><p class="mt-2 font-display text-3xl">${{ $money($stats['earnings_cents']) }}</p></div>
            <div class="rounded-lg border border-white/[0.08] bg-boss-panel p-4"><p class="pd-label">{{ __('Status') }}</p><p class="mt-2 font-display text-3xl">{{ $statusLabels[$draw->status] ?? str($draw->status)->title() }}</p></div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(360px,0.8fr)]">
            <section class="space-y-6">
                <div class="rounded-lg border border-white/[0.08] bg-boss-panel p-5">
                    <p class="pd-label">{{ __('Draw settings') }}</p>
                    <form method="POST" action="{{ route('admin.weekly-draws.update', $draw) }}" class="mt-4 grid gap-3 md:grid-cols-2">
                        @csrf @method('PATCH')
                        <label><span class="pd-label">{{ __('Title') }}</span><input class="pd-input mt-2" name="title" value="{{ old('title', $draw->title) }}" required></label>
                        <label><span class="pd-label">{{ __('Status') }}</span><select class="pd-input mt-2" name="status">@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('status', $draw->status) === $value)>{{ $label }}</option>@endforeach</select></label>
                        <label><span class="pd-label">{{ __('Week starts') }}</span><input class="pd-input mt-2" type="date" name="week_start" value="{{ old('week_start', $draw->week_start->toDateString()) }}" required></label>
                        <label><span class="pd-label">{{ __('Week ends') }}</span><input class="pd-input mt-2" type="date" name="week_end" value="{{ old('week_end', $draw->week_end->toDateString()) }}" required></label>
                        <label><span class="pd-label">{{ __('Qualify at $') }}</span><input class="pd-input mt-2" type="number" step="0.01" min="0" name="qualification_threshold" value="{{ old('qualification_threshold', $money($draw->qualification_threshold_cents)) }}" placeholder="{{ __('Optional') }}"></label>
                        <label><span class="pd-label">{{ __('Recording link') }}</span><input class="pd-input mt-2" type="url" name="recording_url" value="{{ old('recording_url', $draw->recording_url) }}" placeholder="https://..."></label>
                        <label class="md:col-span-2"><span class="pd-label">{{ __('Notes') }}</span><textarea class="pd-input mt-2" name="notes" rows="3">{{ old('notes', $draw->notes) }}</textarea></label>
                        <div class="md:col-span-2 flex justify-end"><button class="pd-btn-primary rounded-lg px-4 py-2.5 text-xs">{{ __('Save settings') }}</button></div>
                    </form>
                </div>

                <div class="rounded-lg border border-white/[0.08] bg-boss-panel p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="pd-label">{{ __('Model earnings') }}</p>
                            <p class="mt-1 text-sm text-boss-ivory/45">{{ __('Add one model at a time, or paste imported earnings below. Qualified entries are exported for Wheel of Names.') }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.weekly-draws.entries.store', $draw) }}" class="mt-5 grid gap-3 rounded-lg border border-dashed border-boss-gold/20 p-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_110px_120px] lg:items-end">
                        @csrf
                        <label><span class="pd-label">{{ __('Existing model') }}</span><select class="pd-input mt-2" name="model_id"><option value="">{{ __('Manual name') }}</option>@foreach($modelOptions as $model)<option value="{{ $model->id }}">{{ $model->name }} - {{ $model->email }}</option>@endforeach</select></label>
                        <label><span class="pd-label">{{ __('Manual name') }}</span><input class="pd-input mt-2" name="model_name" placeholder="{{ __('Only needed without model') }}"></label>
                        <label><span class="pd-label">{{ __('Earnings $') }}</span><input class="pd-input mt-2" type="number" step="0.01" min="0" name="earnings" required></label>
                        <label class="flex items-center gap-2 pb-3 text-xs text-boss-ivory/55"><input type="hidden" name="is_qualified" value="0"><input type="checkbox" name="is_qualified" value="1"> {{ __('Qualified') }}</label>
                        <label class="lg:col-span-3"><span class="pd-label">{{ __('Note') }}</span><input class="pd-input mt-2" name="qualification_note" placeholder="{{ __('Optional') }}"></label>
                        <button class="pd-btn-primary h-[43px] rounded-lg px-3 text-xs">{{ __('Add earnings') }}</button>
                    </form>

                    <form method="POST" action="{{ route('admin.weekly-draws.entries.import', $draw) }}" class="mt-4 rounded-lg border border-white/[0.06] bg-white/[0.02] p-3">
                        @csrf
                        <label><span class="pd-label">{{ __('Import earnings') }}</span><textarea class="pd-input mt-2 font-mono text-xs" name="import_text" rows="5" placeholder="Name,email@example.com,125.50,yes,optional note"></textarea></label>
                        <div class="mt-3 flex flex-col gap-2 text-xs text-boss-ivory/40 sm:flex-row sm:items-center sm:justify-between">
                            <span>{{ __('CSV columns: name, email, earnings, qualified yes/no, note. Header row is optional.') }}</span>
                            <button class="pd-btn-secondary rounded-lg px-3 py-2 text-xs">{{ __('Import') }}</button>
                        </div>
                    </form>

                    <div class="mt-5 space-y-3">
                        @forelse($draw->entries as $entry)
                            <form method="POST" action="{{ route('admin.weekly-draws.entries.update', [$draw, $entry]) }}" class="grid gap-3 rounded-lg border border-white/[0.06] bg-white/[0.02] p-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_110px_110px_auto] lg:items-end">
                                @csrf @method('PATCH')
                                <label><span class="pd-label">{{ __('Model') }}</span><select class="pd-input mt-2" name="model_id"><option value="">{{ __('Manual entry') }}</option>@foreach($modelOptions as $model)<option value="{{ $model->id }}" @selected($entry->user_id === $model->id)>{{ $model->name }} - {{ $model->email }}</option>@endforeach</select></label>
                                <label><span class="pd-label">{{ __('Name') }}</span><input class="pd-input mt-2" name="model_name" value="{{ $entry->model_name }}" required></label>
                                <label><span class="pd-label">{{ __('Earnings $') }}</span><input class="pd-input mt-2" type="number" step="0.01" min="0" name="earnings" value="{{ $money($entry->earnings_cents) }}" required></label>
                                <label class="flex items-center gap-2 pb-3 text-xs text-boss-ivory/55"><input type="hidden" name="is_qualified" value="0"><input type="checkbox" name="is_qualified" value="1" @checked($entry->is_qualified)> {{ __('Qualified') }}</label>
                                <button class="pd-btn-secondary h-[43px] rounded-lg px-3 text-xs">{{ __('Save') }}</button>
                                <label class="lg:col-span-4"><span class="pd-label">{{ __('Note') }}</span><input class="pd-input mt-2" name="qualification_note" value="{{ $entry->qualification_note }}"></label>
                            </form>
                            <form method="POST" action="{{ route('admin.weekly-draws.entries.destroy', [$draw, $entry]) }}" class="-mt-2 flex justify-end">@csrf @method('DELETE')<button class="text-xs text-red-200/80 hover:text-red-100">{{ __('Remove :name', ['name' => $entry->model_name]) }}</button></form>
                        @empty
                            <p class="rounded-lg border border-white/[0.06] bg-white/[0.02] p-5 text-center text-sm text-boss-ivory/40">{{ __('No model earnings added yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <div class="rounded-lg border border-white/[0.08] bg-boss-panel p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="pd-label">{{ __('Wheel of Names export') }}</p>
                            <p class="mt-1 text-sm text-boss-ivory/45">{{ __('Copy these names into Wheel of Names for the OBS live spin.') }}</p>
                        </div>
                        <a href="{{ route('admin.weekly-draws.qualified.export', $draw) }}" class="pd-btn-secondary rounded-lg px-3 py-2 text-xs">{{ __('Export .txt') }}</a>
                    </div>
                    <textarea id="qualified-names" class="pd-input mt-4 min-h-48 font-mono text-sm" readonly>{{ $qualifiedNames }}</textarea>
                    <button type="button" class="pd-btn-primary mt-3 w-full rounded-lg px-3 py-2.5 text-xs" onclick="navigator.clipboard.writeText(document.getElementById('qualified-names').value)">{{ __('Copy qualified names') }}</button>
                </div>

                <div class="rounded-lg border border-white/[0.08] bg-boss-panel p-5">
                    <p class="pd-label">{{ __('Weekly prizes') }}</p>
                    <form method="POST" action="{{ route('admin.weekly-draws.prizes.store', $draw) }}" class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_100px] sm:items-end">
                        @csrf
                        <label><span class="pd-label">{{ __('Prize') }}</span><input class="pd-input mt-2" name="name" required></label>
                        <label><span class="pd-label">{{ __('Value $') }}</span><input class="pd-input mt-2" type="number" step="0.01" min="0" name="value"></label>
                        <button class="pd-btn-primary rounded-lg px-3 py-2.5 text-xs sm:col-span-2">{{ __('Add prize') }}</button>
                    </form>
                    <div class="mt-4 space-y-2">
                        @forelse($draw->prizes as $prize)
                            <form method="POST" action="{{ route('admin.weekly-draws.prizes.update', [$draw, $prize]) }}" class="grid gap-2 rounded-lg border border-white/[0.06] bg-white/[0.02] p-3 sm:grid-cols-[minmax(0,1fr)_90px_70px_auto] sm:items-end">
                                @csrf @method('PATCH')
                                <label><span class="pd-label">{{ __('Name') }}</span><input class="pd-input mt-1" name="name" value="{{ $prize->name }}" required></label>
                                <label><span class="pd-label">{{ __('Value') }}</span><input class="pd-input mt-1" type="number" step="0.01" min="0" name="value" value="{{ $money($prize->value_cents) }}"></label>
                                <label><span class="pd-label">{{ __('Order') }}</span><input class="pd-input mt-1" type="number" min="0" name="sort_order" value="{{ $prize->sort_order }}" required></label>
                                <button class="pd-btn-secondary h-[39px] rounded-lg px-3 text-xs">{{ __('Save') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.weekly-draws.prizes.destroy', [$draw, $prize]) }}" class="-mt-1 flex justify-end">@csrf @method('DELETE')<button class="text-xs text-red-200/80 hover:text-red-100">{{ __('Remove prize') }}</button></form>
                        @empty
                            <p class="rounded-lg border border-white/[0.06] bg-white/[0.02] p-3 text-sm text-boss-ivory/40">{{ __('No prizes added yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg border border-white/[0.08] bg-boss-panel p-5">
                    <p class="pd-label">{{ __('Live draw result') }}</p>
                    @if($draw->winnerEntry)
                        <div class="mt-3 rounded-lg border border-boss-gold/20 bg-boss-gold/10 p-3">
                            <p class="text-sm text-boss-ivory/55">{{ __('Winner') }}</p>
                            <p class="mt-1 font-display text-2xl text-boss-gold">{{ $draw->winnerEntry->model_name }}</p>
                            @if($draw->winningPrize)<p class="mt-1 text-sm text-boss-ivory/55">{{ $draw->winningPrize->name }}</p>@endif
                            @if($draw->recording_url)<a class="mt-3 inline-block text-sm font-semibold text-boss-gold" href="{{ $draw->recording_url }}" target="_blank" rel="noopener">{{ __('Open recording') }}</a>@endif
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.weekly-draws.complete', $draw) }}" class="mt-4 space-y-3">
                        @csrf
                        <label><span class="pd-label">{{ __('Winner') }}</span><select class="pd-input mt-2" name="winner_entry_id" required><option value="">{{ __('Select qualified model') }}</option>@foreach($draw->entries->where('is_qualified', true) as $entry)<option value="{{ $entry->id }}" @selected($draw->winner_entry_id === $entry->id)>{{ $entry->model_name }}</option>@endforeach</select></label>
                        <label><span class="pd-label">{{ __('Prize won') }}</span><select class="pd-input mt-2" name="winning_prize_id"><option value="">{{ __('No specific prize') }}</option>@foreach($draw->prizes as $prize)<option value="{{ $prize->id }}" @selected($draw->winning_prize_id === $prize->id)>{{ $prize->name }}</option>@endforeach</select></label>
                        <label><span class="pd-label">{{ __('Recording link') }}</span><input class="pd-input mt-2" type="url" name="recording_url" value="{{ $draw->recording_url }}" placeholder="https://..."></label>
                        <label><span class="pd-label">{{ __('Drawn at') }}</span><input class="pd-input mt-2" type="datetime-local" name="drawn_at" value="{{ $draw->drawn_at?->format('Y-m-d\TH:i') }}"></label>
                        <button class="pd-btn-primary w-full rounded-lg px-3 py-2.5 text-xs">{{ __('Save winner') }}</button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</x-admin-layout>
