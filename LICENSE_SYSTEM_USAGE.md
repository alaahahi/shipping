# دليل استخدام نظام الترخيص

## 📋 نظرة عامة

نظام الترخيص والتفعيل للمنتج يوفر حماية كاملة من الاستخدام غير المصرح به مع دعم التفعيل Online و Offline.

---

## 🚀 البدء السريع

### 1. تفعيل النظام

في ملف `.env`:
```env
LICENSE_ENABLED=true
LICENSE_SECRET_KEY=your-secret-key-change-this
LICENSE_CHECK_EVERY_REQUEST=false
LICENSE_OFFLINE_MODE=true
```

### 2. تشغيل Migration

```bash
php artisan migrate
```

### 3. إنشاء مفتاح ترخيص

```bash
php artisan license:generate --domain=example.com --type=standard --expires=2025-12-31
```

---

## 🔑 إنشاء مفاتيح الترخيص

### الطريقة 1: Command Line

```bash
# ترخيص دائم
php artisan license:generate --domain=system.intellijapp.com --type=standard

# ترخيص سنوي
php artisan license:generate --domain=system.intellijapp.com --type=standard --expires=2025-12-31

# ترخيص تجريبي (30 يوم)
php artisan license:generate --domain=system.intellijapp.com --type=trial --expires=2024-12-31

# ترخيص متعدد التثبيتات
php artisan license:generate --domain=example.com --type=premium --installations=5
```

### الطريقة 2: برمجياً

```php
use App\Services\LicenseService;

$licenseData = [
    'domain' => 'system.intellijapp.com',
    'fingerprint' => LicenseService::getServerFingerprint(),
    'type' => 'standard',
    'expires_at' => '2025-12-31',
    'max_installations' => 1,
];

$licenseKey = LicenseService::encryptLicenseKey($licenseData);
echo $licenseKey;
```

---

## 🎯 تفعيل الترخيص

### الطريقة 1: من الواجهة

1. افتح المتصفح واذهب إلى: `/license/activate`
2. أدخل مفتاح الترخيص
3. اضغط "تفعيل الآن"

### الطريقة 2: من API

```bash
curl -X POST http://your-domain.com/api/license/activate \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "your-license-key-here"
  }'
```

### الطريقة 3: برمجياً

```php
use App\Services\LicenseService;

$result = LicenseService::activate($licenseKey);

if ($result['success']) {
    echo "تم التفعيل بنجاح!";
} else {
    echo "فشل التفعيل: " . $result['message'];
}
```

---

## 🔍 التحقق من الترخيص

### Command Line

```bash
php artisan license:verify
```

### برمجياً

```php
use App\Services\LicenseService;

// التحقق من التفعيل
if (LicenseService::isActivated()) {
    echo "الترخيص مفعل";
}

// التحقق من الصلاحية
if (LicenseService::verify()) {
    echo "الترخيص صالح";
}

// الحصول على معلومات الترخيص
$info = LicenseService::getLicenseInfo();
print_r($info);
```

### Helper Functions

```php
// التحقق من التفعيل
if (license()) {
    // الترخيص مفعل
}

// الحصول على نوع الترخيص
$type = license_type(); // 'trial', 'standard', 'premium'

// الحصول على تاريخ الانتهاء
$expires = license_expires_at();

// الحصول على الأيام المتبقية
$days = license_days_remaining();
```

---

## 🛡️ الحماية

### Middleware

يتم تطبيق `check.license` Middleware تلقائياً على Routes المهمة.

لإضافة حماية يدوياً:

```php
Route::middleware(['auth', 'check.license'])->group(function () {
    // Routes محمية
});
```

### Routes المستثناة

في `config/license.php`:
```php
'excluded_routes' => [
    'license.activate',
    'license.status',
    'login',
    'register',
],
```

---

## 📊 صفحات الواجهة

### صفحة التفعيل
- **URL:** `/license/activate`
- **Route:** `license.activate`
- **الوصف:** صفحة تفعيل الترخيص

### صفحة الحالة
- **URL:** `/license/status`
- **Route:** `license.status`
- **الوصف:** عرض حالة الترخيص الحالي

