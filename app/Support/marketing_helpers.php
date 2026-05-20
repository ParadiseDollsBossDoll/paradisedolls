<?php

use App\Support\MarketingContent;

if (! function_exists('marketing_content')) {
    function marketing_content(string $key, ?string $fallback = ''): string
    {
        return MarketingContent::text($key, $fallback);
    }
}

if (! function_exists('marketing_paragraphs')) {
    function marketing_paragraphs(string $key): array
    {
        return MarketingContent::paragraphs($key);
    }
}

if (! function_exists('marketing_items')) {
    function marketing_items(string $key): array
    {
        return MarketingContent::items($key);
    }
}

if (! function_exists('marketing_image')) {
    function marketing_image(string $key): string
    {
        return MarketingContent::image($key);
    }
}

if (! function_exists('marketing_link')) {
    function marketing_link(string $key, string $fallback = '#'): string
    {
        return MarketingContent::link($key, $fallback);
    }
}
