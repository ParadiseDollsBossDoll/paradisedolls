<x-admin-layout>
    <div class="mx-auto max-w-6xl space-y-6 text-boss-ivory">
        <header class="flex items-start justify-between gap-4">
            <div>
                <p class="pd-kicker">{{ __('Website') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(1.8rem,4vw,2.5rem)]">{{ __('Success Stories') }}</h1>
                <p class="mt-2 text-[0.82rem] text-boss-ivory/35">{{ __('Manage testimonials shown on the public site.') }}</p>
            </div>
            <a href="{{ route('admin.testimonials.create') }}" class="pd-btn-primary shrink-0">{{ __('New Story') }}</a>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($testimonials as $testimonial)
                @php
                    $statusClass = $testimonial->is_published
                        ? 'bg-green-400/10 text-green-300'
                        : ($testimonial->submitted_by ? 'bg-amber-400/10 text-amber-200' : 'bg-white/[0.04] text-boss-ivory/35');
                @endphp
                <article class="overflow-hidden rounded-sm border border-white/[0.06] bg-[#141419]">
                    <div class="h-1 bg-gradient-to-r from-boss-gold to-boss-gold-light"></div>
                    <div class="grid gap-4 p-4 sm:grid-cols-[120px_1fr]">
                        <div class="aspect-square overflow-hidden rounded-sm bg-boss-panel">
                            <img src="{{ $testimonial->displayImage() }}" alt="" class="h-full w-full object-cover">
                        </div>
                        <div class="min-w-0">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2 py-0.5 text-[0.62rem] {{ $statusClass }}">
                                    {{ $testimonial->statusLabel() }}
                                </span>
                                @if ($testimonial->result_label)
                                    <span class="rounded-full bg-boss-gold/10 px-2 py-0.5 text-[0.62rem] text-boss-gold">{{ $testimonial->result_label }}</span>
                                @endif
                                <span class="text-[0.62rem] text-boss-ivory/25">{{ __('Order') }} {{ $testimonial->sort_order }}</span>
                            </div>
                            <h2 class="pd-heading truncate text-[1.15rem] text-boss-ivory">{{ $testimonial->headline }}</h2>
                            <p class="mt-1 text-[0.78rem] text-boss-ivory/38">{{ $testimonial->name }}{{ $testimonial->location ? ' - '.$testimonial->location : '' }}</p>
                            @if ($testimonial->submitter)
                                <p class="mt-1 text-[0.68rem] text-boss-ivory/28">{{ __('Submitted by') }} {{ $testimonial->submitter->name }} &middot; {{ $testimonial->created_at->diffForHumans() }}</p>
                            @elseif ($testimonial->approver)
                                <p class="mt-1 text-[0.68rem] text-boss-ivory/28">{{ __('Approved by') }} {{ $testimonial->approver->name }} &middot; {{ $testimonial->approved_at?->diffForHumans() }}</p>
                            @endif
                            <p class="mt-3 line-clamp-2 text-[0.78rem] leading-relaxed text-boss-ivory/42">{{ $testimonial->quote }}</p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                @if ($testimonial->is_published)
                                    <form method="POST" action="{{ route('admin.testimonials.visibility', $testimonial) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_published" value="0">
                                        <x-secondary-button type="submit">{{ __('Unpublish') }}</x-secondary-button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.testimonials.approve', $testimonial) }}">
                                        @csrf
                                        <x-secondary-button type="submit">{{ $testimonial->submitted_by ? __('Approve') : __('Publish') }}</x-secondary-button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="pd-btn-secondary">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('{{ __('Delete this success story?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-sm border border-white/[0.06] bg-[#141419] py-20 text-center lg:col-span-2">
                    <p class="text-[0.9rem] text-boss-ivory/35">{{ __('No success stories yet.') }}</p>
                    <a href="{{ route('admin.testimonials.create') }}" class="mt-4 inline-flex text-[0.82rem] text-boss-gold hover:text-boss-gold-light">{{ __('Create the first story') }} -></a>
                </div>
            @endforelse
        </div>

        <div class="px-2">{{ $testimonials->links() }}</div>
    </div>
</x-admin-layout>
