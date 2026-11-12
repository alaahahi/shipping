<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Iran invoices page PIN
    |--------------------------------------------------------------------------
    |
    | Shared PIN required (once per session) for non-admin users to unlock
    | /iran and its APIs. Never expose this value to the Vue bundle.
    |
    */

    'page_pin' => env('IRAN_PAGE_PIN', '134679'),

    /*
    |--------------------------------------------------------------------------
    | Iran invoices page token
    |--------------------------------------------------------------------------
    |
    | Shared secret for guest access without login:
    |   /iran?token=...
    |   X-Iran-Token header on APIs
    | Never expose this value to the Vue bundle as a hard-coded default for
    | production; guests receive it only via the URL they were given.
    |
    */

    'page_token' => env('IRAN_PAGE_TOKEN', 'IrnTok_9c4e2a7f1b8d6e0c3a5f8b2d1e7c4a90'),

    /*
    |--------------------------------------------------------------------------
    | Owner id for token-guest sessions
    |--------------------------------------------------------------------------
    */

    'owner_id' => (int) env('IRAN_OWNER_ID', 1),

];
