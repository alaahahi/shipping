# دليل العمل Offline

## 📋 نظرة عامة

نظام الترخيص يدعم العمل **Offline** بشكل كامل، مما يعني أنه يمكن تفعيله والتحقق منه بدون اتصال بالإنترنت.

---

## 🔌 كيف يعمل Offline Mode

### 1. حفظ الترخيص في ملف

عند التفعيل، يتم حفظ الترخيص في:
```
storage/app/license.key
```

الملف يحتوي على:
- مفتاح الترخيص المشفر
- Domain
- Fingerprint
- نوع الترخيص
- تاريخ الانتهاء
- معلومات أخرى

### 2. قراءة من الملف

عند عدم وجود اتصال:
1. النظام يحاول قراءة من Database أولاً
2. إذا فشل، يقرأ من ملف `license.key`
3. يستخدم البيانات من الملف للتحقق

---

## 🚀 التفعيل Offline

### السيناريو: سيرفر بدون إنترنت

#### الخطوة 1: الحصول على معلومات السيرفر

في سيرفر متصل، احصل على معلومات السيرفر:

```bash
# عبر API
curl http://your-server.com/api/license/server-info

# أو عبر Command
php artisan tinker
>>> \IntellijApp\License\Services\LicenseService::getCurrentDomain()
>>> \IntellijApp\License\Services\LicenseService::getServerFingerprint()
```

#### الخطوة 2: إنشاء الترخيص

في سيرفر متصل (أو محلي):

```bash
php artisan license:generate \
  --domain=offline-server.com \
  --type=standard \
  --expires=2025-12-31
```

**احفظ مفتاح الترخيص!**

#### الخطوة 3: تفعيل في السيرفر Offline

##### الطريقة 1: عبر ملف
1. أنشئ ملف `license.key` في `storage/app/`
2. ضع مفتاح الترخيص فيه
3. استخدم `activateFromFile()` (إذا كان متوفراً)

##### الطريقة 2: عبر API (إذا كان متاحاً مؤقتاً)
```bash
curl -X POST http://offline-server.com/api/license/activate \
  -H "Content-Type: application/json" \
  -d '{"license_key": "your-license-key"}'
```

##### الطريقة 3: برمجياً
```php
use IntellijApp\License\Services\LicenseService;

$result = LicenseService::activate($licenseKey);
```

---

## 🔍 التحقق Offline

### Command Line

```bash
php artisan license:verify
```

سيعمل حتى بدون إنترنت إذا كان ملف `license.key` موجود.

### برمجياً

```php
use IntellijApp\License\Services\LicenseService;

// التحقق العادي (يحاول DB ثم File)
$isValid = LicenseService::verify();

// التحقق Offline فقط (من File)
$isValidOffline = LicenseService::verifyOffline();

// معلومات Offline
$info = LicenseService::getOfflineLicenseInfo();
```

---

## 📁 ملف الترخيص

### الموقع
```
storage/app/license.key
```

### المحتوى (JSON)
```json
{
    "license_key": "encrypted-key-here",
    "domain": "example.com",
    "fingerprint": "server-fingerprint-hash",
    "type": "standard",
    "activated_at": "2025-01-01 12:00:00",
    "expires_at": "2025-12-31 23:59:59",
    "saved_at": "2025-01-01 12:00:00"
}
```

### الأمان
- الملف مشفر
- يحتوي على توقيع للتحقق
- يجب حمايته (permissions: 600)

---

## 🔐 Fingerprint

### ما هو Fingerprint؟

Fingerprint هو hash فريد للسيرفر يتضمن:
- Hostname
- Machine type
- Operating system
- MAC Address (إن أمكن)

### استخدامه

عند إنشاء ترخيص، يتم ربطه بـ Fingerprint السيرفر. هذا يمنع استخدام نفس المفتاح في سيرفرات أخرى.

### تخصيص Fingerprint

في `config/license.php`:
```php
'fingerprint_methods' => [
    'mac_address',
    'server_info',
    'domain',
    // يمكن إضافة المزيد
]
```

---

## ⚠️ ملاحظات مهمة

### 1. أمان الملف
```bash
# حماية الملف
chmod 600 storage/app/license.key
```

### 2. النسخ الاحتياطي
احتفظ بنسخة احتياطية من `license.key` في مكان آمن.

### 3. التحديث
عند تحديث الترخيص، يتم تحديث الملف تلقائياً.

### 4. الحذف
حذف الملف لا يحذف الترخيص من Database، لكن يمنع العمل Offline.

---

## 🐛 استكشاف الأخطاء

### المشكلة: لا يعمل Offline
**الحل:**
1. تحقق من وجود `storage/app/license.key`
2. تحقق من `LICENSE_OFFLINE_MODE=true` في `.env`
3. تحقق من permissions الملف

### المشكلة: Fingerprint غير متطابق
**الحل:**
- تأكد من إنشاء الترخيص بنفس Fingerprint
- أو استخدم `--fingerprint` عند الإنشاء

### المشكلة: الملف تالف
**الحل:**
- احذف الملف
- فعّل الترخيص مرة أخرى

---

## 📚 أمثلة

### مثال 1: تفعيل Offline كامل

```php
// في سيرفر متصل
$licenseKey = 'your-license-key';
$domain = 'offline-server.com';
$fingerprint = 'server-fingerprint';

// حفظ في ملف
$license = LicenseService::activate($licenseKey, $domain, $fingerprint);
LicenseService::saveToFile($license['license']);

// نسخ الملف إلى السيرفر Offline
// scp storage/app/license.key user@offline-server:/path/to/storage/app/
```

### مثال 2: التحقق الدوري Offline

```php
// في Scheduled Task
$schedule->call(function () {
    if (!LicenseService::verifyOffline()) {
        // إرسال تنبيه
        Log::warning('License verification failed (Offline)');
    }
})->hourly();
```

---

**نظام Offline جاهز للاستخدام! 🔌**

