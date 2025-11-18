# إعداد المزامنة التلقائية في Windows

## الطريقة 1: استخدام Laravel Scheduler (موصى بها)

### الخطوة 1: تشغيل Scheduler يدوياً للاختبار

افتح Command Prompt كـ Administrator وانتقل إلى مجلد المشروع:

```cmd
cd C:\xampp\htdocs\shipping
php artisan schedule:work
```

هذا الأمر سيعمل بشكل مستمر ويقوم بتنفيذ المهام المجدولة كل دقيقة.

### الخطوة 2: إعداد Task Scheduler في Windows

1. افتح **Task Scheduler** من قائمة Start
2. انقر على **Create Basic Task** من الجانب الأيمن
3. املأ المعلومات:
   - **Name**: Laravel Scheduler
   - **Description**: تشغيل Laravel Scheduler للمزامنة التلقائية
4. اضغط **Next**
5. اختر **When the computer starts** (عند بدء تشغيل الكمبيوتر)
6. اضغط **Next**
7. اختر **Start a program**
8. اضغط **Next**
9. في **Program/script** اكتب:
   ```
   C:\xampp\php\php.exe
   ```
10. في **Add arguments** اكتب:
    ```
    artisan schedule:work
    ```
11. في **Start in** اكتب:
    ```
    C:\xampp\htdocs\shipping
    ```
12. اضغط **Next** ثم **Finish**

### الخطوة 3: إعداد Task Scheduler باستخدام ملف VBS (لإخفاء النافذة)

1. انسخ ملف `run-scheduler.vbs` إلى مكان مناسب
2. في Task Scheduler:
   - **Program/script**: `wscript.exe`
   - **Add arguments**: `C:\xampp\htdocs\shipping\run-scheduler.vbs`
   - **Start in**: `C:\xampp\htdocs\shipping`

## الطريقة 2: استخدام Task Scheduler مباشرة (بدون Laravel Scheduler)

### إنشاء Task جديد:

1. افتح **Task Scheduler**
2. **Create Basic Task**
3. **Name**: Database Sync
4. **Trigger**: كل 5 دقائق
5. **Action**: Start a program
6. **Program**: `C:\xampp\php\php.exe`
7. **Arguments**: `artisan db:sync --direction=down`
8. **Start in**: `C:\xampp\htdocs\shipping`

### إنشاء Task ثاني للمزامنة العكسية:

1. نفس الخطوات السابقة
2. **Name**: Database Sync Reverse
3. **Trigger**: كل 10 دقائق
4. **Arguments**: `artisan db:sync --direction=up`

## الأوامر المتاحة:

### مزامنة من MySQL إلى SQLite:
```cmd
php artisan db:sync --direction=down
```

### مزامنة من SQLite إلى MySQL:
```cmd
php artisan db:sync --direction=up
```

### مزامنة جداول محددة:
```cmd
php artisan db:sync --direction=down --tables=car,users,transactions
```

### مزامنة جميع الجداول:
```cmd
php artisan db:sync --direction=down --all
```

## التحقق من عمل المزامنة:

1. تحقق من ملف الـ Log:
   ```
   storage\logs\sync.log
   ```

2. تحقق من Laravel Log:
   ```
   storage\logs\laravel.log
   ```

## ملاحظات مهمة:

- تأكد من أن PHP في PATH أو استخدم المسار الكامل
- تأكد من أن XAMPP يعمل
- تأكد من أن قاعدة البيانات متصلة
- يمكنك تعديل التوقيت في `app/Console/Kernel.php`

## تعديل توقيت المزامنة:

افتح `app/Console/Kernel.php` وعدل:

```php
// كل 5 دقائق
->everyFiveMinutes()

// كل 10 دقائق
->everyTenMinutes()

// كل ساعة
->hourly()

// كل 30 دقيقة
->everyThirtyMinutes()

// كل دقيقة
->everyMinute()
```

