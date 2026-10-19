<div class="pd-review-gallery">
    @foreach (collect(marketing_items('success_stories.review_photos'))->filter(fn ($photo) => !empty($photo['image'])) as $photo)
        <a href="{{ \App\Support\MarketingContent::imageUrl($photo['image']) }}" target="_blank" rel="noopener" class="pd-review-photo" aria-label="{{ __('Open full-size review: :name', ['name' => $photo['title'] ?? '']) }}">
            <img src="{{ \App\Support\MarketingContent::imageUrl($photo['image']) }}" alt="{{ __('Review from :name', ['name' => $photo['title'] ?? '']) }}" loading="lazy" decoding="async">
        </a>
    @endforeach
</div>
