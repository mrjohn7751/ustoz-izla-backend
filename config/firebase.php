<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    */

    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/credentials.json')),

    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY', ''),
        'sender_id' => env('FCM_SENDER_ID', ''),
    ],

    'database' => [
        'url' => env('FIREBASE_DATABASE_URL', ''),
    ],

    'storage' => [
        'bucket' => env('FIREBASE_STORAGE_BUCKET', ''),
    ],
];
