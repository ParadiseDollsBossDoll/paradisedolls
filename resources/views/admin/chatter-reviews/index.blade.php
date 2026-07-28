<x-admin-layout>
    @php
        $statusOptions = [
            '' => __('All statuses'),
            \App\Models\ChatterPerformanceReview::STATUS_SUBMITTED => __('Submitted'),
            \App\Models\ChatterPerformanceReview::STATUS_REVIEWED => __('Reviewed'),
            \App\Models\ChatterPerformanceReview::STATUS_FOLLOW_UP => __('Follow-up needed'),
            \App\Models\ChatterPerformanceReview::STATUS_CLOSED => __('Closed'),
        ];
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 text-boss-ivory">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Confidential Feedback') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2rem,4vw,2.7rem)]">{{ __('Chatter Reviews') }}</h1>
                <p class="mt-2 max-w-3xl text-[0.86rem] leading-relaxed text-boss-ivory/40">{{ __('Private first-month chatter performance reviews submitted by models. These are for Paradise Dolls management only.') }}</p>
            </div>
            <button type="button" class="pd-btn-secondary shrink-0" data-copy-review-link="{{ $memberReviewLink }}">{{ __('Copy member review link') }}</button>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.chatter-reviews.index') }}" class="pd-panel grid gap-3 p-4 md:grid-cols-[1fr_220px_auto] md:items-end">
            <div>
                <x-input-label for="search" :value="__('Search reviews')" />
                <x-text-input id="search" name="search" class="mt-2" :value="$search" placeholder="{{ __('Model, chatter, or email') }}" />
            </div>
            <div>
                <x-input-label for="status" :value="__('Status')" />
                <select id="status" name="status" class="pd-input mt-2">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-primary-button class="justify-center">{{ __('Apply') }}</x-primary-button>
        </form>

        <section class="overflow-hidden rounded-sm border border-white/[0.06] bg-[#141419]">
            <div class="flex items-center justify-between gap-3 border-b border-white/[0.06] p-5">
                <div>
                    <p class="pd-kicker text-boss-ivory/35">{{ __('Review Inbox') }}</p>
                    <h2 class="pd-heading mt-2 text-[1.35rem] text-boss-ivory">{{ __('Submitted reviews') }}</h2>
                </div>
                <p class="text-[0.75rem] text-boss-ivory/30">{{ $reviews->total() }} {{ __('records') }}</p>
            </div>

            <div class="divide-y divide-white/[0.06]">
                @forelse ($reviews as $review)
                    @php
                        $displayModel = $review->is_anonymous ? __('Anonymous model') : ($review->model_name ?: $review->member?->name);
                    @endphp
                    <article class="grid gap-4 p-5 lg:grid-cols-[1.1fr_1fr_140px_140px_auto] lg:items-center">
                        <div class="min-w-0">
                            <p class="text-[0.98rem] font-semibold text-boss-ivory">{{ $displayModel }}</p>
                            <p class="mt-1 truncate text-[0.76rem] text-boss-ivory/35">{{ $review->is_anonymous ? __('Identity hidden by request') : $review->member?->email }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="pd-kicker text-boss-ivory/25">{{ __('Chatter') }}</p>
                            <p class="mt-1 truncate text-[0.9rem] text-boss-ivory/70">{{ $review->chatter_name ?: $review->chatter?->name ?: __('Not specified') }}</p>
                        </div>
                        <div>
                            <p class="pd-kicker text-boss-ivory/25">{{ __('Average') }}</p>
                            <p class="mt-1 text-[1.1rem] font-semibold text-boss-gold">{{ number_format((float) $review->average_score, 2) }}/5</p>
                        </div>
                        <div>
                            <span class="inline-flex rounded-full bg-boss-gold/10 px-3 py-1 text-[0.72rem] text-boss-gold">{{ $review->statusLabel() }}</span>
                            <p class="mt-2 text-[0.68rem] text-boss-ivory/30">{{ $review->created_at->format('M j, Y') }}</p>
                        </div>
                        <a href="{{ route('admin.chatter-reviews.show', $review) }}" class="pd-btn-secondary justify-center">{{ __('Review') }}</a>
                    </article>
                @empty
                    <div class="py-20 text-center">
                        <p class="text-[0.9rem] text-boss-ivory/35">{{ __('No chatter reviews have been submitted yet.') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="px-2">{{ $reviews->links() }}</div>
    </div>

    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-copy-review-link]');
            if (!button) return;

            navigator.clipboard?.writeText(button.dataset.copyReviewLink).then(function () {
                const original = button.textContent;
                button.textContent = @json(__('Copied'));
                setTimeout(function () { button.textContent = original; }, 1600);
            });
        });
    </script>
</x-admin-layout>
