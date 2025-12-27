# Laravel License Package

نظام إدارة التراخيص مع دعم التفعيل Online و Offline للمشاريع Laravel.

## 📋 المميزات

- ✅ تفعيل Online و Offline
- ✅ حماية Routes عبر Middleware
- ✅ واجهة إدارة كاملة
- ✅ Commands لإنشاء والتحقق من التراخيص
- ✅ دعم أنواع تراخيص متعددة (Trial, Standard, Premium)
- ✅ Grace Period بعد انتهاء الترخيص
- ✅ Fingerprint للتحقق من السيرفر

## 🚀 التثبيت السريع

### الطريقة 1: Path Repository (مُوصى بها)

1. **نسخ Package إلى المشروع:**
```bash
# نسخ Package إلى packages/
cp -r laravel-license-package packages/intellijapp/laravel-license
```

2. **إضافة إلى composer.json:**
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/intellijapp/laravel-license"
        }
    ],
    "require": {
        "intellijapp/laravel-license": "@dev"
    }
}
```

3. **تثبيت Package:**
```bash
composer require intellijapp/laravel-license:@dev
```

4. **نشر Config و Migrations:**
```bash
php artisan vendor:publish --tag=license-config
php artisan vendor:publish --tag=license-migrations
```

5. **تشغيل Migrations:**
```bash
php artisan migrate
```

6. **تثبيت النظام (اختياري):**
```bash
php artisan license:install
```

## ⚙️ الإعدادات

### 1. إعداد .env

```env
LICENSE_ENABLED=true
LICENSE_SECRET_KEY=your-secret-key-change-this
LICENSE_OFFLINE_MODE=true
LICENSE_CHECK_EVERY_REQUEST=false
LICENSE_GRACE_PERIOD=7
```

### 2. تخصيص Admin Check

في `config/license.php`:

```php
'admin_check' => function($user) {
    // مثال: return $user->isAdmin();
    // أو: return $user->type_id == UserType::where('name', 'admin')->first()?->id;
    return $user->isAdmin();
}
```

## 📝 الاستخدام

### إنشاء ترخيص

```bash
php artisan license:generate --domain=example.com --type=standard --expires=2025-12-31
```

### التحقق من الترخيص

```bash
php artisan license:verify
```

### في الكود

```php
use IntellijApp\License\Services\LicenseService;

// التحقق من التفعيل
if (LicenseService::isActivated()) {
    // الترخيص مفعل
}

// Helper Functions
if (license()) {
    // الترخيص مفعل
}

$type = license_type(); // 'trial', 'standard', 'premium'
$expires = license_expires_at();
$days = license_days_remaining();
```

### Middleware

```php
Route::middleware(['auth', 'check.license'])->group(function () {
    // Routes محمية
});
```

## 🔌 العمل Offline

النظام يدعم العمل Offline بشكل كامل:

1. **التفعيل Offline:**
   - إنشاء ترخيص في سيرفر متصل
   - نسخ مفتاح الترخيص
   - تفعيله في سيرفر غير متصل

2. **التحقق Offline:**
   - النظام يقرأ من ملف `storage/app/license.key`
   - لا يحتاج اتصال بالإنترنت

## 📚 التوثيق الكامل

راجع الملفات:
- `INSTALLATION.md` - دليل التثبيت التفصيلي
- `OFFLINE_GUIDE.md` - دليل العمل Offline

## 📄 الترخيص

MIT License

