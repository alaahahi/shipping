/**
 * Service Worker للعمل Offline
 * يحفظ الأصول الثابتة ويوفر تجربة أفضل عند فقدان الاتصال
 * 
 * 🔴 خط أحمر: جميع الطلبات الحساسة لا تمر عبر Service Worker
 * - POST, PUT, DELETE, PATCH: لا تُعترض أبداً
 * - Accounting, Payments, Transactions: لا تُعترض أبداً
 * - فقط GET requests للصفحات والأصول الثابتة تمر عبر SW للكاش
 */

const CACHE_VERSION = 'v2.1.0'; // إصلاح Inertia SPA - عدم اعتراض X-Inertia requests
const CACHE_NAME = `shipping-system-${CACHE_VERSION}`;

// مدة الانتظار القصوى للطلبات (3 ثوان للاستجابة السريعة)
const FETCH_TIMEOUT = 3000;

// تتبع الطلبات الجارية لمنع التكرار
const pendingRequests = new Map();

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

// قائمة المسارات الحساسة التي لا يجب اعتراضها أبداً (خط أحمر)
const SENSITIVE_PATHS = [
    '/api/addPaymentCar',
    '/api/addPaymentCarTotal',
    '/api/AddPayFromBalanceCar',
    '/api/DelPayFromBalanceCar',
    '/api/updateCarsS',
    '/api/DelCar',
    '/api/addCarContracts',
    '/api/editCarContracts',
    '/api/makeCarExit',
    '/api/makeDrivingDocument',
    '/api/checkClientBalance',
    'accounting',
    'salesDebt',
    'transaction',
    'payment',
    'wallet'
];

// التحقق من كون المسار حساس
function isSensitivePath(url) {
    const pathname = url.pathname.toLowerCase();
    return SENSITIVE_PATHS.some(path => pathname.includes(path.toLowerCase()));
}

// استراتيجية Cache
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // تجاهل الطلبات من نطاقات خارجية
    if (url.origin !== location.origin) {
        return;
    }

    // 🔴 تجاهل طلبات Inertia (XHR with X-Inertia header) - مهم جداً!
    // Inertia يحتاج الرد الطازج من الخادم دائماً
    if (request.headers.get('X-Inertia')) {
        console.log('⚡ Inertia request - تمرير مباشر:', url.pathname);
        return; // لا نعترض أبداً
    }

    // 🔴 خط أحمر: عدم اعتراض الطلبات الحساسة أبداً
    // اتركها تمر مباشرة للخادم بدون أي تدخل
    if (isSensitivePath(url) || request.method !== 'GET') {
        // لا نعترض، ندع المتصفح يتعامل معها مباشرة
        return;
    }

    // فقط الـ GET requests غير الحساسة تمر عبر Service Worker
    if (request.method === 'GET') {
        event.respondWith(handleGetRequest(request));
    }
});

/**
 * منع تكرار الطلبات
 * إذا كان نفس الطلب قيد التنفيذ، ننتظره بدلاً من إنشاء طلب جديد
 */
async function getDedupedRequest(requestKey, fetchFn) {
    // إذا كان الطلب قيد التنفيذ، نرجع نفس الـ Promise
    if (pendingRequests.has(requestKey)) {
        console.log('⏳ طلب مكرر تم منعه:', requestKey);
        return pendingRequests.get(requestKey);
    }

    // إنشاء طلب جديد
    const promise = fetchFn()
        .finally(() => {
            // إزالة الطلب من القائمة بعد انتهائه
            pendingRequests.delete(requestKey);
        });

    // حفظ الطلب في القائمة
    pendingRequests.set(requestKey, promise);

    return promise;
}

/**
 * Fetch مع timeout
 * يلغي الطلب إذا استغرق وقتاً طويلاً
 */
async function fetchWithTimeout(request, timeout = FETCH_TIMEOUT) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeout);

    try {
        const response = await fetch(request, {
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        return response;
    } catch (error) {
        clearTimeout(timeoutId);
        
        // إذا كان الخطأ بسبب الإلغاء، نرمي خطأ timeout
        if (error.name === 'AbortError') {
            throw new Error('Request timeout');
        }
        
        throw error;
    }
}

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
        // محاولة الإرسال للسيرفر مباشرة (بدون timeout في online mode)
        const response = await fetch(request.clone());
        // console.log('✅ طلب تعديل نجح:', request.url);
        return response;
    } catch (error) {
        console.warn('⚠️ فشل طلب التعديل (offline):', request.url);
        
        // إرجاع استجابة مخصصة تشير إلى أن الطلب في queue
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
 * استراتيجية Cache First (محسّنة)
 * يفضل Cache، ثم Network
 */
async function cacheFirst(request) {
    const cached = await caches.match(request);
    
    if (cached) {
        // إرجاع من الكاش مباشرة، وتحديث في الخلفية
        fetch(request).then(response => {
            if (response.ok) {
                caches.open(CACHE_NAME).then(cache => {
                    cache.put(request, response);
                });
            }
        }).catch(() => {
            // تجاهل الأخطاء في التحديث الخلفي
        });
        
        return cached;
    }
    
    // إذا لم يكن في الكاش، جلب من الشبكة
    try {
        const response = await fetch(request);
        
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone()).catch(() => {});
        }
        
        return response;
    } catch (error) {
        // console.warn('⚠️ فشل تحميل الأصل (offline):', request.url);
        return new Response('Offline', { status: 503 });
    }
}

/**
 * استراتيجية Network First (محسّنة)
 * يفضل Network، ثم Cache
 */
async function networkFirst(request) {
    try {
        // محاولة الطلب من الشبكة مباشرة (بدون timeout في online mode للسرعة)
        const response = await fetch(request);
        
        // احفظ في Cache للمرات القادمة فقط إذا كان الطلب ناجحاً
        if (response.ok && request.method === 'GET') {
            // الحفظ في الخلفية بدون انتظار (أسرع)
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone()).catch(() => {
                // تجاهل أخطاء الحفظ في الكاش
            });
        }
        
        return response;
    } catch (error) {
        // فقط في حالة offline نستخدم Cache
        const cached = await caches.match(request);
        
        if (cached) {
            console.log('📦 من الـ Cache (offline):', request.url);
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

/**
 * معالجة الرسائل من الصفحة
 */
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        console.log('⏭️ تخطي الانتظار وتفعيل Service Worker الجديد');
        self.skipWaiting();
    }
});

console.log('🚀 Service Worker جاهز!');