### صفحة إدارة التراخيص (للأدمن)
- **URL:** `/admin/licenses`
- **Route:** `admin.licenses.index`
- **الوصف:** صفحة إدارة التراخيص - إنشاء، عرض، تعديل، وحذف التراخيص
- **المميزات:**
  - ✅ عرض جميع التراخيص في جدول
  - ✅ إنشاء تراخيص جديدة مع عرض مفتاح الترخيص
  - ✅ تعديل التراخيص (النوع، تاريخ الانتهاء، الحالة)
  - ✅ تفعيل/إلغاء تفعيل التراخيص
  - ✅ حذف التراخيص
  - ✅ عرض تفاصيل الترخيص
  - ✅ إحصائيات التراخيص (إجمالي، مفعل، معطل، منتهي)
  - ✅ نسخ مفتاح الترخيص بعد الإنشاء

---

## 🔧 الإعدادات

### config/license.php

```php
return [
    // تفعيل/تعطيل النظام
    'enabled' => env('LICENSE_ENABLED', true),
    
    // التحقق عند كل طلب (قد يؤثر على الأداء)
    'check_on_every_request' => env('LICENSE_CHECK_EVERY_REQUEST', false),
    
    // فترة السماح بعد الانتهاء (بالأيام)
    'grace_period_days' => env('LICENSE_GRACE_PERIOD', 7),
    
    // فترة التحقق الدوري (بالثواني)
    'verification_interval' => env('LICENSE_VERIFICATION_INTERVAL', 3600),
    
    // دعم التفعيل Offline
    'offline_mode' => env('LICENSE_OFFLINE_MODE', true),
    
    // مسار ملف الترخيص
    'license_file' => storage_path('app/license.key'),
    
    // Secret Key للتوقيع
    'secret_key' => env('LICENSE_SECRET_KEY', 'your-secret-key-change-this'),
];
```

---

## 📝 أنواع الترخيص

### Trial (تجريبي)
- للاختبار والتجربة
- عادة 30 يوم

### Standard (قياسي)
- للاستخدام العادي
- يمكن أن يكون دائم أو محدود

### Premium (مميز)
- للميزات المتقدمة
- قد يدعم تعدد التثبيتات

---

## 🔄 التحقق الدوري

يتم التحقق من الترخيص تلقائياً كل ساعة عبر Scheduled Task:

```php
// في app/Console/Kernel.php
$schedule->command('license:verify')
    ->hourly()
    ->withoutOverlapping();
```

---

## 🗄️ قاعدة البيانات

### جدول `licenses`

```sql
SELECT * FROM licenses WHERE domain = 'your-domain.com';
```

### الحقول المهمة:
- `license_key`: مفتاح الترخيص المشفر
- `domain`: Domain أو IP
- `fingerprint`: Server Fingerprint
- `type`: نوع الترخيص
- `expires_at`: تاريخ الانتهاء
- `is_active`: حالة التفعيل

---

## 🐛 حل المشاكل

### الترخيص غير مفعل

1. تحقق من `LICENSE_ENABLED` في `.env`
2. تأكد من وجود ترخيص في Database
3. تحقق من ملف `storage/app/license.key`

### مفتاح الترخيص غير صالح

1. تأكد من صحة المفتاح
2. تحقق من Domain و Fingerprint
3. تأكد من عدم انتهاء الترخيص

### خطأ في التشفير

1. تأكد من `LICENSE_SECRET_KEY` في `.env`
2. تأكد من `APP_KEY` في `.env`
3. قم بتشغيل `php artisan config:clear`

---

## 📞 الدعم

للمساعدة:
1. تحقق من ملفات السجل: `storage/logs/license.log`
2. شغل `php artisan license:verify` للتحقق
3. راجع `LICENSE_SYSTEM_PLAN.md` للتفاصيل

---

## ✅ Checklist للتنصيب

- [ ] إعداد `.env` مع `LICENSE_ENABLED=true`
- [ ] تشغيل Migration
- [ ] إنشاء مفتاح ترخيص
- [ ] تفعيل الترخيص
- [ ] التحقق من التفعيل
- [ ] اختبار الحماية
- [ ] إعداد التحقق الدوري

---

## 🔐 الأمان

### توصيات:

1. **غير `LICENSE_SECRET_KEY`** في الإنتاج
2. **احفظ مفاتيح الترخيص** في مكان آمن
3. **استخدم HTTPS** في الإنتاج
4. **راقب السجلات** بانتظام
5. **حدث النظام** بانتظام

---

## 📚 ملفات إضافية

- `LICENSE_SYSTEM_PLAN.md` - خطة العمل التفصيلية
- `LICENSE_OPTIONS_COMPARISON.md` - مقارنة الخيارات
- `app/Services/LicenseService.php` - خدمة الترخيص
- `app/Http/Controllers/LicenseController.php` - Controller
- `app/Http/Middleware/CheckLicense.php` - Middleware

---

**تم إنشاء النظام بنجاح! 🎉**

