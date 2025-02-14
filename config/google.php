<?php

return [
    'analytics' => [
        'view_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID', ''),
        'credentials_json' => env('GOOGLE_APPLICATION_CREDENTIALS', storage_path('keys/service-account.json')),
    ],
];
