<?php

return [
    'enabled' => env('PAYMENTS_ENABLED', false),
    'environment' => env('KHALTI_ENVIRONMENT', 'sandbox'),
    'secret' => env('KHALTI_SECRET_KEY'),
    'khalti_enabled' => env('KHALTI_ENABLED', true),
    'esewa' => [
        'enabled' => env('ESEWA_ENABLED', false),
        'environment' => env('ESEWA_ENVIRONMENT', 'sandbox'),
        'product_code' => env('ESEWA_PRODUCT_CODE'),
        'secret' => env('ESEWA_SECRET_KEY'),
    ],
];
