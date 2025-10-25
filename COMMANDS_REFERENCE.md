# 📟 دليل الأوامر السريع

## 🚀 أوامر الإعداد الأولي

```bash
# تثبيت المكتبات
npm install

# إعداد Queue (Database)
php artisan queue:table
php artisan migrate

# بناء الأصول
npm run build

# تحسين الأداء
php artisan performance:optimize
```

---

## ⚙️ أوامر Queue

```bash
# تشغيل Queue Worker
php artisan queue:work

# تشغيل مع Redis
php artisan queue:work redis

# تشغيل في الخلفية (daemon)
php artisan queue:work --daemon

# مع إعادة محاولة 3 مرات
php artisan queue:work --daemon --tries=3

# مراقبة Queue
php artisan queue:monitor

# عرض Failed Jobs
php artisan queue:failed

# إعادة محاولة Job محدد
php artisan queue:retry {job_id}

# إعادة محاولة كل Failed Jobs
php artisan queue:retry all

# حذف Failed Job
php artisan queue:forget {job_id}

# إعادة تشغيل Queue Workers
php artisan queue:restart

# مسح كل Jobs
php artisan queue:flush
```

---

## 📦 أوامر Cache

```bash
# مسح Cache
php artisan cache:clear

# تخزين Config مؤقتاً
php artisan config:cache

# مسح Config Cache
php artisan config:clear

# تخزين Routes مؤقتاً
php artisan route:cache

# مسح Routes Cache
php artisan route:clear

# تخزين Views مؤقتاً
php artisan view:cache

# مسح Views Cache
php artisan view:clear

# مسح Compiled Classes
php artisan clear-compiled

# تحسين شامل
php artisan optimize

# مسح التحسين
php artisan optimize:clear
```

---

## 🎯 أوامر Performance (مخصصة)

```bash
# تحسين شامل (تنفيذ كل شيء)
php artisan performance:optimize

# مسح كل الـ Caches
php artisan performance:optimize --clear

# تخزين التكوينات فقط
php artisan performance:optimize --cache

# عرض معلومات الأداء
php artisan performance:optimize --info

# اختبار أداء الـ Cache
php artisan performance:optimize --benchmark

# Warm up Cache
php artisan performance:optimize --warmup
```

---

## 🗄️ أوامر Database

```bash
# تشغيل Migrations
php artisan migrate

# إعادة Migrations
php artisan migrate:fresh

# إعادة مع Seeders
php artisan migrate:fresh --seed

# Rollback آخر Migration
php artisan migrate:rollback

# عرض حالة Migrations
php artisan migrate:status

# إنشاء Migration جديد
php artisan make:migration create_table_name
```

---

## 🔨 أوامر Development

```bash
# تشغيل Dev Server (Vite)
npm run dev

# بناء للإنتاج
npm run build

# تشغيل Laravel Server
php artisan serve

# Tinker (PHP REPL)
php artisan tinker

# تشغيل Telescope (إذا كان مثبت)
php artisan telescope:install

# تشغيل Scheduler (للـ Cron Jobs)
php artisan schedule:run

# عرض Routes
php artisan route:list

# عرض Commands
php artisan list
```

---

## 🧹 أوامر التنظيف

```bash
# مسح كل شيء
php artisan optimize:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear

# مسح Logs
rm storage/logs/*.log

# مسح Compiled Views
rm -rf storage/framework/views/*

# مسح Cache Files
rm -rf storage/framework/cache/*
```

---

## 🐛 أوامر Debugging

```bash
# عرض الأخطاء في Log
tail -f storage/logs/laravel.log

# عرض آخر 50 سطر
tail -n 50 storage/logs/laravel.log

# Debug Bar Clear
php artisan debugbar:clear

# عرض التكوينات
php artisan config:show

# اختبار الاتصال بقاعدة البيانات
php artisan db:show

# عرض معلومات البيئة
php artisan env
```

---

## 🔐 أوامر Production

```bash
# التحسين الكامل للإنتاج
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# تشغيل Queue Worker (Production)
php artisan queue:work --daemon --tries=3 --timeout=300

# إيقاف Maintenance Mode
php artisan up

# تفعيل Maintenance Mode
php artisan down --secret="bypass-token"
```

---

## 📊 أوامر المراقبة

