<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Biostar2 API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for your Biostar2 API endpoint
    |
    */
    'base_url' => env('BIOSTAR2_BASE_URL', 'https://10.150.20.173'),

    /*
    |--------------------------------------------------------------------------
    | Biostar2 Authentication
    |--------------------------------------------------------------------------
    |
    | Your Biostar2 admin credentials
    |
    */
    'login_id' => env('BIOSTAR2_LOGIN_ID'),
    'password' => env('BIOSTAR2_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    |
    | Whether to verify SSL certificates. Set to false for self-signed certs
    |
    */
    'verify_ssl' => env('BIOSTAR2_VERIFY_SSL', false),

    /*
    |--------------------------------------------------------------------------
    | Token Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long to cache the session token in seconds (default: 1 hour)
    |
    */
    'token_cache_duration' => env('BIOSTAR2_TOKEN_CACHE_DURATION', 3600),

    /*
    |--------------------------------------------------------------------------
    | Device Mappings
    |--------------------------------------------------------------------------
    |
    | Define your device IDs for different areas
    |
    */
    'devices' => [
        'smoke' => [544430390, 544430379],
        'outside' => [546209132, 544415326],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Event Types
    |--------------------------------------------------------------------------
    |
    | Common event type codes
    |
    */
    'event_types' => [
        'access_granted' => 4102,
        'access_denied' => 4354,
        'door_opened' => 6401,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default User Settings
    |--------------------------------------------------------------------------
    |
    | Default values when creating users
    |
    */
    'default_user' => [
        'start_datetime' => '2001-01-01T00:00:00.00Z',
        'expiry_datetime' => '2030-12-31T23:59:00.00Z',
        'permission_id' => '1',
        'user_group_id' => '1',
    ],
];