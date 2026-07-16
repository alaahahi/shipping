<?php

return [

    'enabled' => env('MONITOR_ENABLED', true),

    'project_name' => env('MONITOR_PROJECT_NAME', env('APP_NAME', 'Laravel')),

    'capture_web' => env('MONITOR_CAPTURE_WEB', true),
    'capture_api' => env('MONITOR_CAPTURE_API', true),
    'capture_console' => env('MONITOR_CAPTURE_CONSOLE', true),
    'capture_queue' => env('MONITOR_CAPTURE_QUEUE', true),

    'log_path' => storage_path('logs/monitor'),

    'slow_query_threshold_ms' => (int) env('MONITOR_SLOW_QUERY_MS', 500),
    'slow_request_threshold_ms' => (int) env('MONITOR_SLOW_REQUEST_MS', 2000),

    'ignore_routes' => [
        'monitor.dashboard',
        'monitor.status',
    ],

    'ignore_path_prefixes' => [
        '_debugbar',
        'livewire',
    ],

    'ignore_commands' => [
        'monitor:clean',
        'schedule:run',
    ],

    'alert_thresholds' => [
        'threads_connected' => (int) env('MONITOR_ALERT_THREADS', 100),
        'response_time_ms' => (int) env('MONITOR_ALERT_RESPONSE_MS', 5000),
        'memory_mb' => (int) env('MONITOR_ALERT_MEMORY_MB', 128),
        'query_time_ms' => (int) env('MONITOR_ALERT_QUERY_MS', 3000),
    ],

    'retention_days' => (int) env('MONITOR_RETENTION_DAYS', 30),

    'admin_type_ids' => array_filter(array_map('intval', explode(',', env('MONITOR_ADMIN_TYPES', '1')))),

    'dashboard_middleware' => ['web', 'auth', 'monitor.admin'],
    'status_middleware' => ['web', 'auth', 'monitor.admin'],

    'snapshot_db_every_request' => env('MONITOR_DB_SNAPSHOT', true),

];
