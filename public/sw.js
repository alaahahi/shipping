// Service Worker - تحديث مُحسّن للـ SPA
// الهدف: عدم التدخل في Inertia + دعم offline

const CACHE_NAME = 'shipping-v2.1.0'; // ⬆️ تحديث الإصدار لتفعيل التحديث
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
  
  // ❌ تجاهل تماماً: طلبات Inertia (التنقل في SPA)
  if (request.headers.get('X-Inertia') || request.headers.get('X-Inertia-Version')) {
    return; // دع Inertia يتعامل معها بشكل طبيعي
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

// 🚀 المرحلة 4: Background Sync
self.addEventListener('sync', (event) => {
  console.log('🔄 Background Sync event:', event.tag);
  
  if (event.tag === 'sync-contracts') {
    event.waitUntil(syncContracts());
  }
});

// دالة المزامنة في الخلفية
async function syncContracts() {
  try {
    console.log('🔄 بدء المزامنة في الخلفية...');
    
    // فتح IndexedDB
    const db = await openDatabase();
    const contracts = await getPendingContracts(db);
    
    if (contracts.length === 0) {
      console.log('✅ لا يوجد عقود للمزامنة');
      return;
    }
    
    console.log(`📦 وجدنا ${contracts.length} عقد للمزامنة`);
    
    let synced = 0;
    for (const contract of contracts) {
      try {
        // إرسال العقد للسيرفر
        const response = await fetch('/api/addCarContract', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(contract)
        });
        
        if (response.ok) {
          // حذف من IndexedDB
          await deleteContractFromDB(db, contract.id);
          console.log(`✅ تمت مزامنة: ${contract.offline_id}`);
          synced++;
        } else {
          console.error(`❌ فشل: ${contract.offline_id}`, response.status);
        }
        
        // تأخير صغير
        await new Promise(resolve => setTimeout(resolve, 500));
      } catch (error) {
        console.error(`❌ خطأ في مزامنة: ${contract.offline_id}`, error);
      }
    }
    
    console.log(`✅ تمت مزامنة ${synced} من ${contracts.length} عقد في الخلفية`);
    
    // إرسال رسالة للتطبيق
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
      client.postMessage({
        type: 'SYNC_COMPLETE',
        synced: synced,
        total: contracts.length
      });
    });
    
  } catch (error) {
    console.error('❌ خطأ في Background Sync:', error);
    throw error; // لإعادة المحاولة تلقائياً
  }
}

// فتح IndexedDB
function openDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('ShippingDB', 1);
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

// الحصول على العقود المعلقة
function getPendingContracts(db) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['contracts'], 'readonly');
    const store = transaction.objectStore('contracts');
    const index = store.index('synced');
    const request = index.getAll(false);
    
    request.onsuccess = () => resolve(request.result || []);
    request.onerror = () => reject(request.error);
  });
}

// حذف عقد من IndexedDB
function deleteContractFromDB(db, id) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['contracts'], 'readwrite');
    const store = transaction.objectStore('contracts');
    const request = store.delete(id);
    
    request.onsuccess = () => resolve(true);
    request.onerror = () => reject(request.error);
  });
}

console.log('🚀 Service Worker loaded!');

