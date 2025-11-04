// Service Worker - المرحلة 1: Cache الأساسي
// الهدف: التطبيق يفتح حتى بدون نت

const CACHE_NAME = 'shipping-v1.0.0';
const ASSETS_TO_CACHE = [
  '/',
  '/build/assets/app.css',
  '/build/assets/app.js'
];

// تثبيت Service Worker
self.addEventListener('install', (event) => {
  console.log('✅ Service Worker: Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('📦 Caching app assets...');
        return cache.addAll(ASSETS_TO_CACHE);
      })
      .then(() => {
        console.log('✅ App assets cached successfully!');
        return self.skipWaiting(); // تفعيل فوري
      })
  );
});

// تفعيل Service Worker
self.addEventListener('activate', (event) => {
  console.log('✅ Service Worker: Activating...');
  
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
      console.log('✅ Service Worker activated!');
      return self.clients.claim(); // التحكم الفوري
    })
  );
});

// التعامل مع الطلبات
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // ✅ استراتيجية: Network First, Cache Fallback
  // جرب الشبكة أولاً، إذا فشلت استخدم الـ cache
  
  // تجاهل الطلبات الخارجية
  if (url.origin !== location.origin) {
    return;
  }
  
  // تجاهل Inertia XHR requests (للتنقل)
  if (request.headers.get('X-Inertia')) {
    console.log('🔄 Inertia request - passing through:', url.pathname);
    return; // دعها تمر بشكل طبيعي
  }
  
  // تجاهل API requests الحساسة
  if (
    request.method !== 'GET' || 
    url.pathname.startsWith('/api/') ||
    url.pathname.includes('addCarContract') ||
    url.pathname.includes('logout') ||
    url.pathname.includes('login')
  ) {
    console.log('🔒 Sensitive request - passing through:', url.pathname);
    return; // دعها تمر بشكل طبيعي
  }
  
  event.respondWith(
    fetch(request)
      .then(response => {
        // إذا نجح الطلب، احفظ في الـ cache
        if (response && response.status === 200) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        // إذا فشل (offline)، استخدم الـ cache
        console.log('📦 Serving from cache:', url.pathname);
        return caches.match(request);
      })
  );
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

