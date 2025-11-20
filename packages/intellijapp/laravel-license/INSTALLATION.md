# دليل التثبيت التفصيلي

## 📋 المتطلبات

- PHP >= 8.0
- Laravel >= 9.0
- Composer

---

## 🚀 خطوات التثبيت

### 1. نسخ Package

#### الطريقة 1: نسخ مباشر
```bash
# نسخ Package إلى مجلد packages في مشروعك
cp -r packages/intellijapp/laravel-license /path/to/your/project/packages/intellijapp/
```

#### الطريقة 2: Git Submodule (إذا كان Package في Git)
```bash
git submodule add https://github.com/your-repo/laravel-license.git packages/intellijapp/laravel-license
```

---

### 2. إضافة إلى composer.json

افتح `composer.json` وأضف:

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

---

### 3. تثبيت Package

```bash
composer require intellijapp/laravel-license:@dev
```

---

### 4. نشر Config

```bash
php artisan vendor:publish --tag=license-config
```

سيتم نسخ `config/license.php` إلى مشروعك.

---

### 5. نشر Migrations

```bash
php artisan vendor:publish --tag=license-migrations
```

---

### 6. تشغيل Migrations

```bash
php artisan migrate
```

---

### 7. تخصيص Config

افتح `config/license.php` وخصص:

#### أ. Admin Check (مهم!)
```php
'admin_check' => function($user) {
    // مثال 1: إذا كان لديك isAdmin() method
    return $user->isAdmin();
    
    // مثال 2: إذا كان لديك UserType
    // return $user->type_id == UserType::where('name', 'admin')->first()?->id;
    
    // مثال 3: إذا كان لديك role
    // return $user->role === 'admin';
},
```

#### ب. إعدادات أخرى
```php
'enabled' => env('LICENSE_ENABLED', true),
'offline_mode' => env('LICENSE_OFFLINE_MODE', true),
'secret_key' => env('LICENSE_SECRET_KEY', 'your-secret-key-change-this'),
```

---

### 8. إعداد .env

أضف إلى `.env`:

```env
LICENSE_ENABLED=true
LICENSE_SECRET_KEY=your-secret-key-change-this-to-random-string
LICENSE_OFFLINE_MODE=true
LICENSE_CHECK_EVERY_REQUEST=false
LICENSE_GRACE_PERIOD=7
```

**⚠️ مهم:** غير `LICENSE_SECRET_KEY` إلى قيمة عشوائية قوية!

---

### 9. إنشاء ترخيص

```bash
php artisan license:generate --domain=example.com --type=standard --expires=2025-12-31
```

---

### 10. تفعيل الترخيص

#### الطريقة 1: عبر الواجهة
افتح المتصفح واذهب إلى: `/license/activate`

#### الطريقة 2: عبر API
```bash
curl -X POST http://your-domain.com/api/license/activate \
  -H "Content-Type: application/json" \
  -d '{"license_key": "your-license-key-here"}'
```

#### الطريقة 3: برمجياً
```php
use IntellijApp\License\Services\LicenseService;

$result = LicenseService::activate($licenseKey);
if ($result['success']) {
    echo "تم التفعيل بنجاح!";
}
```

---

## ✅ التحقق من التثبيت

```bash
php artisan license:verify
```

إذا رأيت معلومات الترخيص، فالتثبيت نجح! ✅

---

## 🔧 استكشاف الأخطاء

### المشكلة: Package غير موجود
**الحل:** تأكد من أن المسار في `composer.json` صحيح

### المشكلة: Migration فشل
**الحل:** تأكد من أن جدول `licenses` غير موجود مسبقاً

### المشكلة: Admin Check لا يعمل
**الحل:** تأكد من تخصيص `admin_check` في `config/license.php`

### المشكلة: Routes غير موجودة
**الحل:** تأكد من أن `LicenseServiceProvider` مسجل في `config/app.php` (يحدث تلقائياً)

---

## 📚 الخطوات التالية

بعد التثبيت:
1. ✅ راجع `README.md` للاستخدام
2. ✅ راجع `OFFLINE_GUIDE.md` للعمل Offline
3. ✅ ابدأ بإنشاء التراخيص

---

**تم التثبيت بنجاح! 🎉**

