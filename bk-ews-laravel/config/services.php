<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Flask ML Service
    |--------------------------------------------------------------------------
    */
    'flask_ml' => [
        'url'     => env('FLASK_ML_URL', 'http://127.0.0.1:5000'),
        'timeout' => env('FLASK_ML_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonnte WhatsApp Gateway
    |--------------------------------------------------------------------------
    */
    'fonnte' => [
        'token' => env('FONNTE_TOKEN'),
        'url'   => env('FONNTE_URL', 'https://api.fonnte.com/send'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Postmark
    |--------------------------------------------------------------------------
    */
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS
    |--------------------------------------------------------------------------
    */
    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
