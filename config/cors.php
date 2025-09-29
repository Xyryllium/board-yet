<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // Development origins
        'http://localhost:5173',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
        
        // Production origins
        'https://boardyet.com',
        
        // Legacy/test origins
        'https://api-test-board.com',
        'http://api-test-board.com:8000',
    ],

    'allowed_origins_patterns' => [
        'http://.*\.localhost:5173',
        'https://.*\.boardyet\.com',
        'https://.*\.api-test-board\.com',
    ],

    'allowed_credentials' => true,

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
