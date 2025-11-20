# دليل المزامنة مع السيرفر المركزي

## 📋 نظرة عامة

نظام الترخيص يدعم المزامنة مع سيرفر مركزي لإدارة التراخيص بشكل مركزي.

---

## ⚙️ الإعداد

### 1. تفعيل المزامنة في Config

في `config/license.php` أو `.env`:

```env
LICENSE_SYNC_ENABLED=true
LICENSE_SYNC_SERVER_URL=https://your-central-server.com
LICENSE_SYNC_API_TOKEN=your-api-token-here
LICENSE_SYNC_INTERVAL=3600
```

### 2. إعدادات Config

```php
'sync_enabled' => env('LICENSE_SYNC_ENABLED', false),
'sync_server_url' => env('LICENSE_SYNC_SERVER_URL', null),
'sync_api_token' => env('LICENSE_SYNC_API_TOKEN', null),
'sync_interval' => env('LICENSE_SYNC_INTERVAL', 3600), // بالثواني
```

---

## 🔄 أنواع المزامنة

### 1. المزامنة التلقائية (Sync)
- مزامنة ثنائية الاتجاه
- تحديث الترخيص من السيرفر المركزي
- إرسال الترخيص إلى السيرفر المركزي

### 2. جلب من السيرفر (Pull)
- جلب الترخيص من السيرفر المركزي فقط
- مفيد عند فقدان الترخيص المحلي

### 3. إرسال إلى السيرفر (Push)
- إرسال الترخيص إلى السيرفر المركزي فقط
- مفيد لتحديث السيرفر المركزي

---

## 🚀 الاستخدام

### عبر Command Line

#### مزامنة تلقائية:
```bash
php artisan license:sync
```

#### جلب من السيرفر:
```bash
php artisan license:sync --pull
```

#### إرسال إلى السيرفر:
```bash
php artisan license:sync --push
```

### عبر API

#### مزامنة:
```bash
POST /api/license/sync
```

#### جلب:
```bash
POST /api/license/pull
```

#### إرسال:
```bash
POST /api/license/push
```

### برمجياً

```php
use IntellijApp\License\Services\LicenseService;

// مزامنة تلقائية
$result = LicenseService::syncWithCentralServer();

// جلب من السيرفر
$result = LicenseService::pullFromCentralServer();

// إرسال إلى السيرفر
$result = LicenseService::pushToCentralServer();

// مزامنة تلقائية (من Scheduled Task)
LicenseService::autoSync();
```

---

## 📡 API Endpoints المطلوبة في السيرفر المركزي

### 1. POST /api/license/sync
**Request:**
```json
{
    "license_key": "...",
    "domain": "example.com",
    "fingerprint": "...",
    "type": "standard",
    "is_active": true,
    "activated_at": "2025-01-01T00:00:00Z",
    "expires_at": "2025-12-31T23:59:59Z",
    "last_verified_at": "2025-01-01T00:00:00Z"
}
```

**Response:**
```json
{
    "success": true,
    "license": {
        "is_active": true,
        "expires_at": "2025-12-31T23:59:59Z"
    }
}
```

### 2. POST /api/license/pull
**Request:**
```json
{
    "domain": "example.com",
    "fingerprint": "..."
}
```

**Response:**
```json
{
    "success": true,
    "license": {
        "license_key": "...",
        "domain": "example.com",
        "fingerprint": "...",
        "type": "standard",
        "is_active": true,
        "activated_at": "2025-01-01T00:00:00Z",
        "expires_at": "2025-12-31T23:59:59Z"
    }
}
```

### 3. POST /api/license/push
**Request:**
```json
{
    "license_key": "...",
    "domain": "example.com",
    "fingerprint": "...",
    "type": "standard",
    "is_active": true,
    "activated_at": "2025-01-01T00:00:00Z",
    "expires_at": "2025-12-31T23:59:59Z",
    "last_verified_at": "2025-01-01T00:00:00Z"
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم حفظ الترخيص"
}
```

---

## ⏰ المزامنة التلقائية

### إضافة Scheduled Task

في `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // مزامنة الترخيص كل ساعة
    $schedule->call(function () {
        \IntellijApp\License\Services\LicenseService::autoSync();
    })->hourly();
}
```

---

## 🔐 الأمان

### 1. API Token
- استخدم token قوي
- لا تشارك token في الكود
- استخدم `.env` لحفظ token

### 2. HTTPS
- استخدم HTTPS فقط
- لا تستخدم HTTP في الإنتاج

### 3. التحقق
- تحقق من التوقيع
- تحقق من Domain و Fingerprint

---

## 🐛 استكشاف الأخطاء

### المشكلة: المزامنة تفشل
**الحل:**
1. تحقق من `LICENSE_SYNC_ENABLED=true`
2. تحقق من `LICENSE_SYNC_SERVER_URL`
3. تحقق من `LICENSE_SYNC_API_TOKEN`
4. تحقق من اتصال الإنترنت
5. تحقق من Logs

### المشكلة: Token غير صحيح
**الحل:**
- تحقق من token في السيرفر المركزي
- تأكد من استخدام Bearer Token

### المشكلة: السيرفر المركزي غير متاح
**الحل:**
- النظام سيعمل Offline تلقائياً
- سيتم المحاولة مرة أخرى في المزامنة التالية

---

## 📚 أمثلة

### مثال 1: مزامنة يدوية
```php
$result = LicenseService::syncWithCentralServer();
if ($result['success']) {
    echo "تمت المزامنة بنجاح!";
} else {
    echo "فشلت المزامنة: " . $result['message'];
}
```

### مثال 2: جلب ترخيص مفقود
```php
$result = LicenseService::pullFromCentralServer();
if ($result['success']) {
    $license = $result['license'];
    echo "تم جلب الترخيص: " . $license->license_key;
}
```

### مثال 3: إرسال تحديث
```php
$license = LicenseService::getCurrentLicense();
$license->update(['is_active' => false]);

// إرسال التحديث إلى السيرفر المركزي
LicenseService::pushToCentralServer();
```

---

**نظام المزامنة جاهز للاستخدام! 🔄**

