<x-admin-layout>
    <div class="mx-auto max-w-full space-y-6 text-boss-ivory">
        <header>
            <p class="pd-kicker">{{ __('Members') }}</p>
            <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.6rem)]">{{ __('Member Progress') }}</h1>
        </header>

        <div class="overflow-hidden rounded-2xl border border-white/[0.06] bg-boss-panel-strong">
            <div class="overflow-x-auto">
                <table class="pd-table min-w-full">
                    <thead>
                        <tr>
                            <th class="sticky left-0 z-10 bg-boss-panel-strong text-left">{{ __('Member') }}</th>
                            @foreach ($courses as $course)
                                <th class="min-w-[150px] text-left">
                                    <div>{{ $course->title }}</div>
                                    @if ($course->platform_label)
                                        <div class="mt-1 normal-case tracking-normal text-boss-ivory/25">{{ $course->platform_label }}</div>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($models as $model)
                            <tr>
                                <td class="sticky left-0 z-10 bg-boss-panel-strong">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-boss-gold/20 bg-boss-gold/10 font-display text-[0.72rem] text-boss-gold">
                                            {{ strtoupper(substr($model->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-boss-ivory">{{ $model->name }}</div>
                                            <div class="text-xs text-boss-ivory/35">{{ $model->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                @foreach ($courses as $course)
                                    @php($pct = $matrix[$model->id][$course->id] ?? 0)
                                    <td>
                                        <div class="mb-2 flex items-center gap-2">
                                            <span class="font-display text-[1rem] text-boss-gold">{{ $pct }}%</span>
                                            @if ($course->lessons_count === 0)
                                                <span class="text-xs text-boss-ivory/25">{{ __('No lessons') }}</span>
                                            @endif
                                        </div>
                                        <div class="pd-progress-track">
                                            <div class="pd-progress-bar" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $courses->count() + 1 }}" class="py-12 text-center text-boss-ivory/35">
                                    {{ __('No member accounts yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
