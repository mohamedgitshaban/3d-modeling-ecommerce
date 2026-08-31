<?php

return [
    'base_url' => env('PAYMOB_BASE_URL', 'https://accept.paymob.com/api'),
    'api_key' => env('PAYMOB_API_KEY'),
    'hmac_secret' => env('PAYMOB_HMAC_SECRET'),

    'integrations' => [
        'card' => env('PAYMOB_INTEGRATION_ID_CARD'),
        'wallet' => env('PAYMOB_INTEGRATION_ID_WALLET'),
        'kiosk' => env('PAYMOB_INTEGRATION_ID_KIOSK'),
    ],

    'iframe_id' => env('PAYMOB_IFRAME_ID'),
    'currency' => env('PAYMOB_CURRENCY', 'EGP'),
];
