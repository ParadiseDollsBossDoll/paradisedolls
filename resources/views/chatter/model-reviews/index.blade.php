<x-chatter-layout title="Weekly Model Reviews">
    @php
        $submittedLookup = collect($submittedModelIds)->mapWithKeys(fn ($id) => [(int) $id => true]);
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 text-boss-ivory">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Mandatory Weekly Review') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,3rem)]">{{ __('Weekly Model Reviews') }}</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-boss-ivory/45">
                    {{ __('Submit one review for each model assigned to your weekly review list. Deadline: every Sunday before 11:59 PM UK time.') }}
                </p>
            </div>
            @if ($models->isNotEmpty())
                <a href="{{ route('chatter.model-reviews.create') }}" class="pd-btn-primary justify-center">{{ __('Submit Review') }}</a>
            @endif
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>
        @endif

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="pd-panel p-5">
                <p class="pd-kicker text-boss-ivory/35">{{ __('Week Ending') }}</p>
                <p class="mt-3 font-display text-3xl">{{ $weekEnding->format('j M') }}</p>
                <p class="mt-1 text-xs text-boss-ivory/35">{{ __('Europe/London') }}</p>
            </div>
            <div class="pd-panel p-5">
                <p class="pd-kicker text-boss-ivory/35">{{ __('Assigned Models') }}</p>
                <p class="mt-3 font-display text-3xl">{{ $models->count() }}</p>
            </div>
            <div class="pd-panel p-5">
                <p class="pd-kicker text-boss-ivory/35">{{ __('Submitted') }}</p>
                <p class="mt-3 font-display text-3xl text-emerald-300">{{ $submittedThisWeek }}</p>
            </div>
            <div class="pd-panel p-5">
                <p class="pd-kicker text-boss-ivory/35">{{ __('Still To Complete') }}</p>
                <p class="mt-3 font-display text-3xl text-boss-gold">{{ $remainingThisWeek }}</p>
            </div>
        </section>

        <section class="pd-panel overflow-hidden">
            <div class="border-b border-white/[0.06] p-5">
                <p class="pd-kicker text-boss-ivory/35">{{ __('This Week') }}</p>
                <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Assigned model review checklist') }}</h2>
            </div>
            <div class="divide-y divide-white/[0.06]">
                @forelse ($models as $model)
                    @php($isSubmitted = $submittedLookup->has((int) $model->id))
                    <article class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-semibold text-boss-ivory">{{ $model->name }}</p>
                            <p class="mt-1 truncate text-xs text-boss-ivory/35">{{ $model->email }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($isSubmitted)
                                <span class="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-200">{{ __('Submitted') }}</span>
                            @else
                                <span class="rounded-full border border-boss-gold/20 bg-boss-gold/10 px-3 py-1 text-xs font-semibold text-boss-gold">{{ __('Required') }}</span>
                                <a href="{{ route('chatter.model-reviews.create', ['model_id' => $model->id]) }}" class="pd-btn-secondary justify-center">{{ __('Complete') }}</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="p-10 text-center text-sm leading-6 text-boss-ivory/45">
                        {{ __('No models have been assigned to your chatter account yet. Ask an admin to assign the models you worked with before Sunday.') }}
                    </div>
                @endforelse
            </div>
        </section>

        <section class="pd-panel overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-white/[0.06] p-5">
                <div>
                    <p class="pd-kicker text-boss-ivory/35">{{ __('History') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Submitted reviews') }}</h2>
                </div>
                <p class="text-xs text-boss-ivory/30">{{ $reviews->total() }} {{ __('records') }}</p>
            </div>
            <div class="divide-y divide-white/[0.06]">
                @forelse ($reviews as $review)
                    <article class="grid gap-4 p-5 md:grid-cols-[1fr_150px_160px] md:items-center">
                        <div class="min-w-0">
                            <p class="font-semibold">{{ $review->model?->name ?? __('Deleted model') }}</p>
                            <p class="mt-1 truncate text-xs text-boss-ivory/35">{{ $review->model?->email }}</p>
                        </div>
                        <div>
                            <p class="pd-kicker text-boss-ivory/25">{{ __('Week Ending') }}</p>
                            <p class="mt-1 text-sm text-boss-ivory/70">{{ $review->week_ending?->format('M j, Y') }}</p>
                        </div>
                        <div>
                            <p class="pd-kicker text-boss-ivory/25">{{ __('Rating') }}</p>
                            <p class="mt-1 font-semibold text-boss-gold">{{ $review->overall_rating }}/5 - {{ $review->overallRatingLabel() }}</p>
                        </div>
                    </article>
                @empty
                    <div class="p-10 text-center text-sm text-boss-ivory/40">{{ __('No weekly model reviews submitted yet.') }}</div>
                @endforelse
            </div>
            @if ($reviews->hasPages())
                <div class="border-t border-white/[0.06] p-5">{{ $reviews->links() }}</div>
            @endif
        </section>
    </div>
</x-chatter-layout>
