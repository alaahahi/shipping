# نظام تتبع تاريخ السيارات (Car History System)

## نظرة عامة

تم تطوير نظام شامل لتتبع جميع التغييرات على السيارات في النظام. النظام يحل محل معاملات رأس المال القديمة ويوفر تتبع مفصل لكل تغيير.

## المكونات

### 1. جدول `car_history`
```sql
- id: المفتاح الرئيسي
- car_id: ID السيارة
- action: نوع العملية (create, update, delete, restore)
- old_data: البيانات القديمة (JSON)
- new_data: البيانات الجديدة (JSON)
- changes: التغييرات المحددة (JSON)
- field_changed: الحقل الذي تغير
- description: وصف التغيير
- user_id: المستخدم الذي قام بالتغيير
- user_name: اسم المستخدم (للأرشفة)
- ip_address: عنوان IP
- created_at, updated_at: تواريخ الإنشاء والتحديث
```

### 2. Model `CarHistory`
- علاقات مع `Car` و `User`
- Scopes مفيدة (byCar, byAction, recent, etc.)
- Helper methods لإنشاء السجلات تلقائياً

### 3. Trait `TracksHistory`
- يتم تطبيقه على Model `Car`
- يتتبع الإنشاء، التحديث، والحذف تلقائياً

### 4. Controller `CarHistoryController`
- API endpoints لإدارة التاريخ
- وظائف الترحيل من الجدول القديم
- إحصائيات ومقارنات

### 5. صفحة عرض التاريخ
- Vue component لعرض تاريخ السيارة
- فلترة حسب التاريخ، النوع، المستخدم
- تفاصيل كل تغيير

## الاستخدام

### 1. عرض تاريخ سيارة
```php
// الويب
GET /car/{carId}/history

// API
GET /api/car/{carId}/history
```

### 2. ترحيل المعاملات القديمة
```php
// API للترحيل
POST /api/car-history/migrate-transactions
{
    "limit": 100,
    "confirm_delete": false
}
```

### 3. إحصائيات النظام
```php
GET /api/car-history/statistics
```

### 4. مقارنة إصدارين
```php
POST /api/car/{carId}/history/compare
{
    "history_id_1": 123,
    "history_id_2": 124
}
```

### 5. تنظيف السجلات القديمة
```php
POST /api/car-history/cleanup
{
    "older_than_days": 365
}
```

## التشغيل والاختبار

### تشغيل الاختبارات
```bash
# تشغيل جميع الاختبارات
php artisan test

# تشغيل اختبارات Car History فقط
php artisan test tests/Feature/CarHistoryTest.php

# تشغيل اختبارات النظام العام
php artisan test tests/Feature/SystemUpdatesTest.php

# تشغيل اختبار معين
php artisan test --filter it_tracks_car_creation_in_history
```

### ترحيل البيانات من النظام القديم
```bash
# تشغيل الترحيل عبر API
curl -X POST http://your-domain.com/api/car-history/migrate-transactions \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"limit": 100, "confirm_delete": true}'

# أو استخدام الـ command (إذا تم تطويره)
php artisan cars:migrate-transactions-to-history --dry-run
php artisan cars:migrate-transactions-to-history
```

## المميزات

### 1. التتبع التلقائي
- يتتبع جميع التغييرات تلقائياً
- يسجل المستخدم والتاريخ والتفاصيل

### 2. البحث والفلترة
- فلترة حسب النوع (create, update, delete)
- فلترة حسب المستخدم
- فلترة حسب التاريخ
- بحث في الوصف

### 3. المقارنة
- مقارنة بين إصدارين مختلفين
- عرض التغييرات بوضوح

### 4. الإحصائيات
- عدد التغييرات الكلي
- التوزيع حسب النوع
- نشاط المستخدمين
- النشاط الأخير

### 5. الأداء
- فهرسة شاملة للأداء العالي
- تقسيم البيانات حسب التاريخ
- تنظيف تلقائي للسجلات القديمة

## API Reference

### Endpoints الأساسية

```
GET    /api/car/{carId}/history              # قائمة تاريخ السيارة
GET    /api/car/{carId}/history/{historyId}  # تفاصيل سجل معين
POST   /api/car/{carId}/history/compare      # مقارنة سجلين
GET    /api/car-history/statistics           # إحصائيات النظام
POST   /api/car-history/migrate-transactions # ترحيل البيانات
POST   /api/car-history/cleanup              # تنظيف السجلات القديمة
```

### Parameters

#### قائمة التاريخ
```
GET /api/car/{carId}/history?action=update&user_id=1&date_from=2024-01-01&date_to=2024-12-31&page=1
```

#### الترحيل
```
POST /api/car-history/migrate-transactions
{
    "limit": 100,           // عدد السجلات للمعالجة في كل مرة
    "confirm_delete": false  // حذف السجلات من transactions بعد الترحيل
}
```

#### التنظيف
```
POST /api/car-history/cleanup
{
    "older_than_days": 365   // حذف السجلات الأقدم من X يوم
}
```

## الأمان والصلاحيات

- جميع APIs محمية بالمصادقة
- المستخدم يمكنه رؤية تاريخ السيارات في نفس الـ owner_id فقط
- تسجيل IP والمستخدم للمساءلة

## الأداء والتحسين

### الفهرسة
```sql
INDEX idx_car_history_car_created (car_id, created_at)
INDEX idx_car_history_action (action)
INDEX idx_car_history_user (user_id)
INDEX idx_car_history_field (field_changed)
```

### التقسيم (Partitioning)
يُنصح بتقسيم الجدول حسب الشهر أو السنة للأداء الأمثل.

### التنظيف التلقائي
```php
// في Task Scheduler
$schedule->command('car-history:cleanup 90')
         ->weekly()
         ->sundays()
         ->at('02:00');
```

## استكشاف الأخطاء

### مشاكل شائعة

1. **لا تظهر التغييرات الجديدة**
   - تأكد من أن Trait `TracksHistory` مُطبق على Model `Car`
   - تحقق من وجود المستخدم في الـ session

2. **بطء في الاستعلامات**
   - تأكد من وجود الفهارس
   - استخدم pagination
   - راجع استعلامات البحث

3. **خطأ في الترحيل**
   - استخدم `--dry-run` أولاً
   - تحقق من صحة البيانات في جدول transactions
   - راجع logs للأخطاء

### Logs
```php
// في CarHistoryController
Log::error('Migration error for transaction ' . $transaction->id, [
    'error' => $e->getMessage(),
    'transaction' => $transaction->toArray()
]);
```

## التطوير المستقبلي

### مميزات مقترحة
1. **Notifications**: إشعارات عند تغييرات مهمة
2. **Audit Trail**: سجل مفصل لجميع العمليات
3. **Bulk Operations**: عمليات جماعية على السجلات
4. **Export**: تصدير التاريخ إلى PDF/Excel
5. **API Webhooks**: إشعارات خارجية للتغييرات

### تحسينات الأداء
1. **Caching**: حفظ الإحصائيات في Cache
2. **Queue**: معالجة الترحيل في الخلفية
3. **Elasticsearch**: للبحث المتقدم
4. **Read Replicas**: للاستعلامات الكبيرة

## الدعم والصيانة

لأي استفسارات أو مشاكل، يرجى مراجعة:
1. Documentation في هذا الملف
2. Tests في `tests/Feature/CarHistoryTest.php`
3. Logs في `storage/logs/laravel.log`
4. Database migrations في `database/migrations/`

---

**تم تطوير النظام بواسطة**: فريق التطوير
**تاريخ آخر تحديث**: ديسمبر 2024
**الإصدار**: 1.0.0
