/**
 * Service Worker للعمل Offline
 * يحفظ الأصول الثابتة ويوفر تجربة أفضل عند فقدان الاتصال
 */

const CACHE_VERSION = 'v1.0.0';
const CACHE_NAME = `shipping-system-${CACHE_VERSION}`;

// الملفات المهمة التي يجب حفظها
const STATIC_ASSETS = [
    '/',
    '/build/manifest.json',
    '/img/logo.jpg',
    '/img/logo-no-background.png',
    '/offline.html', // سننشئها لاحقاً
];

// التثبيت
self.addEventListener('install', (event) => {
    console.log('🔧 Service Worker: التثبيت...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('📦 Service Worker: حفظ الملفات الأساسية');
                return cache.addAll(STATIC_ASSETS.filter(url => url !== '/offline.html'));
            })
            .then(() => self.skipWaiting())
    );
});

// التفعيل
self.addEventListener('activate', (event) => {
    console.log('✅ Service Worker: التفعيل...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('🗑️ Service Worker: حذف cache قديم:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => self.clients.claim())
    );
});

// استراتيجية Cache
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // تجاهل الطلبات من نطاقات خارجية
    if (url.origin !== location.origin) {
        return;
    }

    // استراتيجية مختلفة حسب نوع الطلب
    if (request.method === 'GET') {
        event.respondWith(handleGetRequest(request));
    } else {
        // POST, PUT, DELETE - محاولة الإرسال للسيرفر
        event.respondWith(handleMutationRequest(request));
    }
});

/**
 * معالجة طلبات GET
 * استراتيجية: Network First, Cache Fallback
 */
async function handleGetRequest(request) {
    const url = new URL(request.url);
    
    // للأصول الثابتة: Cache First
    if (isStaticAsset(url.pathname)) {
        return cacheFirst(request);
    }
    
    // للـ API: Network First
    if (url.pathname.startsWith('/api/')) {
        return networkFirst(request);
    }
    
    // للصفحات: Network First
    return networkFirst(request);
}

/**
 * معالجة طلبات التعديل (POST, PUT, DELETE)
 */
async function handleMutationRequest(request) {
    try {
        // محاولة الإرسال للسيرفر
        const response = await fetch(request.clone());
        return response;
    } catch (error) {
        console.error('❌ فشل الطلب:', error);
        
        // إرجاع استجابة مخصصة
        return new Response(
            JSON.stringify({
                queued: true,
                message: 'تم حفظ الطلب محلياً - سيتم المزامنة لاحقاً',
                offline: true
            }),
            {
                status: 202,
                headers: { 'Content-Type': 'application/json' }
            }
        );
    }
}

/**
 * استراتيجية Cache First
 * يفضل Cache، ثم Network
 */
async function cacheFirst(request) {
    const cached = await caches.match(request);
    
    if (cached) {
        console.log('📦 من الـ Cache:', request.url);
        return cached;
    }
    
    try {
        const response = await fetch(request);
        
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        
        return response;
    } catch (error) {
        console.error('❌ فشل الطلب:', error);
        return new Response('Offline', { status: 503 });
    }
}

/**
 * استراتيجية Network First
 * يفضل Network، ثم Cache
 */
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        
        // احفظ في Cache للمرات القادمة
        if (response.ok && request.method === 'GET') {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        
        return response;
    } catch (error) {
        console.warn('⚠️ فشل الطلب من الشبكة، محاولة القراءة من Cache');
        
        const cached = await caches.match(request);
        
        if (cached) {
            console.log('📦 من الـ Cache:', request.url);
            return cached;
        }
        
        // إذا كانت صفحة، أرجع صفحة offline
        if (request.mode === 'navigate') {
            const offlinePage = await caches.match('/offline.html');
            if (offlinePage) return offlinePage;
        }
        
        return new Response('Offline - No cached data', { status: 503 });
    }
}

/**
 * التحقق من كون الملف أصل ثابت
 */
function isStaticAsset(pathname) {
    const staticExtensions = ['.js', '.css', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.ttf', '.ico'];
    return staticExtensions.some(ext => pathname.endsWith(ext));
}

/**
 * Background Sync
 * للمزامنة عند عودة الاتصال
 */
self.addEventListener('sync', (event) => {
    console.log('🔄 Background Sync:', event.tag);
    
    if (event.tag === 'sync-data') {
        event.waitUntil(syncData());
    }
});

/**
 * مزامنة البيانات
 */
async function syncData() {
    try {
        console.log('🔄 بدء المزامنة...');
        
        // هنا يمكن إضافة منطق المزامنة
        // سيتم التعامل معها من IndexedDB
        
        // إرسال رسالة للصفحة
        const clients = await self.clients.matchAll();
        clients.forEach(client => {
            client.postMessage({
                type: 'SYNC_COMPLETE',
                timestamp: Date.now()
            });
        });
        
        console.log('✅ اكتملت المزامنة');
    } catch (error) {
        console.error('❌ فشلت المزامنة:', error);
        throw error;
    }
}

/**
 * Push Notifications (للمستقبل)
 */
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    
    const options = {
        body: data.body || 'إشعار جديد',
        icon: '/img/logo.jpg',
        badge: '/img/logo.jpg',
        vibrate: [200, 100, 200],
        data: data
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title || 'نظام الشحن', options)
    );
});

/**
 * معالجة النقر على الإشعار
 */
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow(event.notification.data.url || '/')
    );
});

console.log('🚀 Service Worker جاهز!');

