# 🚀 دليل تحسين الأداء - نظام الشحن

## 📋 نظرة عامة

هذا الدليل يشرح كيفية تحسين أداء النظام وتفعيل وضع العمل Offline مع المزامنة التلقائية.

---

## 🎯 الميزات الجديدة

### ✅ 1. نظام IndexedDB للتخزين المحلي
- حفظ البيانات محلياً في متصفح المستخدم
- العمل بشكل كامل عند فقدان الاتصال
- مزامنة تلقائية عند عودة الاتصال

### ✅ 2. Service Worker
- تخزين مؤقت للأصول الثابتة
- استجابة سريعة حتى مع اتصال ضعيف
- صفحة Offline مخصصة

### ✅ 3. Pinia Store
- إدارة حالة التطبيق
- مراقبة حالة الاتصال والمزامنة
- إشعارات فورية للمستخدم

### ✅ 4. Queue System
- معالجة العمليات الثقيلة في الخلفية
- عدم تأخير استجابة المستخدم
- إعادة محاولة تلقائية عند الفشل

### ✅ 5. API Caching
- تخزين مؤقت ذكي للاستجابات
- تقليل الضغط على السيرفر
- استجابة أسرع للمستخدم

### ✅ 6. Sync Indicator
- مؤشر بصري لحالة الاتصال
- عرض عدد العمليات في الانتظار
- مزامنة يدوية عند الحاجة

---

## 📦 التثبيت والإعداد

### الخطوة 1: تثبيت المكتبات

```bash
# تثبيت مكتبات JavaScript
npm install

# أو باستخدام Yarn
yarn install
```

### الخطوة 2: إعداد Queue System

#### الطريقة 1: استخدام Database Queue (سهل)

1. تحديث `.env`:
```env
QUEUE_CONNECTION=database
```

2. إنشاء جدول Jobs:
```bash
php artisan queue:table
php artisan migrate
```

3. تشغيل Queue Worker:
```bash
php artisan queue:work --daemon --tries=3
```

#### الطريقة 2: استخدام Redis (أفضل للأداء)

1. تثبيت Redis:
```bash
# Windows (باستخدام WSL)
sudo apt install redis-server
sudo service redis-server start

# أو استخدام Redis for Windows
# تحميل من: https://github.com/microsoftarchive/redis/releases
```

2. تحديث `.env`:
```env
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis
```

3. تثبيت PHP Redis Extension:
```bash
# عبر PECL
pecl install redis

# أو قم بإضافته في php.ini:
extension=redis
```

4. تشغيل Queue Worker:
```bash
php artisan queue:work redis --daemon --tries=3
```

### الخطوة 3: تفعيل التخزين المؤقت

```bash
# تخزين التكوين مؤقتاً
php artisan config:cache

# تخزين المسارات مؤقتاً
php artisan route:cache

# تخزين Views مؤقتاً
php artisan view:cache

# تحسين autoloader
composer dump-autoload -o
```

### الخطوة 4: بناء الأصول

```bash
# للتطوير
npm run dev

# للإنتاج (مُحسّن)
npm run build
```

### الخطوة 5: تسجيل Middleware

في `app/Http/Kernel.php`، أضف:

```php
protected $middlewareGroups = [
    'api' => [
        // ... middleware أخرى
        \App\Http\Middleware\ApiCacheMiddleware::class . ':10', // 10 دقائق
    ],
];
```

---

## 🔧 الاستخدام

### في Vue Components

#### 1. استخدام API Wrapper

