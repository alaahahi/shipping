<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'vin_decoder' => [
        'base_url' => env('VIN_DECODER_BASE_URL', 'https://vpic.nhtsa.dot.gov/api/vehicles'),
        'api_key' => env('VIN_DECODER_API_KEY'),
        'timeout' => (int) env('VIN_DECODER_TIMEOUT', 10),
        'cache_ttl' => (int) env('VIN_DECODER_CACHE_TTL', 86400),
    ],

    'whatsapp_queue' => [
        'base_url' => env('WA_QUEUE_BASE_URL', 'https://wa.intellij-app.com'),
        'created_by' => env('WA_QUEUE_CREATED_BY', 'shipping-erp'),
    ],

];
