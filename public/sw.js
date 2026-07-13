// Service Worker - PWA محسّن - دعم Offline سريع
// الهدف: تسريع التطبيق + دعم offline بدون تخزين IndexedDB

const CACHE_NAME = 'shipping-v3.0.1'; // ⬆️ تحديث الإصدار - PWA محسّن
const RUNTIME_CACHE = 'shipping-runtime-v3.0.1';
const ASSETS_TO_CACHE = [
  '/',
  '/offline.html',
  '/app-shell.html'
];

// تثبيت Service Worker
self.addEventListener('install', (event) => {
  console.log('✅ SW v2.0: Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('📦 Caching essential assets...');
        return cache.addAll(ASSETS_TO_CACHE).catch(err => {
          console.log('⚠️ Some assets failed to cache, continuing anyway');
        });
      })
      .then(() => {
        console.log('✅ SW installed!');
        return self.skipWaiting(); // تفعيل فوري
      })
  );
});

// تفعيل Service Worker
self.addEventListener('activate', (event) => {
  console.log('✅ SW v2.0: Activating...');
  
  event.waitUntil(
    // مسح الـ caches القديمة
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames
          .filter(name => name !== CACHE_NAME)
          .map(name => {
            console.log('🗑️ Deleting old cache:', name);
            return caches.delete(name);
          })
      );
    }).then(() => {
      console.log('✅ SW v2.0 activated and claimed all clients!');
      return self.clients.claim(); // التحكم الفوري بجميع الصفحات
    })
  );
});

// 🎯 التعامل مع الطلبات - الحل الصحيح
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // ❌ تجاهل تماماً: الطلبات الخارجية
  if (url.origin !== location.origin) {
    return;
  }
  
  // ❌ تجاهل تماماً: طلبات POST/PUT/DELETE
  if (request.method !== 'GET') {
    return;
  }
  
  // ✅ دعم طلبات Inertia (زيارة الصفحات داخل التطبيق)
  const isInertiaRequest = request.headers.get('X-Inertia') || request.headers.get('X-Inertia-Version');
  if (isInertiaRequest) {
    event.respondWith(
      fetch(request)
        .then(response => {
          if (response && response.ok) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
          }
          return response;
        })
        .catch(async () => {
          const cached = await caches.match(request);
          if (cached) {
            return cached;
          }
          const shell = await caches.match('/app-shell.html');
          if (shell) {
            return shell;
          }
          return caches.match('/offline.html');
        })
    );
    return;
  }
  
  // ❌ تجاهل تماماً: API endpoints
  if (url.pathname.startsWith('/api/') || 
      url.pathname.includes('logout') || 
      url.pathname.includes('login')) {
    return;
  }
  
  // ✅ تعامل مع طلبات التصفّح (navigate) لتوفير fallback
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then(response => {
          const copy = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
          return response;
        })
        .catch(async () => {
          const cachedPage = await caches.match(request);
          if (cachedPage) {
            return cachedPage;
          }
          const shell = await caches.match('/app-shell.html');
          if (shell) {
            return shell;
          }
          return caches.match('/offline.html');
        })
    );
    return;
  }
  
  // ✅ فقط للملفات الثابتة: استخدم Network First strategy
  if (
    url.pathname.startsWith('/build/') ||
    url.pathname.startsWith('/icons/') ||
    url.pathname.startsWith('/img/') ||
    url.pathname.endsWith('.js') ||
    url.pathname.endsWith('.css') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.jpg') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.woff') ||
    url.pathname.endsWith('.woff2')
  ) {
    event.respondWith(
      fetch(request)
        .then(response => {
          // حفظ في cache فقط إذا نجح
          if (response && response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then(cache => {
              cache.put(request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          // fallback للـ cache
          return caches.match(request);
        })
    );
  }
  // لكل شيء آخر: دعه يمر بشكل طبيعي (no caching للصفحات الرئيسية)
});

// رسالة لتحديث Service Worker
self.addEventListener('message', (event) => {
  if (event.data === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

// 🚀 Background Sync - مزامنة SQLite تلقائياً عند عودة الإنترنت
self.addEventListener('sync', (event) => {
  console.log('🔄 Background Sync event:', event.tag);
  
  if (event.tag === 'sync-database') {
    event.waitUntil(syncDatabase());
  }
});

// دالة المزامنة على نمط Git: Pull أولاً (من السيرفر) ثم Push (إلى السيرفر)
async function syncDatabase() {
  try {
    console.log('🔄 بدء مزامنة قاعدة البيانات (نمط Git: Pull ثم Push)...');
    
    // 1. Pull: MySQL → SQLite (سحب التحديثات من السيرفر أولاً)
    console.log('📥 Pull: جلب التحديثات من MySQL...');
    const responseDown = await fetch('/api/sync-monitor/sync', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ direction: 'down' })
    });
    
    if (!responseDown.ok) {
      console.error('❌ فشل Pull:', responseDown.status);
      throw new Error('فشل سحب التحديثات من السيرفر');
    }
    const resultDown = await responseDown.json();
    console.log('✅ تم Pull بنجاح:', resultDown);
    
    // 2. Push: SQLite → MySQL (رفع التعديلات المحلية)
    console.log('📤 Push: رفع التعديلات المحلية إلى MySQL...');
    const responseUp = await fetch('/api/sync-monitor/sync', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        direction: 'up',
        safe_mode: true,
        create_backup: true
      })
    });
    
    let resultUp = null;
    if (!responseUp.ok) {
      console.error('❌ فشل Push:', responseUp.status);
    } else {
      resultUp = await responseUp.json();
      console.log('✅ تم Push بنجاح:', resultUp);
    }
    
    // إرسال رسالة للتطبيق
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
      client.postMessage({
        type: 'SYNC_COMPLETE',
        success: true,
        data: { pull: resultDown, push: resultUp }
      });
    });
    
  } catch (error) {
    console.error('❌ خطأ في Background Sync:', error);
    throw error; // لإعادة المحاولة تلقائياً
  }
}

console.log('🚀 Service Worker loaded!');

