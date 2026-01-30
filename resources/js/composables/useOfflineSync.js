/**
 * نظام المزامنة السريع - استخدام SQLite مباشرة
 * بدون IndexedDB - فقط SQLite للمزامنة
 */

import { ref, onMounted } from 'vue';
import axios from 'axios';

function generateUuid() {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

export function useOfflineSync() {
    const isOnline = ref(navigator.onLine);
    const isSyncing = ref(false);

    /**
     * حفظ عقد - مباشرة في SQLite (Online أو Offline)
     */
    const saveContract = async (contractData) => {
        const payload = { ...contractData };
        if (!payload.uuid && (!payload.id || payload.id === 0)) {
            payload.uuid = generateUuid();
        }
        try {
            // محاولة الحفظ مباشرة - Laravel سيستخدم SQLite إذا كان Offline
            const response = await axios.post('/api/addCarContract', payload, {
                timeout: 10000, // 10 ثواني
                headers: {
                    'Accept': 'application/json',
                }
            });
            
            return { 
                success: true, 
                online: isOnline.value,
                data: response.data,
                id: response.data?.id || response.data?.data?.id 
            };
        } catch (error) {
            // إذا فشل، Laravel سيعيد استخدام SQLite تلقائياً
            if (error.code === 'NETWORK_ERROR' || !navigator.onLine) {
                // محاولة مرة أخرى - Laravel يستخدم SQLite
                try {
                    const response = await axios.post('/api/addCarContract', payload, {
                        timeout: 5000,
                        headers: {
                            'Accept': 'application/json',
                        }
                    });
                    
                    return { 
                        success: true, 
                        online: false,
                        data: response.data,
                        id: response.data?.id || response.data?.data?.id 
                    };
                } catch (retryError) {
                    throw new Error('فشل الحفظ في SQLite المحلي');
                }
            }
            
            throw error;
        }
    };

    /**
     * مزامنة تلقائية عند عودة الإنترنت
     */
    const handleOnline = async () => {
        console.log('🌐 عاد الاتصال بالإنترنت - بدء المزامنة التلقائية...');
        isOnline.value = true;
        
        if (isSyncing.value) {
            return;
        }

        try {
            isSyncing.value = true;
            
            // 🚀 استخدام Background Sync أولاً (يعمل حتى لو كان التطبيق مغلق)
            if ('serviceWorker' in navigator && 'sync' in (await navigator.serviceWorker.ready)) {
                try {
                    const registration = await navigator.serviceWorker.ready;
                    await registration.sync.register('sync-database');
                    console.log('✅ Background Sync registered - سيتم المزامنة تلقائياً');
                } catch (error) {
                    console.log('⚠️ Background Sync غير مدعوم، استخدام المزامنة العادية');
                    // Fallback: مزامنة عادية - من SQLite إلى MySQL أولاً (مع Safe Mode)
                    await axios.post('/api/sync-monitor/sync', {
                        direction: 'up', // من SQLite إلى MySQL (نقل البيانات المحلية للسيرفر)
                        safe_mode: true, // Safe Mode: إضافة فقط، لا تحديث
                        create_backup: true // إنشاء نسخة احتياطية
                    }, {
                        timeout: 60000
                    });
                    // ثم مزامنة من MySQL إلى SQLite للتأكد من التحديثات
                    await axios.post('/api/sync-monitor/sync', {
                        direction: 'down' // من MySQL إلى SQLite
                    }, {
                        timeout: 60000
                    });
                }
            } else {
                // Fallback: مزامنة عادية - من SQLite إلى MySQL أولاً (مع Safe Mode)
                await axios.post('/api/sync-monitor/sync', {
                    direction: 'up', // من SQLite إلى MySQL (نقل البيانات المحلية للسيرفر)
                    safe_mode: true, // Safe Mode: إضافة فقط، لا تحديث
                    create_backup: true // إنشاء نسخة احتياطية
                }, {
                    timeout: 60000
                });
                // ثم مزامنة من MySQL إلى SQLite للتأكد من التحديثات
                await axios.post('/api/sync-monitor/sync', {
                    direction: 'down' // من MySQL إلى SQLite
                }, {
                    timeout: 60000
                });
                console.log('✅ تمت المزامنة التلقائية بنجاح');
            }
        } catch (error) {
            console.error('❌ فشلت المزامنة التلقائية:', error);
        } finally {
            isSyncing.value = false;
        }
    };

    const handleOffline = () => {
        console.log('📡 انقطع الاتصال - استخدام SQLite المحلي');
        isOnline.value = false;
    };

    /**
     * التهيئة
     */
    onMounted(() => {
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);
        
        // إذا كان online عند التحميل، تأكد من المزامنة
        if (navigator.onLine) {
            handleOnline();
        }
    });

    return {
        isOnline,
        isSyncing,
        saveContract
    };
}