```vue
<script setup>
import { ref } from 'vue';

const cars = ref([]);
const loading = ref(false);

// جلب البيانات (يعمل Online و Offline)
const fetchCars = async () => {
    loading.value = true;
    try {
        const response = await window.$api.get('/api/cars', {
            cache: true,  // تخزين مؤقت
            cacheTTL: 600000  // 10 دقائق
        });
        
        cars.value = response.data;
        
        // معلومة: هل البيانات من الـ Cache؟
        if (response.fromCache) {
            console.log('📦 البيانات من الـ Cache المحلي');
        }
    } catch (error) {
        console.error('فشل جلب البيانات:', error);
    } finally {
        loading.value = false;
    }
};

// حفظ البيانات (يعمل Offline ويزامن تلقائياً)
const saveCar = async (carData) => {
    try {
        const response = await window.$api.post('/api/cars', carData, {
            saveLocal: true  // حفظ محلي
        });
        
        if (response.queued) {
            // البيانات محفوظة محلياً وفي انتظار المزامنة
            window.$toast.warning('تم الحفظ محلياً - سيتم المزامنة عند عودة الاتصال');
        } else {
            window.$toast.success('تم الحفظ بنجاح');
        }
    } catch (error) {
        window.$toast.error('فشل الحفظ');
    }
};
</script>
```

#### 2. استخدام Pinia Store

```vue
<script setup>
import { useAppStore } from '@/stores/appStore';

const appStore = useAppStore();

// التحقق من حالة الاتصال
if (appStore.isOnline) {
    console.log('متصل بالإنترنت');
} else {
    console.log('وضع Offline');
}

// مزامنة يدوية
const syncNow = async () => {
    await appStore.syncData();
};

// عدد العمليات في الانتظار
console.log('عمليات في الانتظار:', appStore.syncStatus.pendingCount);
</script>
```

#### 3. إضافة Sync Indicator

في Layout الرئيسي (مثل `resources/js/Layouts/AuthenticatedLayout.vue`):

```vue
<template>
    <div>
        <!-- محتوى الصفحة -->
        
        <!-- مؤشر المزامنة -->
        <SyncIndicator />
    </div>
</template>

<script setup>
import SyncIndicator from '@/Components/SyncIndicator.vue';
</script>
```

### في Backend (Controllers)

#### استخدام Queue للعمليات الثقيلة

```php
<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessHeavyTaskJob;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function store(Request $request)
    {
        // حفظ البيانات الأساسية فوراً
        $car = Car::create($request->all());
        
        // معالجة الصور في الخلفية
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('uploads', 'public');
                $images[] = $path;
            }
            
            // Queue Job لمعالجة الصور
            ProcessHeavyTaskJob::dispatch('image_processing', [
                'images' => $images,
                'car_id' => $car->id
            ]);
        }
        
        return response()->json([
            'success' => true,
            'car' => $car,
            'message' => 'تم الحفظ - جاري معالجة الصور في الخلفية'
        ]);
    }
    
    // استخدام Cache Middleware
    public function index()
    {
        // سيتم تخزين هذا الطلب مؤقتاً تلقائياً
        $cars = Car::with('model', 'color')->paginate(20);
        
        return response()->json($cars);
    }
}
```

---

## ⚙️ إعدادات متقدمة

### 1. تخصيص Cache TTL لكل Route

في `routes/api.php`:

```php
Route::middleware(['api', 'auth:sanctum', 'api.cache:30'])->group(function () {
    Route::get('/cars', [CarController::class, 'index']);  // Cache لمدة 30 دقيقة
});

Route::middleware(['api', 'auth:sanctum', 'api.cache:5'])->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);  // Cache لمدة 5 دقائق
});
```

### 2. مسح Cache يدوياً

```php
// في Controller
use Illuminate\Support\Facades\Cache;

public function clearCache()
{
    Cache::flush();  // مسح كل الـ Cache
    
    // أو مسح مفاتيح محددة
    Cache::forget('api_cache:specific_key');
    
    return response()->json(['message' => 'تم مسح الـ Cache']);
}
```

### 3. تخصيص IndexedDB Stores

في `resources/js/utils/db.js`، يمكنك إضافة stores جديدة:

```javascript
if (!db.objectStoreNames.contains('your_new_store')) {
    const newStore = db.createObjectStore('your_new_store', { 
        keyPath: 'id', 
        autoIncrement: true 
    });
    newStore.createIndex('your_field', 'your_field', { unique: false });
}
```

---

## 🚀 تشغيل القائمة كخدمة (Production)

### Windows (باستخدام NSSM)

1. تحميل NSSM: https://nssm.cc/download
2. فتح Command Prompt كمسؤول:

