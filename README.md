# Laravel Offline Sync Guide

هذا الدليل يوضح كيفية تشغيل نسخة محلية من المشروع باستخدام SQLite والعمل بدون إنترنت، مع مزامنة تلقائية عند عودة الاتصال.

## 1. إعداد البيئة المحلية (Offline Instance)

1. **تثبيت الاعتمادات**
   ```bash
   composer install
   npm install
   ```
2. **ضبط الاتصال بـ SQLite**
   - إنشاء قاعدة بيانات محلية:
     ```bash
     touch database/database.sqlite
     ```
   - ضبط ملف `.env` أو `.env.local`:
     ```
     APP_ENV=local
     APP_DEBUG=true
     DB_CONNECTION=sqlite
     DB_DATABASE=database/database.sqlite
     SYNC_ENABLED=true
     SYNC_LOCAL_CONNECTION=sqlite
     SYNC_SERVER_URL=https://SERVER
     SYNC_API_TOKEN=your-api-token
     ```
3. **تشغيل السيرفر المحلي**
   ```bash
   php artisan serve --host=127.0.0.1 --port=8000
   ```

## 2. جدول المزامنة المحلي

- ملف SQL جاهز: `database/sql/sqlite_sync_jobs.sql`
- يمكن استخدام Migration `database/migrations/2025_11_14_000001_create_sync_jobs_table.php` إذا رغبت.

## 3. خدمة المزامنة

- المسار: `app/Services/Sync/SyncService.php`
- الدوال الرئيسية:
  - `recordLocalChange($modelKey, $operation, $payload)`
  - `pushLocalChangesToServer()`
  - `pullRemoteChanges()`
  - `applyRemoteChanges($changes)`

## 4. إعدادات المزامنة

- الملف: `config/sync.php`
- يحتوي على:
  - عنوان الخادم
  - قائمة الموديلات القابلة للمزامنة
  - الاتصال المحلي (SQLite)

## 5. API الخادم الرئيسي

- `POST /api/sync` → استقبال العمليات من الأجهزة
- `GET /api/changes` → إعادة التحديثات منذ آخر مزامنة
- الكود: `app/Http/Controllers/SyncController.php`

## 6. نظام UUID موحّد

- `AppServiceProvider` يقوم بتوليد UUID لأي سجل يحتوي عمود `uuid`.
- تأكد من إضافة الأعمدة `uuid`, `updated_at`, `deleted_at` للجداول المتزامنة.

## 7. جدولة المزامنة التلقائية

- الأمر: `sync:run` (ملف الأمر في `app/Console/Commands/RunSyncCommand.php`)
- مجدول في `app/Console/Kernel.php` كل دقيقة
- لتفعيل المجدول:
  ```
  php artisan schedule:run
  ```
  أو إعداد Cron:
  ```
  * * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
  ```

## 8. دمج الخدمة مع CRUD

- المثال:
  ```php
  app(App\Services\Sync\SyncService::class)
      ->recordLocalChange('car_contracts', 'create', $payload);
  ```
- استخدم مفاتيح الموديلات المحددة في `config/sync.php`.

## 9. ملاحظات

- فعّل `SYNC_API_TOKEN` للتحقق بين الأجهزة والخادم.
- يفضّل استخدام HTTPS.
- حدّث `config/sync.php` عند إضافة موديلات جديدة.
 
