<x-admin-layout>
    <div class="mx-auto max-w-7xl space-y-6 text-boss-ivory">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Chatter Feedback') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.7rem)]">{{ __('Model Reviews') }}</h1>
                <p class="mt-2 max-w-3xl text-[0.86rem] leading-relaxed text-boss-ivory/40">
                    {{ __('Weekly reviews submitted by chatters about the models they worked with. These are private for Paradise Dolls management.') }}
                </p>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.chatter-model-reviews.index') }}" class="pd-panel p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                <div class="min-w-0 flex-1">
                    <x-input-label for="search" :value="__('Search reviews')" />
                    <x-text-input id="search" name="search" class="mt-2 h-11" :value="$search" placeholder="{{ __('Chatter, model, or email') }}" />
                </div>
                <div class="lg:w-56">
                    <x-input-label for="week_ending" :value="__('Week ending')" />
                    <x-text-input id="week_ending" name="week_ending" type="date" class="mt-2 h-11" :value="$weekEnding" />
                </div>
                <x-primary-button class="h-11 w-full justify-center px-6 lg:w-32">{{ __('Apply') }}</x-primary-button>
            </div>
        </form>

        <section class="overflow-hidden rounded-sm border border-white/[0.06] bg-[#141419]">
            <div class="flex items-center justify-between gap-3 border-b border-white/[0.06] p-5">
                <div>
                    <p class="pd-kicker text-boss-ivory/35">{{ __('Review Inbox') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Weekly model reviews') }}</h2>
                </div>
                <p class="text-[0.75rem] text-boss-ivory/30">{{ $reviews->total() }} {{ __('records') }}</p>
            </div>

            <div class="divide-y divide-white/[0.06]">
                @forelse ($reviews as $review)
                    <article class="grid gap-4 p-5 lg:grid-cols-[1fr_1fr_150px_150px_auto] lg:items-center">
                        <div class="min-w-0">
                            <p class="pd-kicker text-boss-ivory/25">{{ __('Chatter') }}</p>
                            <p class="mt-1 truncate text-[0.95rem] font-semibold text-boss-ivory">{{ $review->chatter?->name ?? __('Deleted chatter') }}</p>
                            <p class="mt-1 truncate text-xs text-boss-ivory/35">{{ $review->chatter?->email }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="pd-kicker text-boss-ivory/25">{{ __('Model') }}</p>
                            <p class="mt-1 truncate text-[0.95rem] font-semibold text-boss-ivory">{{ $review->model?->name ?? __('Deleted model') }}</p>
                            <p class="mt-1 truncate text-xs text-boss-ivory/35">{{ $review->model?->email }}</p>
                        </div>
                        <div>
                            <p class="pd-kicker text-boss-ivory/25">{{ __('Week') }}</p>
                            <p class="mt-1 text-sm text-boss-ivory/70">{{ $review->week_ending?->format('M j, Y') }}</p>
                        </div>
                        <div>
                            <p class="pd-kicker text-boss-ivory/25">{{ __('Overall') }}</p>
                            <p class="mt-1 text-[1rem] font-semibold text-boss-gold">{{ $review->overall_rating }}/5</p>
                            <p class="mt-1 text-xs text-boss-ivory/35">{{ $review->submitted_at?->format('M j, Y') }}</p>
                        </div>
                        <a href="{{ route('admin.chatter-model-reviews.show', $review) }}" class="pd-btn-secondary justify-center">{{ __('Review') }}</a>
                    </article>
                @empty
                    <div class="py-20 text-center">
                        <p class="text-[0.9rem] text-boss-ivory/35">{{ __('No weekly model reviews have been submitted yet.') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="px-2">{{ $reviews->links() }}</div>
    </div>
</x-admin-layout>
