# خطة تنفيذ: Package نظام الترخيص للاستخدام المحلي

## 🎯 الهدف

إنشاء Package محلي قابل لإعادة الاستخدام في مشاريع متعددة مع:
- ✅ **قصة تثبيت بسيطة** (نسخ مجلد أو path repository)
- ✅ **نظام Offline قوي** (يعمل بدون إنترنت)
- ✅ **سهولة التخصيص** (Config مرن)
- ✅ **إعادة استخدام كاملة**

---

## 📦 هيكل Package النهائي

```
laravel-license-package/
├── src/
│   ├── LicenseServiceProvider.php       # Service Provider الرئيسي
│   ├── Models/
│   │   └── License.php                  # Model الترخيص
│   ├── Services/
│   │   └── LicenseService.php           # خدمة الترخيص (Online/Offline)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── LicenseController.php     # Controller التفعيل والحالة
│   │   │   └── AdminLicenseController.php # Controller الإدارة
│   │   └── Middleware/
│   │       └── CheckLicense.php         # Middleware الحماية
│   ├── Console/
│   │   └── Commands/
│   │       ├── GenerateLicense.php      # إنشاء ترخيص
│   │       └── VerifyLicense.php        # التحقق من الترخيص
│   ├── Helpers/
│   │   └── LicenseHelper.php            # Helper Functions
│   ├── Database/
│   │   └── Migrations/
│   │       └── YYYY_MM_DD_create_licenses_table.php
│   ├── Config/
│   │   └── license.php                   # ملف الإعدادات
│   └── Installer/
│       └── LicenseInstaller.php         # مساعد التثبيت
├── resources/
│   └── views/
│       ├── license/
│       │   ├── activate.blade.php        # صفحة التفعيل
│       │   └── status.blade.php          # صفحة الحالة
│       └── admin/
│           └── licenses/
│               └── index.blade.php       # صفحة الإدارة
├── routes/
│   ├── web.php                           # Web Routes
│   └── api.php                           # API Routes
├── composer.json                          # Composer Config
├── README.md                              # دليل التثبيت والاستخدام
├── INSTALLATION.md                        # دليل التثبيت التفصيلي
└── OFFLINE_GUIDE.md                       # دليل العمل Offline

```

---

## 🚀 قصة التثبيت (Installation Story)

### الطريقة 1: Path Repository (مُوصى بها)

```json
// في composer.json للمشروع الجديد
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-license-package"
        }
    ],
    "require": {
        "your-vendor/laravel-license": "@dev"
    }
}
```

```bash
composer require your-vendor/laravel-license:@dev
php artisan vendor:publish --tag=license-config
php artisan vendor:publish --tag=license-migrations
php artisan migrate
php artisan license:install
```

### الطريقة 2: نسخ مباشر

```bash
# نسخ Package إلى packages/
cp -r laravel-license-package packages/your-vendor/laravel-license

# إضافة إلى composer.json
composer dump-autoload
```

---

## 🔌 نظام Offline - المتطلبات

### 1. حفظ الترخيص في ملف
- ✅ حفظ في `storage/app/license.key`
- ✅ JSON format مشفر
- ✅ يحتوي على جميع البيانات المطلوبة

### 2. قراءة من الملف عند عدم وجود DB
- ✅ التحقق من وجود الملف أولاً
- ✅ فك التشفير والتحقق من التوقيع
- ✅ استخدام البيانات من الملف

### 3. Fingerprint للتحقق
- ✅ MAC Address
- ✅ Server Info
- ✅ Domain/IP
- ✅ قابلة للتخصيص

### 4. Grace Period
- ✅ فترة سماح بعد انتهاء الترخيص
- ✅ تحذيرات قبل الانتهاء
- ✅ قابلة للتخصيص

---

## 📝 خطوات التنفيذ

### المرحلة 1: إنشاء هيكل Package ✅
- [x] إنشاء مجلدات Package
- [ ] إنشاء composer.json
- [ ] إنشاء Service Provider الأساسي

### المرحلة 2: نقل Core Files
- [ ] نقل License Model
- [ ] نقل LicenseService (مع تحسين Offline)
- [ ] نقل Helpers

### المرحلة 3: نقل Controllers & Middleware
- [ ] نقل LicenseController
- [ ] نقل AdminLicenseController
- [ ] نقل CheckLicense Middleware

### المرحلة 4: نقل Commands
- [ ] نقل GenerateLicense
- [ ] نقل VerifyLicense
- [ ] إنشاء Install Command

### المرحلة 5: Config & Migrations
- [ ] نقل Config
- [ ] نقل Migrations
- [ ] إعداد النشر

### المرحلة 6: Routes & Views
- [ ] نقل Routes
- [ ] إنشاء Blade Views (أو API فقط)
- [ ] تسجيل Routes

### المرحلة 7: Installer & Documentation
- [ ] إنشاء LicenseInstaller
- [ ] كتابة README.md
- [ ] كتابة INSTALLATION.md
- [ ] كتابة OFFLINE_GUIDE.md

---

## 🔧 تحسينات Offline

### 1. LicenseService Improvements

```php
// إضافة methods للـ Offline
- loadFromFile(): ?License
- saveToFile(License $license): bool
- verifyOffline(): bool
- getOfflineLicenseInfo(): array
```

### 2. Fallback Mechanism

```php
// في getCurrentLicense()
1. محاولة من Database
2. إذا فشل، محاولة من File
3. إذا فشل، return null
```

### 3. File-based Activation

```php
// إمكانية التفعيل من ملف مباشرة
- activateFromFile(string $filePath): array
- exportLicenseToFile(string $filePath): bool
```

---

## 🎨 التخصيص

### 1. Admin Check Configurable

```php
// في config/license.php
'admin_check' => function($user) {
    // Default implementation
    return $user->isAdmin(); // أو أي طريقة أخرى
}
```

### 2. Route Prefixes

```php
'route_prefix' => env('LICENSE_ROUTE_PREFIX', 'license'),
'admin_route_prefix' => env('LICENSE_ADMIN_PREFIX', 'admin/licenses'),
```

### 3. Fingerprint Customization

```php
'fingerprint_methods' => [
    'mac_address',
    'server_info',
    'domain',
    // يمكن إضافة المزيد
]
```

---

## 📚 التوثيق المطلوب

### 1. README.md
- نظرة عامة
- المتطلبات
- التثبيت السريع
- الاستخدام الأساسي

### 2. INSTALLATION.md
- خطوات التثبيت التفصيلية
- التكوين
- الاختبار

### 3. OFFLINE_GUIDE.md
- كيف يعمل Offline
- التفعيل Offline
- استكشاف الأخطاء

---

## ✅ Checklist التنفيذ

- [ ] المرحلة 1: هيكل Package
- [ ] المرحلة 2: Core Files
- [ ] المرحلة 3: Controllers & Middleware
- [ ] المرحلة 4: Commands
- [ ] المرحلة 5: Config & Migrations
- [ ] المرحلة 6: Routes & Views
- [ ] المرحلة 7: Installer & Docs
- [ ] الاختبار في مشروع جديد
- [ ] التوثيق النهائي

---

**جاهز للبدء! 🚀**

