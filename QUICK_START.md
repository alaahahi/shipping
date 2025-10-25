# ⚡ دليل البدء السريع - تحسين الأداء

## 🚀 الإعداد في 5 دقائق

### 1. تثبيت المكتبات
```bash
npm install
```

### 2. إعداد Queue (اختر واحد)

#### الخيار أ: Database Queue (سهل وسريع)
```bash
# في .env
QUEUE_CONNECTION=database

# إنشاء الجداول
php artisan queue:table
php artisan migrate

# تشغيل Worker
php artisan queue:work
```

#### الخيار ب: Redis (للأداء الأفضل)
```bash
# في .env
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# تشغيل Worker
php artisan queue:work redis
```

### 3. بناء الأصول
```bash
# للتطوير
npm run dev

# للإنتاج
npm run build
```

### 4. تفعيل Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## ✅ التحقق من التثبيت

### في المتصفح:

1. افتح الموقع
2. ستظهر أيقونة دائرية في أسفل اليسار (Sync Indicator) ✅
3. افتح Chrome DevTools → Console
4. ستجد رسائل:
   - ✅ قاعدة البيانات المحلية جاهزة
   - ✅ Service Worker جاهز

### اختبار Offline Mode:

1. في Chrome DevTools → Network
2. حدد "Offline"
3. جرّب حفظ بيانات - ستحفظ محلياً
4. أرجع "Online" - ستزامن تلقائياً

---

## 🎯 الاستخدام الأساسي

### في Vue Components:

```vue
<script setup>
// جلب البيانات (يعمل Online/Offline)
const fetchData = async () => {
    const response = await window.$api.get('/api/cars', {
        cache: true  // تخزين مؤقت
    });
    console.log(response.data);
};

// حفظ البيانات (يزامن تلقائياً)
const saveData = async (data) => {
    const response = await window.$api.post('/api/cars', data);
    if (response.queued) {
        console.log('سيتم المزامنة لاحقاً');
    }
};
</script>

<template>
    <div>
        <!-- أضف مؤشر المزامنة -->
        <SyncIndicator />
    </div>
</template>
```

### في Controllers:

```php
use App\Jobs\ProcessHeavyTaskJob;

// معالجة العمليات الثقيلة في الخلفية
public function store(Request $request)
{
    $car = Car::create($request->all());
    
    // Queue للعمليات الثقيلة
    ProcessHeavyTaskJob::dispatch('image_processing', $data);
    
    return response()->json(['success' => true]);
}
```

---

## 🔧 الأوامر المفيدة

```bash
# مراقبة Queue
php artisan queue:monitor

# إعادة محاولة Failed Jobs
php artisan queue:retry all

# مسح Cache
php artisan cache:clear

# إعادة تشغيل Queue
php artisan queue:restart
```

---

## 📊 النتائج المتوقعة

✅ **سرعة:** استجابة أسرع 3-5 مرات
✅ **استقرار:** لا توقف عند فقدان الاتصال
✅ **تجربة:** عمل سلس مع مزامنة تلقائية
✅ **موثوقية:** لا فقدان للبيانات

---

## ❓ مشاكل شائعة

**Pinia غير معرّف؟**
```bash
npm install pinia
npm run build
```

**Queue لا تعمل؟**
```bash
php artisan queue:restart
php artisan queue:work
```

**Service Worker لا يعمل؟**
- تأكد من HTTPS أو localhost
- امسح Cache المتصفح

---

## 📚 مزيد من التفاصيل

راجع `PERFORMANCE_GUIDE.md` للدليل الكامل.

---

**جاهز للعمل! 🎉**

