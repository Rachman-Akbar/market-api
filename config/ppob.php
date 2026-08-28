<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | PPOB Provider (IAK / Mobile Pulsa)
    |--------------------------------------------------------------------------
    | Credentials are read from the environment so they are never hardcoded or
    | exposed through the API or frontend.
    */

    'provider' => [
        'base_url' => env('IAK_DEV_BASE_URL', 'https://prepaid.iak.dev/api/v1'),
        'username' => env('IAK_DEV_USER_ID', ''),
        'api_key' => env('IAK_DEV_API_KEY', ''),
        'environment' => env('IAK_ENV', 'development'),
        'timeout' => (int) env('IAK_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction defaults
    |--------------------------------------------------------------------------
    */

    'reference_prefix' => env('PPOB_REF_PREFIX', 'PPOB'),

    /*
    | How long (minutes) a pending/created prepaid transaction stays valid
    | before a status check is triggered / order expires.
    */
    'pending_expiry_minutes' => (int) env('PPOB_PENDING_EXPIRY_MINUTES', 15),

    /*
    | How long (minutes) a postpaid inquiry result remains usable for payment.
    */
    'inquiry_ttl_minutes' => (int) env('PPOB_INQUIRY_TTL_MINUTES', 30),
];
