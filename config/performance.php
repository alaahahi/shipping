<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    | إعدادات تحسين الأداء
    |--------------------------------------------------------------------------
    */

    'cache' => [
        /*
         | مدة التخزين المؤقت الافتراضية (بالدقائق)
         */
        'default_ttl' => env('CACHE_DEFAULT_TTL', 10),
        
        /*
         | مدة التخزين المؤقت للـ API (بالدقائق)
         */
        'api_ttl' => env('CACHE_API_TTL', 10),
        
        /*
         | مدة التخزين المؤقت للبيانات الثابتة (بالساعات)
         */
        'static_ttl' => env('CACHE_STATIC_TTL', 24) * 60,
        
        /*
         | تفعيل التخزين المؤقت للاستعلامات
         */
        'query_cache_enabled' => env('QUERY_CACHE_ENABLED', true),
    ],

    'queue' => [
        /*
         | عدد محاولات إعادة المحاولة عند الفشل
         */
        'max_tries' => env('QUEUE_MAX_TRIES', 3),
        
        /*
         | الوقت بين المحاولات (بالثواني)
         */
        'backoff' => [10, 30, 60],
        
        /*
         | Timeout للـ Jobs (بالثواني)
         */
        'timeout' => env('QUEUE_TIMEOUT', 300), // 5 minutes
        
        /*
         | عدد الـ Workers المطلوبة
         */
        'workers' => env('QUEUE_WORKERS', 2),
    ],

    'offline' => [
        /*
         | تفعيل وضع Offline
         */
        'enabled' => env('OFFLINE_MODE_ENABLED', true),
        
        /*
         | مدة صلاحية البيانات المحلية (بالساعات)
         */
        'local_data_ttl' => env('OFFLINE_DATA_TTL', 24),
        
        /*
         | المزامنة التلقائية عند العودة Online
         */
        'auto_sync' => env('OFFLINE_AUTO_SYNC', true),
        
        /*
         | عدد محاولات المزامنة
         */
        'sync_retries' => env('OFFLINE_SYNC_RETRIES', 3),
    ],

    'images' => [
        /*
         | جودة الصور المحفوظة (1-100)
         */
        'quality' => env('IMAGE_QUALITY', 85),
        
        /*
         | الحد الأقصى لعرض الصورة (pixels)
         */
        'max_width' => env('IMAGE_MAX_WIDTH', 1920),
        
        /*
         | الحد الأقصى لارتفاع الصورة (pixels)
         */
        'max_height' => env('IMAGE_MAX_HEIGHT', 1920),
        
        /*
         | أبعاد الصور المصغرة
         */
        'thumbnail' => [
            'width' => env('THUMBNAIL_WIDTH', 300),
            'height' => env('THUMBNAIL_HEIGHT', 300),
        ],
        
        /*
         | معالجة الصور في الخلفية
         */
        'process_in_background' => env('IMAGE_PROCESS_BACKGROUND', true),
    ],

    'api' => [
        /*
         | معدل الطلبات المسموحة (requests per minute)
         */
        'rate_limit' => env('API_RATE_LIMIT', 60),
        
        /*
         | وقت التحلل للـ Rate Limiter (بالدقائق)
         */
        'rate_limit_decay' => env('API_RATE_LIMIT_DECAY', 1),
        
        /*
         | Timeout للطلبات (بالثواني)
         */
        'timeout' => env('API_TIMEOUT', 30),
        
        /*
         | إعادة المحاولة التلقائية
         */
        'retry_attempts' => env('API_RETRY_ATTEMPTS', 2),
    ],

    'database' => [
        /*
         | تفعيل الاتصالات الدائمة
         */
        'persistent' => env('DB_PERSISTENT', false),
        
        /*
         | عدد الاتصالات في الـ Pool
         */
        'pool_size' => env('DB_POOL_SIZE', 10),
        
        /*
         | Timeout للاستعلامات (بالثواني)
         */
        'timeout' => env('DB_TIMEOUT', 60),
    ],

    'optimization' => [
        /*
         | تفعيل تحسين الأداء
         */
        'enabled' => env('OPTIMIZE_ENABLED', true),
        
        /*
         | ضغط الاستجابات
         */
        'compress_responses' => env('COMPRESS_RESPONSES', true),
        
        /*
         | تصغير HTML/CSS/JS
         */
        'minify_assets' => env('MINIFY_ASSETS', true),
        
        /*
         | استخدام CDN
         */
        'use_cdn' => env('USE_CDN', false),
        'cdn_url' => env('CDN_URL', ''),
    ],

    'monitoring' => [
        /*
         | تفعيل المراقبة
         */
        'enabled' => env('MONITORING_ENABLED', true),
        
        /*
         | تسجيل الاستعلامات البطيئة (ms)
         */
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000),
        
        /*
         | تسجيل الطلبات البطيئة (ms)
         */
        'slow_request_threshold' => env('SLOW_REQUEST_THRESHOLD', 2000),
    ],

];

