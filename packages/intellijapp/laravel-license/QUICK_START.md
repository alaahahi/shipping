# دليل البدء السريع

## 🚀 تثبيت Package في مشروع جديد

### 1. نسخ Package
```bash
# نسخ Package إلى مجلد packages في مشروعك الجديد
cp -r packages/intellijapp/laravel-license /path/to/new-project/packages/intellijapp/
```

### 2. إضافة إلى composer.json
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

### 3. تثبيت
```bash
composer require intellijapp/laravel-license:@dev
php artisan vendor:publish --tag=license-config
php artisan vendor:publish --tag=license-migrations
php artisan migrate
```

### 4. تخصيص Config
في `config/license.php`:
```php
'admin_check' => function($user) {
    // ضع منطق التحقق من Admin هنا
    // مثال:
    return $user->isAdmin();
    // أو:
    // return $user->type_id == UserType::where('name', 'admin')->first()?->id;
}
```

### 5. إنشاء ترخيص
```bash
php artisan license:generate --domain=example.com --type=standard
```

### 6. تفعيل الترخيص
```bash
# عبر API
curl -X POST http://your-domain.com/api/license/activate \
  -H "Content-Type: application/json" \
  -d '{"license_key": "your-license-key-here"}'
```

---

## ✅ Package جاهز للاستخدام!

