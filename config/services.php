<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'bunny' => [
        'library_id' => env('BUNNY_LIBRARY_ID'),
        'api_key' => env('BUNNY_API_KEY'),
        'cdn_hostname' => env('BUNNY_CDN_HOSTNAME'),
        'upload_signature_ttl' => (int) env('BUNNY_UPLOAD_SIGNATURE_TTL', 86400),
        'connect_timeout' => (int) env('BUNNY_CONNECT_TIMEOUT', 10),
        'timeout' => (int) env('BUNNY_TIMEOUT', 30),
    ],

    'translation' => [
        'enabled' => env('TRANSLATION_ENABLED', false),
        'provider' => env('TRANSLATION_PROVIDER', 'google'),
        'cache_ttl' => (int) env('TRANSLATION_CACHE_TTL', 604800),
        'timeout' => (int) env('TRANSLATION_TIMEOUT', env('GOOGLE_TRANSLATE_TIMEOUT', 10)),
    ],

    'azure_translator' => [
        'key' => env('AZURE_TRANSLATOR_KEY'),
        'region' => env('AZURE_TRANSLATOR_REGION'),
        'endpoint' => env('AZURE_TRANSLATOR_ENDPOINT', 'https://api.cognitive.microsofttranslator.com'),
    ],

    'google_translate' => [
        'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
        'cache_ttl' => (int) env('TRANSLATION_CACHE_TTL', 604800),
        'timeout' => (int) env('GOOGLE_TRANSLATE_TIMEOUT', 10),
    ],

    'google_analytics' => [
        'measurement_id' => env('GOOGLE_ANALYTICS_ID'),
    ],

    'google_tag_manager' => [
        'container_id' => env('GOOGLE_TAG_MANAGER_ID'),
    ],

    'email_campaigns' => [
        'timezone' => env('EMAIL_CAMPAIGN_TIMEZONE', 'Europe/London'),
    ],

    'chatter_payroll' => [
        'exchange_rate_enabled' => env('CHATTER_EXCHANGE_RATE_ENABLED', true),
        'exchange_rate_url' => env('CHATTER_EXCHANGE_RATE_URL', 'https://api.frankfurter.dev/v1/latest'),
        'exchange_rate_refresh_hours' => env('CHATTER_EXCHANGE_RATE_REFRESH_HOURS', 6),
        'exchange_rate_retry_minutes' => env('CHATTER_EXCHANGE_RATE_RETRY_MINUTES', 15),
        'exchange_rate_timeout' => env('CHATTER_EXCHANGE_RATE_TIMEOUT', 5),
        'usd_to_php_rate_fallback' => env('CHATTER_USD_TO_PHP_RATE', '61.40'),
    ],

];
