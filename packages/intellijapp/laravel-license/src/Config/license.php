<?php

return [
    /*
    |--------------------------------------------------------------------------
    | License System Configuration
    |--------------------------------------------------------------------------
    |
    | إعدادات نظام الترخيص والتفعيل للمنتج
    |
    */

    // تفعيل/تعطيل نظام الترخيص
    'enabled' => env('LICENSE_ENABLED', true),

    // التحقق من الترخيص عند كل طلب (قد يؤثر على الأداء)
    'check_on_every_request' => env('LICENSE_CHECK_EVERY_REQUEST', false),

    // فترة السماح بعد انتهاء الترخيص (بالأيام)
    'grace_period_days' => env('LICENSE_GRACE_PERIOD', 7),

    // فترة التحقق الدوري (بالثواني)
    'verification_interval' => env('LICENSE_VERIFICATION_INTERVAL', 3600), // ساعة واحدة

    // دعم التفعيل Offline
    'offline_mode' => env('LICENSE_OFFLINE_MODE', true),

    // مسار ملف الترخيص
    'license_file' => storage_path('app/license.key'),

    // Secret Key لتوقيع الترخيص (يجب تغييره في الإنتاج)
    'secret_key' => env('LICENSE_SECRET_KEY', 'your-secret-key-change-this'),

    // URL للتحقق Online (اختياري)
    'verification_url' => env('LICENSE_VERIFICATION_URL', null),

    // إعدادات المزامنة مع السيرفر المركزي
    'sync_enabled' => env('LICENSE_SYNC_ENABLED', false),
    'sync_server_url' => env('LICENSE_SYNC_SERVER_URL', null),
    'sync_api_token' => env('LICENSE_SYNC_API_TOKEN', null),
    'sync_interval' => env('LICENSE_SYNC_INTERVAL', 3600), // ثانية واحدة = 1 ساعة

    // السماح بتعدد التثبيتات لنفس المفتاح
    'allow_multiple_installations' => env('LICENSE_ALLOW_MULTIPLE', false),

    // Routes التي لا تحتاج ترخيص
    'excluded_routes' => [
        'license.activate',
        'license.status',
        'login',
        'register',
    ],

    // Controllers التي لا تحتاج ترخيص
    'excluded_controllers' => [
        'LicenseController',
    ],

    // Route Prefixes
    'route_prefix' => env('LICENSE_ROUTE_PREFIX', 'license'),
    'admin_route_prefix' => env('LICENSE_ADMIN_PREFIX', 'admin/licenses'),

    // Admin Check Function (قابلة للتخصيص)
    'admin_check' => function($user) {
        // Default: يجب تخصيصها حسب نظام المستخدمين في المشروع
        // مثال: return $user->isAdmin();
        // أو: return $user->type_id == UserType::where('name', 'admin')->first()?->id;
        return false; // يجب التخصيص
    },

    // Fingerprint Methods
    'fingerprint_methods' => [
        'mac_address',
        'server_info',
        'domain',
    ],

    // إعدادات قاعدة البيانات (للدعم المحلي مع SQLite)
    'database' => [
        // Connection الافتراضي (MySQL)
        'default_connection' => env('LICENSE_DB_CONNECTION', 'mysql'),
        
        // Connection للعمل المحلي (SQLite)
        'local_connection' => env('LICENSE_LOCAL_CONNECTION', 'sync_sqlite'),
        
        // التبديل التلقائي بين MySQL و SQLite حسب البيئة
        'auto_switch' => env('LICENSE_AUTO_SWITCH_DB', false),
        
        // استخدام SQLite في البيئة المحلية تلقائياً
        'use_sqlite_in_local' => env('LICENSE_USE_SQLITE_IN_LOCAL', true),
    ],
];