```bash
nssm install LaravelQueue "C:\xampp\php\php.exe" "C:\xampp\htdocs\shipping\artisan queue:work --daemon --tries=3"
nssm start LaravelQueue
```

### Linux (Supervisor)

1. تثبيت Supervisor:
```bash
sudo apt install supervisor
```

2. إنشاء ملف config:
```bash
sudo nano /etc/supervisor/conf.d/laravel-queue.conf
```

3. إضافة:
```ini
[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/shipping/artisan queue:work --daemon --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/shipping/storage/logs/queue.log
```

4. تفعيل:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-queue:*
```

---

## 📊 مراقبة الأداء

### 1. Laravel Telescope (Development)

```bash
# في التطوير فقط
php artisan telescope:install
php artisan migrate
```

زيارة: `http://localhost/telescope`

### 2. مراقبة Queue

```bash
# عرض حالة Queue
php artisan queue:monitor

# عرض Failed Jobs
php artisan queue:failed

# إعادة محاولة Failed Jobs
php artisan queue:retry all
```

### 3. Chrome DevTools

- افتح DevTools → Application → Storage
- شاهد IndexedDB و Cache Storage
- تحقق من Service Worker في Application → Service Workers

---

## 🐛 استكشاف الأخطاء

### المشكلة: Service Worker لا يعمل

**الحل:**
- تأكد من أن الموقع يعمل على HTTPS أو localhost
- امسح Cache المتصفح
- في Chrome DevTools → Application → Service Workers → Unregister ثم حدّث

### المشكلة: Queue لا تعمل

**الحل:**
```bash
# أعد تشغيل Queue Worker
php artisan queue:restart

# تحقق من الأخطاء
php artisan queue:failed

# أعد محاولة Jobs الفاشلة
php artisan queue:retry all
```

### المشكلة: بطء في الأداء

**الحلول:**
1. استخدم Redis بدلاً من File Cache
2. فعّل OPcache في php.ini
3. قم بتخزين Config مؤقتاً
4. استخدم CDN للأصول الثابتة
5. قم بتحسين استعلامات قاعدة البيانات

### المشكلة: Pinia غير معرّف

**الحل:**
```bash
# تثبيت Pinia
npm install pinia

# أعد بناء الأصول
npm run build
```

---

## 📈 نصائح لأفضل أداء

### 1. قاعدة البيانات

```sql
-- إضافة Indexes للأعمدة المستخدمة كثيراً
CREATE INDEX idx_cars_status ON cars(status);
CREATE INDEX idx_contracts_car_id ON contracts(car_id);
CREATE INDEX idx_transactions_date ON transactions(date);

-- تحسين الاستعلامات
OPTIMIZE TABLE cars;
ANALYZE TABLE cars;
```

### 2. PHP Configuration (php.ini)

```ini
; زيادة Memory Limit
memory_limit = 256M

; تفعيل OPcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2

; تحسين Upload
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
```

### 3. MySQL Configuration (my.ini)

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
query_cache_type = 1
query_cache_size = 64M
max_connections = 200
```

### 4. Laravel Optimization

```bash
# في Production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 🎓 موارد إضافية

- [Laravel Queues Documentation](https://laravel.com/docs/queues)
- [Service Workers MDN](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [IndexedDB API](https://developer.mozilla.org/en-US/docs/Web/API/IndexedDB_API)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Redis Documentation](https://redis.io/documentation)

---

## 📞 الدعم

إذا واجهت أي مشكلة:
1. راجع الأخطاء في `storage/logs/laravel.log`
2. افحص Console في المتصفح
3. تحقق من حالة Queue: `php artisan queue:monitor`
4. راجع هذا الدليل

---

## ✨ الخلاصة

بعد تطبيق هذه التحسينات:

✅ **الأداء:** تحسن كبير في سرعة الاستجابة
✅ **الاستقرار:** عمل مستقر حتى مع اتصال ضعيف
✅ **Offline:** العمل بشكل كامل بدون اتصال
✅ **المزامنة:** تلقائية وشفافة للمستخدم
✅ **تجربة المستخدم:** أفضل بكثير مع feedback فوري

---

**تم بناء النظام مع ❤️ لتحسين تجربة المستخدم والأداء**

