<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sync Toggle
    |--------------------------------------------------------------------------
    |
    | Allows enabling or disabling sync logic without changing the code.
    |
    */
    'enabled' => env('SYNC_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Local Database Connection
    |--------------------------------------------------------------------------
    |
    | Connection name used for persisting local sync jobs (SQLite by default).
    |
    */
    'local_connection' => env('SYNC_LOCAL_CONNECTION', 'sync_sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Remote Server Settings
    |--------------------------------------------------------------------------
    */
    'server_url' => rtrim(env('SYNC_SERVER_URL', ''), '/'),
    'push_endpoint' => env('SYNC_PUSH_ENDPOINT', '/api/sync'),
    'pull_endpoint' => env('SYNC_PULL_ENDPOINT', '/api/changes'),
    'api_token' => env('SYNC_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Syncable Models
    |--------------------------------------------------------------------------
    |
    | Map API model keys to Eloquent class names. Add all models that should
    | participate in the offline/online sync workflow.
    |
    */
    'models' => [
        'car_contracts' => App\Models\CarContract::class,
        'transactions_contracts' => App\Models\TransactionsContract::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key
    |--------------------------------------------------------------------------
    |
    | Cache key used for persisting the last successful sync timestamp.
    |
    */
    'last_sync_cache_key' => 'sync:last_sync_time',
];

