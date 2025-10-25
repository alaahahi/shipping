/**
 * تسجيل Service Worker
 * يتم استدعاؤه من app.js
 */

export async function registerServiceWorker() {
    // التحقق من دعم Service Worker
    if (!('serviceWorker' in navigator)) {
        console.warn('⚠️ Service Worker غير مدعوم في هذا المتصفح');
        return;
    }

    try {
        // تسجيل Service Worker
        const registration = await navigator.serviceWorker.register('/service-worker.js', {
            scope: '/'
        });

        console.log('✅ Service Worker مسجل بنجاح:', registration.scope);

        // مراقبة التحديثات
        registration.addEventListener('updatefound', () => {
            const newWorker = registration.installing;
            console.log('🔄 يتم تثبيت تحديث جديد...');

            newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    // يوجد تحديث جديد
                    console.log('✨ تحديث جديد متاح!');
                    
                    // إظهار إشعار للمستخدم
                    if (window.showUpdateNotification) {
                        window.showUpdateNotification();
                    } else {
                        console.log('💡 حدّث الصفحة للحصول على آخر التحديثات');
                    }
                }
            });
        });

        // التحقق من وجود تحديثات
        setInterval(() => {
            registration.update();
        }, 60000); // كل دقيقة

        // مراقبة رسائل Service Worker
        navigator.serviceWorker.addEventListener('message', (event) => {
            handleServiceWorkerMessage(event.data);
        });

        // تسجيل Background Sync
        if ('sync' in registration) {
            console.log('✅ Background Sync مدعوم');
            await registration.sync.register('sync-data');
        } else {
            console.warn('⚠️ Background Sync غير مدعوم');
        }

        return registration;
    } catch (error) {
        console.error('❌ فشل تسجيل Service Worker:', error);
    }
}

/**
 * معالجة رسائل Service Worker
 */
function handleServiceWorkerMessage(data) {
    switch (data.type) {
        case 'SYNC_COMPLETE':
            console.log('✅ اكتملت المزامنة:', new Date(data.timestamp));
            
            // تحديث واجهة المستخدم
            if (window.onSyncComplete) {
                window.onSyncComplete();
            }
            break;
            
        case 'CACHE_UPDATED':
            console.log('📦 تم تحديث الـ Cache');
            break;
            
        default:
            console.log('📨 رسالة من Service Worker:', data);
    }
}

/**
 * إلغاء تسجيل Service Worker
 */
export async function unregisterServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    try {
        const registration = await navigator.serviceWorker.getRegistration();
        
        if (registration) {
            await registration.unregister();
            console.log('✅ تم إلغاء تسجيل Service Worker');
        }
    } catch (error) {
        console.error('❌ فشل إلغاء تسجيل Service Worker:', error);
    }
}

/**
 * التحقق من حالة Service Worker
 */
export async function checkServiceWorkerStatus() {
    if (!('serviceWorker' in navigator)) {
        return { supported: false };
    }

    const registration = await navigator.serviceWorker.getRegistration();
    
    return {
        supported: true,
        registered: !!registration,
        active: !!registration?.active,
        waiting: !!registration?.waiting,
        installing: !!registration?.installing
    };
}

/**
 * إرسال رسالة لـ Service Worker
 */
export async function sendMessageToServiceWorker(message) {
    if (!('serviceWorker' in navigator) || !navigator.serviceWorker.controller) {
        console.warn('⚠️ Service Worker غير متاح');
        return;
    }

    navigator.serviceWorker.controller.postMessage(message);
}

/**
 * طلب المزامنة فوراً
 */
export async function requestSync() {
    const registration = await navigator.serviceWorker.ready;
    
    if ('sync' in registration) {
        try {
            await registration.sync.register('sync-data');
            console.log('🔄 تم طلب المزامنة');
            return true;
        } catch (error) {
            console.error('❌ فشل طلب المزامنة:', error);
            return false;
        }
    }
    
    return false;
}

/**
 * مسح كل الـ Caches
 */
export async function clearAllCaches() {
    if (!('caches' in window)) {
        console.warn('⚠️ Cache API غير مدعوم');
        return;
    }

    try {
        const cacheNames = await caches.keys();
        
        await Promise.all(
            cacheNames.map(cacheName => caches.delete(cacheName))
        );
        
        console.log('✅ تم مسح كل الـ Caches');
        return true;
    } catch (error) {
        console.error('❌ فشل مسح الـ Caches:', error);
        return false;
    }
}

/**
 * إظهار إشعار التحديث
 */
export function showUpdateNotification() {
    // يمكن استخدام Vue Toastification هنا
    if (window.$toast) {
        window.$toast.info('تحديث جديد متاح! حدّث الصفحة للحصول على آخر النسخة', {
            timeout: 0,
            closeOnClick: false,
            closeButton: false,
            action: [
                {
                    text: 'تحديث الآن',
                    onClick: () => {
                        window.location.reload();
                    }
                },
                {
                    text: 'لاحقاً',
                    onClick: (e, toastObject) => {
                        toastObject.goAway(0);
                    }
                }
            ]
        });
    } else {
        const reload = confirm('تحديث جديد متاح! هل تريد تحديث الصفحة الآن؟');
        if (reload) {
            window.location.reload();
        }
    }
}

// تصدير للاستخدام العام
window.showUpdateNotification = showUpdateNotification;

