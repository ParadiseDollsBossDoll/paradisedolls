@props(['scrolling' => false])
@php($photos = collect(marketing_items('success_stories.review_photos'))->filter(fn ($photo) => !empty($photo['image'])))
<div @class(['pd-review-marquee' => $scrolling])>
    @if ($scrolling)
        <label class="mb-4 flex items-center justify-end gap-2 text-sm text-boss-dark/70">
            <input type="checkbox" class="pd-review-pause h-4 w-4 accent-boss-pink">
            {{ __('Pause animation') }}
        </label>
    @endif
    <div @class(['pd-review-viewport' => $scrolling])>
    <div @class(['pd-review-gallery' => !$scrolling, 'pd-review-moving-track' => $scrolling])>
    @foreach (range(1, $scrolling ? 2 : 1) as $copy)
    <div @class(['pd-review-group' => $scrolling, 'contents' => !$scrolling]) @if($copy > 1) aria-hidden="true" inert @endif>
    @foreach ($photos as $photo)
        <a href="{{ \App\Support\MarketingContent::imageUrl($photo['image']) }}" target="_blank" rel="noopener" class="pd-review-photo" aria-label="{{ __('Open full-size review: :name', ['name' => $photo['title'] ?? '']) }}">
            <img src="{{ \App\Support\MarketingContent::imageUrl($photo['image']) }}" alt="{{ __('Review from :name', ['name' => $photo['title'] ?? '']) }}" loading="lazy" decoding="async">
        </a>
    @endforeach
    </div>
    @endforeach
    </div>
    </div>
</div>
