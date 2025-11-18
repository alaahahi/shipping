# دليل رفع المشروع على السيرفر

## ✅ تم إضافة الحماية من المزامنة على السيرفر

تم إضافة حماية شاملة لضمان عدم عمل المزامنة على السيرفر:

### 1. في `app/Console/Kernel.php`
- تم تعطيل Scheduled Tasks للمزامنة إذا كان `APP_ENV=server` أو `production`

### 2. في `app/Http/Controllers/SyncMonitorController.php`
- تم إضافة حماية في دالة `sync()` لرفض طلبات المزامنة من السيرفر

### 3. في `app/Console/Commands/SyncDatabase.php`
- تم إضافة حماية في Command لمنع تشغيل المزامنة من Terminal على السيرفر

## 📋 ملف .env للسيرفر

```env
APP_NAME=Laravel
APP_ENV=server
APP_KEY=base64:6COAvuJ1WJH3gvpMFydBLFaoYHkfKF4njcBf7Av6ikE=
APP_DEBUG=false
APP_URL=https://system.intellijapp.com
FRONTEND_URL=https://system.intellijapp.com
SESSION_DOMAIN=.system.intellijapp.com,.contract.intellijapp.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=209.172.2.60
DB_PORT=3306
DB_DATABASE=intellij_system
DB_USERNAME=intellij_system
DB_PASSWORD=QG5T4ECIGY!G

# المزامنة معطلة على السيرفر - لا حاجة لإعدادات SQLite
# SYNC_LOCAL_CONNECTION=sync_sqlite
# SYNC_SQLITE_PATH=...

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=525600
```

## 🔒 الحماية المضافة

### 1. Scheduled Tasks
```php
// في app/Console/Kernel.php
if (env('APP_ENV') === 'server' || env('APP_ENV') === 'production') {
    return; // لا تعمل المزامنة
}
```

### 2. API Endpoints
```php
// في app/Http/Controllers/SyncMonitorController.php
if (env('APP_ENV') === 'server' || env('APP_ENV') === 'production') {
    return response()->json(['error' => 'Sync disabled'], 403);
}
```

### 3. Console Commands
```php
// في app/Console/Commands/SyncDatabase.php
if (env('APP_ENV') === 'server' || env('APP_ENV') === 'production') {
    $this->error("❌ المزامنة معطلة على السيرفر");
    return 1;
}
```

## ✅ التحقق من الحماية

بعد الرفع على السيرفر، تأكد من:

1. **عدم عمل Scheduled Tasks:**
   ```bash
   # على السيرفر، لن تعمل المزامنة حتى لو تم تشغيل schedule:work
   php artisan schedule:work
   ```

2. **عدم عمل API Endpoints:**
   ```bash
   # محاولة المزامنة من API سترجع 403
   curl -X POST https://system.intellijapp.com/api/sync-monitor/sync
   ```

3. **عدم عمل Commands:**
   ```bash
   # على السيرفر، الأمر سيرفض العمل
   php artisan db:sync
   ```

## 📝 ملاحظات مهمة

- ✅ المزامنة تعمل فقط في البيئة المحلية (`APP_ENV=local`)
- ✅ على السيرفر (`APP_ENV=server`)، المزامنة معطلة تماماً
- ✅ لا حاجة لإعدادات SQLite على السيرفر
- ✅ قاعدة البيانات الوحيدة المستخدمة على السيرفر هي MySQL

## 🚀 خطوات الرفع

1. تأكد من أن `APP_ENV=server` في ملف `.env` على السيرفر
2. تأكد من عدم وجود ملف `database/sync.sqlite` على السيرفر (اختياري)
3. تأكد من أن `config/database.php` لا يحتوي على إعدادات `sync_sqlite` (اختياري)
4. رفع الملفات إلى السيرفر
5. تشغيل `php artisan config:cache` على السيرفر

## ⚠️ تحذيرات

- **لا تغير `APP_ENV` إلى `local` على السيرفر** - هذا سيسمح للمزامنة بالعمل
- **لا تشغل `php artisan schedule:work` على السيرفر** - حتى لو كان معطلاً، لا حاجة له
- **لا تحتاج لإعدادات SQLite على السيرفر** - المزامنة معطلة تماماً

