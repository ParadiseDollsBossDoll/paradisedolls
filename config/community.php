<?php

return [
    'performance' => [
        'enabled' => env('COMMUNITY_PERFORMANCE_ENABLED', false),
        'log_channel' => env('COMMUNITY_PERFORMANCE_LOG_CHANNEL', 'community-performance'),
        'slow_query_ms' => (int) env('COMMUNITY_SLOW_QUERY_MS', 150),
        'slow_request_ms' => (int) env('COMMUNITY_SLOW_REQUEST_MS', 800),
        'initial_message_page_size' => (int) env('COMMUNITY_INITIAL_MESSAGE_PAGE_SIZE', 15),
        'message_page_size' => (int) env('COMMUNITY_MESSAGE_PAGE_SIZE', 25),
        'message_sync_limit' => (int) env('COMMUNITY_MESSAGE_SYNC_LIMIT', 50),
        'read_mark_chunk_size' => (int) env('COMMUNITY_READ_MARK_CHUNK_SIZE', 250),
        'search_min_chars' => (int) env('COMMUNITY_SEARCH_MIN_CHARS', 2),
        'search_preview_limit' => (int) env('COMMUNITY_SEARCH_PREVIEW_LIMIT', 8),
        'image_preview_max_bytes' => (int) env('COMMUNITY_IMAGE_PREVIEW_MAX_BYTES', 4194304),
        'presence_cache_store' => env('COMMUNITY_PRESENCE_CACHE_STORE', 'file'),
        'presence_refresh_interval_ms' => (int) env('COMMUNITY_PRESENCE_REFRESH_INTERVAL_MS', 30000),
    ],
];
