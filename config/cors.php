<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://marketplace-frontend.test',
        'http://market-api.test',           // Backend sendiri
        'https://marketplace-frontend.test',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-XSRF-TOKEN',
        'X-CSRF-TOKEN',
    ],

    'exposed_headers' => [],

    'max_age' => 7200,

    'supports_credentials' => true,
];