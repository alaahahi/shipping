/**
 * IndexedDB للعقود - PWA Stage 3
 * تخزين قوي وسريع بدلاً من localStorage
 */

import { ref, onMounted } from 'vue';
import axios from 'axios';

const DB_NAME = 'ShippingDB';
const DB_VERSION = 1;
const STORE_NAME = 'contracts';

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

export function useIndexedDB() {
    const isOnline = ref(navigator.onLine);
    const pendingCount = ref(0);
    const isSyncing = ref(false);
    let db = null;

    /**
     * فتح/إنشاء قاعدة البيانات
     */
    const openDB = () => {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = () => {
                console.error('❌ فشل فتح IndexedDB:', request.error);
                reject(request.error);
            };

            request.onsuccess = () => {
                db = request.result;
                console.log('✅ IndexedDB opened successfully');
                resolve(db);
            };

            request.onupgradeneeded = (event) => {
                db = event.target.result;
                
                // إنشاء object store إذا لم يكن موجوداً
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    const objectStore = db.createObjectStore(STORE_NAME, { 
                        keyPath: 'id',
                        autoIncrement: true 
                    });
                    
                    // إنشاء indexes للبحث السريع
                    objectStore.createIndex('timestamp', 'timestamp', { unique: false });
                    objectStore.createIndex('synced', 'synced', { unique: false });
                    objectStore.createIndex('offline_id', 'offline_id', { unique: true });
                    
                    console.log('✅ Object store created');
                }
            };
        });
    };

    /**
     * حفظ عقد
     */
    const saveContract = async (contractData) => {
        console.log('🔄 محاولة حفظ العقد...');
        console.log('📡 حالة الاتصال:', navigator.onLine ? 'Online' : 'Offline');
        
        // تجربة الحفظ online أولاً
        if (navigator.onLine) {
            try {
                console.log('📤 إرسال للسيرفر...');
                const response = await axios.post('/api/addCarContract', contractData);
                console.log('✅ تم الحفظ online مباشرة:', response.data);
                return { success: true, online: true, data: response.data };
            } catch (error) {
                console.log('⚠️ فشل الحفظ online:', error.message);
                console.log('💾 سنحفظ offline بدلاً من ذلك...');
                // فشل، احفظ offline
            }
        } else {
            console.log('📡 Offline detected، حفظ مباشر في IndexedDB');
        }
        
        // حفظ offline
        const result = await saveOffline(contractData);
        console.log('💾 نتيجة الحفظ offline:', result);
        return result;
    };

    /**
     * حفظ offline في IndexedDB
     */
    const saveOffline = async (contractData) => {
        console.log('💾 بدء حفظ offline...');
        console.log('📦 البيانات:', contractData);
        
        try {
            if (!db) {
                console.log('⚠️ DB غير مفتوح، فتح الآن...');
                await openDB();
            }
            
            console.log('✅ DB جاهز:', db ? 'نعم' : 'لا');

            return new Promise((resolve, reject) => {
                try {
                    const transaction = db.transaction([STORE_NAME], 'readwrite');
                    const store = transaction.objectStore(STORE_NAME);

                    const uuid = (contractData && contractData.uuid) ? contractData.uuid : generateUuid();
                    const contract = {
                        ...contractData,
                        uuid,
                        offline_id: uuid,
                        timestamp: new Date().toISOString(),
                        synced: false,
                        created_offline: true
                    };

                    console.log('📝 العقد للحفظ:', contract);

                    const request = store.add(contract);

                request.onsuccess = async () => {
                    console.log('✅✅✅ تم الحفظ في IndexedDB بنجاح! ID:', request.result);
                    
                    // تحديث العداد فوراً
                    console.log('🔄 سنحدث العداد الآن...');
                    
                    // تحديث مباشر أولاً (قبل استدعاء updateCount)
                    pendingCount.value = pendingCount.value + 1;
                    console.log('📊 العداد المباشر:', pendingCount.value);
                    
                    // ثم تحديث من DB للتأكد
                    setTimeout(async () => {
                        await updateCount();
                        console.log('📊 العداد من DB:', pendingCount.value);
                    }, 100);
                    
                    // 🚀 تسجيل Background Sync لمزامنة تلقائية
                    await registerBackgroundSync();
                    
                    resolve({ success: true, online: false, id: request.result });
                };

                    request.onerror = () => {
                        console.error('❌ فشل الحفظ في IndexedDB:', request.error);
                        reject(request.error);
                    };
                } catch (err) {
                    console.error('❌ خطأ في transaction:', err);
                    reject(err);
                }
            });
        } catch (error) {
            console.error('❌ خطأ عام في saveOffline:', error);
            return { success: false, error };
        }
    };

    /**
     * الحصول على جميع العقود غير المزامنة
     */
    const getPendingContracts = async () => {
        try {
            console.log('📋 قراءة العقود المعلقة من IndexedDB...');
            
            if (!db) {
                console.log('⚠️ DB غير مفتوح في getPendingContracts، فتح الآن...');
                await openDB();
            }

            return new Promise((resolve, reject) => {
                const transaction = db.transaction([STORE_NAME], 'readonly');
                const store = transaction.objectStore(STORE_NAME);
                const index = store.index('synced');
                const request = index.getAll(false); // synced = false

                request.onsuccess = () => {
                    const pending = request.result || [];
                    console.log('📦 وجدنا', pending.length, 'عقد معلق (synced=false)');
                    if (pending.length > 0) {
                        console.log('العقود:', pending.map(c => c.offline_id));
                    }
                    resolve(pending);
                };

                request.onerror = () => {
                    console.error('❌ خطأ في قراءة العقود:', request.error);
                    reject(request.error);
                };
            });
        } catch (error) {
            console.error('❌ خطأ في getPendingContracts:', error);
            return [];
        }
    };

    /**
     * حذف عقد بعد المزامنة
     */
    const deleteContract = async (id) => {
        try {
            if (!db) await openDB();

            return new Promise((resolve, reject) => {
                const transaction = db.transaction([STORE_NAME], 'readwrite');
                const store = transaction.objectStore(STORE_NAME);
                const request = store.delete(id);

                request.onsuccess = () => {
                    console.log('✅ تم حذف العقد:', id);
                    resolve(true);
                };

                request.onerror = () => {
                    reject(request.error);
                };
            });
        } catch (error) {
            console.error('❌ خطأ في deleteContract:', error);
            return false;
        }
    };

    /**
     * مزامنة جميع العقود المعلقة
     */
    const syncAll = async () => {
        if (isSyncing.value) {
            console.log('⏳ المزامنة جارية بالفعل...');
            return 0;
        }

        try {
            isSyncing.value = true;
            const contracts = await getPendingContracts();

            if (contracts.length === 0) {
                console.log('✅ لا يوجد عقود للمزامنة');
                return 0;
            }

            console.log(`🔄 بدء مزامنة ${contracts.length} عقد...`);
            let synced = 0;
            let failed = 0;

            for (const contract of contracts) {
                try {
                    // إزالة البيانات الخاصة بـ IndexedDB قبل الإرسال (uuid يبقى في contractData للمزامنة)
                    const { id, offline_id, timestamp, synced: _, created_offline, ...contractData } = contract;
                    
                    await axios.post('/api/addCarContract', contractData);
                    
                    // حذف من IndexedDB بعد النجاح
                    await deleteContract(id);
                    
                    console.log(`✅ تمت مزامنة: ${offline_id}`);
                    synced++;
                    
                    // تأخير صغير بين الطلبات
                    await new Promise(resolve => setTimeout(resolve, 500));
                } catch (error) {
                    console.error(`❌ فشل: ${contract.offline_id}`, error);
                    failed++;
                }
            }

            await updateCount();
            
            console.log(`✅ تمت مزامنة ${synced} عقد، فشل ${failed}`);
            return synced;
        } catch (error) {
            console.error('❌ خطأ في syncAll:', error);
            return 0;
        } finally {
            isSyncing.value = false;
        }
    };

    /**
     * تحديث عداد العقود المعلقة
     */
    const updateCount = async () => {
        try {
            console.log('🔄 تحديث العداد...');
            const contracts = await getPendingContracts();
            console.log('📦 عدد العقود المعلقة:', contracts.length);
            pendingCount.value = contracts.length;
            console.log('📊 pendingCount.value =', pendingCount.value);
            
            // إجبار Vue على التحديث
            if (contracts.length > 0) {
                console.log('✅✅✅ يوجد', contracts.length, 'عقد معلق - الشريط يجب أن يظهر!');
            } else {
                console.log('ℹ️ لا يوجد عقود معلقة');
            }
        } catch (error) {
            console.error('❌ خطأ في updateCount:', error);
            pendingCount.value = 0;
        }
    };

    /**
     * الحصول على جميع العقود (للعرض)
     */
    const getAllContracts = async () => {
        try {
            if (!db) await openDB();

            return new Promise((resolve, reject) => {
                const transaction = db.transaction([STORE_NAME], 'readonly');
                const store = transaction.objectStore(STORE_NAME);
                const request = store.getAll();

                request.onsuccess = () => {
                    resolve(request.result || []);
                };

                request.onerror = () => {
                    reject(request.error);
                };
            });
        } catch (error) {
            console.error('❌ خطأ في getAllContracts:', error);
            return [];
        }
    };

    /**
     * مسح جميع العقود (للاختبار)
     */
    const clearAll = async () => {
        try {
            if (!db) await openDB();

            return new Promise((resolve, reject) => {
                const transaction = db.transaction([STORE_NAME], 'readwrite');
                const store = transaction.objectStore(STORE_NAME);
                const request = store.clear();

                request.onsuccess = () => {
                    console.log('✅ تم مسح جميع العقود');
                    updateCount();
                    resolve(true);
                };

                request.onerror = () => {
                    reject(request.error);
                };
            });
        } catch (error) {
            console.error('❌ خطأ في clearAll:', error);
            return false;
        }
    };

    /**
     * مراقبة الاتصال
     */
    const handleOnline = () => {
        console.log('🌐 عاد الاتصال بالإنترنت');
        isOnline.value = true;
        
        // انتظر قليلاً ثم زامن
        setTimeout(async () => {
            await updateCount();
            if (pendingCount.value > 0) {
                console.log(`⏳ يوجد ${pendingCount.value} عقد معلق، جاري المزامنة...`);
                
                // 🚀 محاولة Background Sync أولاً
                await registerBackgroundSync();
                
                // ثم المزامنة العادية كـ fallback
                await syncAll();
            }
        }, 2000);
    };

    const handleOffline = () => {
        console.log('📡 انقطع الاتصال بالإنترنت');
        isOnline.value = false;
    };

    /**
     * تسجيل Background Sync
     */
    const registerBackgroundSync = async () => {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.ready;
                if ('sync' in registration) {
                    await registration.sync.register('sync-contracts');
                    console.log('✅ Background Sync registered');
                }
            } catch (error) {
                console.log('⚠️ Background Sync غير مدعوم، سنستخدم المزامنة العادية');
            }
        }
    };

    /**
     * استقبال رسائل من Service Worker
     */
    const setupMessageListener = () => {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data.type === 'SYNC_COMPLETE') {
                    console.log(`✅ Background Sync complete: ${event.data.synced}/${event.data.total}`);
                    updateCount();
                }
            });
        }
    };

    /**
     * التهيئة
     */
    onMounted(async () => {
        try {
            // فتح قاعدة البيانات
            await openDB();
            
            // تحديث العداد
            await updateCount();
            
            // إعداد مستمع الرسائل
            setupMessageListener();
            
            // مراقبة الاتصال
            window.addEventListener('online', handleOnline);
            window.addEventListener('offline', handleOffline);
            
            // إذا كان online وهناك عقود معلقة، زامن
            if (navigator.onLine && pendingCount.value > 0) {
                console.log(`⏳ يوجد ${pendingCount.value} عقد معلق، جاري المزامنة...`);
                // محاولة Background Sync أولاً
                await registerBackgroundSync();
                // ثم المزامنة العادية كـ fallback
                setTimeout(() => syncAll(), 3000);
            }
            
            console.log('✅ IndexedDB initialized');
        } catch (error) {
            console.error('❌ فشل تهيئة IndexedDB:', error);
        }
    });

    return {
        isOnline,
        pendingCount,
        isSyncing,
        saveContract,
        syncAll,
        getPendingContracts,
        getAllContracts,
        clearAll,
        updateCount
    };
}

