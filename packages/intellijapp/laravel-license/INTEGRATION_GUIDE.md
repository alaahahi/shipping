# دليل التكامل مع نظام المزامنة الموجود

## 📋 نظرة عامة

Package الترخيص متكامل بالكامل مع نظام المزامنة الموجود في المشروع. عند العمل على Local، سيتم استخدام SQLite تلقائياً لجميع الجداول بما فيها `licenses`.

---

## ✅ كيف يعمل التكامل

### 1. التبديل التلقائي للاتصال

في `AppServiceProvider`:
- النظام يتحقق من توفر MySQL
- إذا كان غير متاح، يبدل تلقائياً إلى `sync_sqlite`
- **Package الترخيص يستخدم Connection الافتراضي تلقائياً**

### 2. المزامنة التلقائية

`DatabaseSyncService` يقوم بمزامنة جميع الجداول من MySQL إلى SQLite:
- جدول `licenses` **يتم مزامنته تلقائياً** مع باقي الجداول
- لا حاجة لإعدادات إضافية

---

## 🔧 الإعداد

### 1. إعدادات .env

```env
# إعدادات قاعدة البيانات
DB_CONNECTION=mysql
DB_FAILOVER_ENABLED=true
DB_FALLBACK_CONNECTION=sync_sqlite

# إعدادات SQLite
SYNC_LOCAL_CONNECTION=sync_sqlite
SYNC_SQLITE_PATH=C:\xampp\htdocs\shipping\database\sync.sqlite
```

### 2. إعدادات Package (اختياري)

```env
# Package سيستخدم Connection الافتراضي تلقائياً
# لا حاجة لإعدادات إضافية!
```

---

## 🔄 المزامنة

### مزامنة تلقائية مع باقي الجداول

```php
use App\Services\DatabaseSyncService;

$syncService = new DatabaseSyncService();

// مزامنة جميع الجداول (بما فيها licenses)
$result = $syncService->syncFromMySQLToSQLite();
```

**ملاحظة:** جدول `licenses` **ليس مستثنى** من المزامنة، سيتم مزامنته تلقائياً!

### مزامنة يدوية للتراخيص فقط (اختياري)

```php
use IntellijApp\License\Services\LicenseSyncService;

// من MySQL إلى SQLite
LicenseSyncService::syncToSQLite();

// من SQLite إلى MySQL
LicenseSyncService::syncToMySQL();
```

---

## 🚀 الاستخدام

### في الكود

```php
use IntellijApp\License\Models\License;
use IntellijApp\License\Services\LicenseService;

// سيستخدم Connection الافتراضي تلقائياً
// في Local: SQLite
// في Production: MySQL
$license = LicenseService::getCurrentLicense();

// أو مباشرة
$license = License::where('domain', $domain)->first();
```

### في Commands

```bash
# سيستخدم Connection الافتراضي تلقائياً
php artisan license:verify
php artisan license:generate --domain=example.com
```

---

## 📊 كيف يعمل النظام

### في البيئة المحلية (Local)

1. **عند بدء التطبيق:**
   - `AppServiceProvider` يتحقق من MySQL
   - إذا كان غير متاح، يبدل إلى `sync_sqlite`
   - جميع Models (بما فيها License) تستخدم SQLite

2. **عند المزامنة:**
   - `DatabaseSyncService` يزامن جميع الجداول
   - جدول `licenses` يتم مزامنته تلقائياً

3. **عند القراءة/الكتابة:**
   - Package الترخيص يستخدم Connection الافتراضي
   - في Local = SQLite (أسرع بنسبة 80-90%)

### في البيئة الإنتاجية (Production)

- يستخدم MySQL دائماً
- لا يوجد تبديل تلقائي

---

## ⚙️ إعدادات Config

Package لا يحتاج إعدادات خاصة! يستخدم Connection الافتراضي تلقائياً.

إذا أردت تخصيص (اختياري):

```php
// config/license.php
'database' => [
    // سيستخدم Connection الافتراضي من config/database.php
    // لا حاجة لإعدادات إضافية
],
```

---

## 🔍 التحقق من التكامل

### 1. التحقق من المزامنة

```php
use App\Services\DatabaseSyncService;

$syncService = new DatabaseSyncService();
$result = $syncService->syncFromMySQLToSQLite(['licenses']);

// يجب أن ترى: licenses في النتائج
```

### 2. التحقق من Connection

```php
// في Local
$connection = config('database.default');
// يجب أن يكون: sync_sqlite (إذا كان MySQL غير متاح)

// في Production
$connection = config('database.default');
// يجب أن يكون: mysql
```

### 3. التحقق من البيانات

```php
// قراءة من SQLite
$license = License::on('sync_sqlite')->where('domain', $domain)->first();

// قراءة من MySQL
$license = License::on('mysql')->where('domain', $domain)->first();
```

---

## ⚠️ ملاحظات مهمة

### 1. جدول licenses في المزامنة

- **جدول `licenses` يتم مزامنته تلقائياً** مع باقي الجداول
- **ليس مستثنى** من `DatabaseSyncService`
- لا حاجة لإعدادات إضافية

### 2. التبديل التلقائي

- يتم في `AppServiceProvider::configureDatabaseFailover()`
- Package يستخدم Connection الافتراضي تلقائياً
- لا حاجة لتعديلات في Package

### 3. الأداء

- في Local: SQLite أسرع بنسبة 80-90%
- في Production: MySQL (مستقر وموثوق)

---

## 🐛 استكشاف الأخطاء

### المشكلة: لا يتم استخدام SQLite في Local
**الحل:**
1. تحقق من `DB_FAILOVER_ENABLED=true`
2. تحقق من `DB_FALLBACK_CONNECTION=sync_sqlite`
3. تحقق من أن MySQL غير متاح (سيتم التبديل تلقائياً)

### المشكلة: جدول licenses غير موجود في SQLite
**الحل:**
```php
// مزامنة يدوية
use App\Services\DatabaseSyncService;
$syncService = new DatabaseSyncService();
$syncService->syncFromMySQLToSQLite(['licenses']);
```

### المشكلة: البيانات غير متزامنة
**الحل:**
```php
// مزامنة كاملة
$syncService = new DatabaseSyncService();
$syncService->syncFromMySQLToSQLite();
```

---

## 📚 أمثلة

### مثال 1: مزامنة قبل العمل Offline

```php
use App\Services\DatabaseSyncService;

$syncService = new DatabaseSyncService();
$result = $syncService->syncFromMySQLToSQLite();

// جدول licenses سيتم مزامنته تلقائياً!
```

### مثال 2: استخدام Package في Local

```php
use IntellijApp\License\Services\LicenseService;

// سيستخدم SQLite تلقائياً في Local
$license = LicenseService::getCurrentLicense();
```

### مثال 3: التحقق من Connection

```php
// في Local
$defaultConnection = config('database.default');
// sync_sqlite

// Package يستخدم Connection الافتراضي
$license = License::where('domain', $domain)->first();
// سيقرأ من sync_sqlite تلقائياً
```

---

## ✅ الخلاصة

- ✅ Package متكامل بالكامل مع نظام المزامنة الموجود
- ✅ جدول `licenses` يتم مزامنته تلقائياً
- ✅ يستخدم SQLite في Local تلقائياً
- ✅ لا حاجة لإعدادات إضافية
- ✅ يعمل مع `DatabaseSyncService` الموجود

**النظام جاهز ويعمل تلقائياً! 🚀**