```bash
# مراقبة Queue
watch -n 1 php artisan queue:monitor

# عرض حالة الـ Cache
php artisan performance:optimize --info

# Benchmark الأداء
php artisan performance:optimize --benchmark

# عرض معلومات النظام
php -v
composer --version
npm --version

# عرض استخدام الذاكرة
php -i | grep memory_limit
```

---

## 🛠️ Windows Specific

```bash
# تشغيل كخدمة (NSSM)
nssm install LaravelQueue "C:\xampp\php\php.exe" "C:\xampp\htdocs\shipping\artisan queue:work --daemon"
nssm start LaravelQueue
nssm stop LaravelQueue
nssm restart LaravelQueue
nssm remove LaravelQueue

# عرض الخدمات
nssm list
```

---

## 🐧 Linux Specific

```bash
# Supervisor Commands
sudo supervisorctl status
sudo supervisorctl start laravel-queue:*
sudo supervisorctl stop laravel-queue:*
sudo supervisorctl restart laravel-queue:*
sudo supervisorctl reread
sudo supervisorctl update

# تشغيل في الخلفية (Screen)
screen -S queue
php artisan queue:work --daemon
# اضغط Ctrl+A ثم D للخروج

# العودة للـ Screen
screen -r queue

# عرض Screens
screen -ls
```

---

## 📝 أوامر NPM

```bash
# تثبيت
npm install

# تثبيت package محدد
npm install package-name

# تحديث Packages
npm update

# مسح node_modules
rm -rf node_modules
npm install

# مسح Cache
npm cache clean --force

# تشغيل Dev
npm run dev

# Build
npm run build

# عرض الـ Packages المثبتة
npm list --depth=0
```

---

## 🔄 أوامر Git (للمطورين)

```bash
# حالة التغييرات
git status

# إضافة التغييرات
git add .

# Commit
git commit -m "وصف التغيير"

# Push
git push origin main

# Pull
git pull origin main

# عرض التاريخ
git log --oneline

# إنشاء Branch
git checkout -b feature-name

# التبديل بين Branches
git checkout branch-name
```

---

## 🧪 أوامر الاختبار

```bash
# اختبار Service Worker
# في Chrome DevTools: Application → Service Workers

# اختبار Offline Mode
# في Chrome DevTools: Network → Offline

# اختبار Cache
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
>>> Cache::forget('test');

# اختبار Queue
php artisan tinker
>>> dispatch(new App\Jobs\SyncDataJob('test', ['data']));

# اختبار IndexedDB
# في Chrome DevTools: Application → Storage → IndexedDB
```

---

## 💡 Shortcuts مفيدة

```bash
# Alias مفيدة (أضفها في .bashrc أو .bash_profile)
alias art="php artisan"
alias queue="php artisan queue:work --daemon"
alias optimize="php artisan performance:optimize"
alias fresh="php artisan migrate:fresh --seed"

# استخدام:
art cache:clear
queue
optimize
```

---

## 🆘 أوامر الطوارئ

```bash
# النظام معلق؟
php artisan queue:restart
php artisan cache:clear
php artisan optimize:clear

# Queue لا تعمل؟
php artisan queue:failed
php artisan queue:retry all
php artisan queue:restart

# أخطاء في Views؟
php artisan view:clear

# أخطاء في Config؟
php artisan config:clear

# أخطاء في Routes؟
php artisan route:clear

# بطء شديد؟
php artisan performance:optimize --clear
php artisan performance:optimize --cache

# ذاكرة ممتلئة؟
php artisan cache:clear
php artisan queue:flush
rm storage/logs/*.log
```

---

## 📞 أوامر للمساعدة

```bash
# مساعدة لأمر محدد
php artisan help queue:work
php artisan help performance:optimize

# عرض كل الأوامر
php artisan list

# بحث عن أمر
php artisan list | grep cache

# عرض معلومات Laravel
php artisan about
```

---

## ⚡ Workflow اليومي

```bash
# الصباح (بدء العمل)
git pull
composer install
npm install
php artisan migrate
php artisan queue:work &
npm run dev

# أثناء العمل
git status
git add .
git commit -m "..."
npm run build

# نهاية اليوم
git push
php artisan queue:restart
php artisan optimize
```

---

## 🎯 Deployment Workflow

```bash
# على السيرفر
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan performance:optimize
php artisan queue:restart
sudo supervisorctl restart laravel-queue:*
```

---

**📌 احفظ هذا الملف كمرجع سريع!**

**💡 نصيحة:** أضف أكثر الأوامر استخداماً كـ aliases في terminal الخاص بك.

