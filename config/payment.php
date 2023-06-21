<?php

return [
    'stripe' => [
        'app_id' => env('STRIPE_APP_ID'),
        'app_secret' => env('STRIPE_APP_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET')
    ]
];
