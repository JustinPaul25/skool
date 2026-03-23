<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ALTCHA (public enrollment)
    |--------------------------------------------------------------------------
    |
    | Uses proof-of-work challenge/response for public online enrollment.
    | Configure an HMAC key and set ALTCHA_ENABLED=true in production.
    |
    */

    'altcha' => [
        'enabled' => filter_var(env('ALTCHA_ENABLED', true), FILTER_VALIDATE_BOOLEAN)
            && filled(env('ALTCHA_HMAC_KEY')),
        'hmac_key' => env('ALTCHA_HMAC_KEY'),
        'max_number' => (int) env('ALTCHA_MAX_NUMBER', 100000),
        'expires_seconds' => (int) env('ALTCHA_EXPIRES_SECONDS', 300),
    ],

];
