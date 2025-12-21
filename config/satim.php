<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Satim Authentication
    |--------------------------------------------------------------------------
    |
    | Your Satim merchant credentials provided by your bank.
    |
    */

    'username' => env('SATIM_USERNAME'),
    'password' => env('SATIM_PASSWORD'),
    'terminal_id' => env('SATIM_TERMINAL_ID'),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default language and currency for payments.
    | Language: FR (French), EN (English), AR (Arabic)
    | Currency: 012 (DZD - Algerian Dinar, ISO 4217)
    |
    */

    'language' => env('SATIM_LANGUAGE', 'fr'),
    'currency' => env('SATIM_CURRENCY', '012'),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Satim API base URL. Use test environment for development:
    | Test: https://test2.satim.dz/payment/rest
    | Production: https://satim.dz/payment/rest
    |
    */

    'api_url' => env('SATIM_API_URL', 'https://test2.satim.dz/payment/rest'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    |
    | Configure the HTTP client behavior for API requests.
    |
    */

    'verify_ssl' => env('SATIM_HTTP_VERIFY_SSL', false),
    'timeout' => env('SATIM_HTTP_TIMEOUT', 30),
    'connect_timeout' => env('SATIM_HTTP_CONNECT_TIMEOUT', 10),
];
