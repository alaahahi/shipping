/**
 * نظام IndexedDB للتخزين المحلي
 * يسمح بحفظ البيانات محلياً والعمل Offline
 */

const DB_NAME = 'ShippingSystemDB';
const DB_VERSION = 3; // إزالة تخزين العقود من الفرونت

class LocalDatabase {
    constructor() {
        this.db = null;
        this.isOnline = navigator.onLine;
        this.syncQueue = [];
        this.listeners = new Set();
        
        // مراقبة حالة الاتصال
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
    }

    /**
     * فتح قاعدة البيانات المحلية
     */
    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                console.log('✅ قاعدة البيانات المحلية جاهزة');
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // إنشاء المخازن (Stores)
                if (!db.objectStoreNames.contains('cars')) {
                    const carsStore = db.createObjectStore('cars', { keyPath: 'id', autoIncrement: true });
                    carsStore.createIndex('status', 'status', { unique: false });
                    carsStore.createIndex('created_at', 'created_at', { unique: false });
                    carsStore.createIndex('chassis', 'chassis', { unique: false }); // للبحث بالشاصي
                    carsStore.createIndex('lot', 'lot', { unique: false }); // للبحث بالكاتي
                    carsStore.createIndex('user_id', 'user_id', { unique: false }); // للبحث بالعميل
                }

                // تم إلغاء تخزين العقود في الفرونت - العقود تُحفظ عبر API فقط

                if (!db.objectStoreNames.contains('transactions')) {
                    const transactionsStore = db.createObjectStore('transactions', { keyPath: 'id', autoIncrement: true });
                    transactionsStore.createIndex('date', 'date', { unique: false });
                }

                // مخزن للدفعات والسحوبات
                if (!db.objectStoreNames.contains('payments')) {
                    const paymentsStore = db.createObjectStore('payments', { keyPath: 'id', autoIncrement: true });
                    paymentsStore.createIndex('car_id', 'car_id', { unique: false });
                    paymentsStore.createIndex('user_id', 'user_id', { unique: false });
                    paymentsStore.createIndex('type', 'type', { unique: false }); // payment / withdrawal
                    paymentsStore.createIndex('created_at', 'created_at', { unique: false });
                    paymentsStore.createIndex('synced', 'synced', { unique: false });
                }

                if (!db.objectStoreNames.contains('sync_queue')) {
                    const syncStore = db.createObjectStore('sync_queue', { keyPath: 'id', autoIncrement: true });
                    syncStore.createIndex('timestamp', 'timestamp', { unique: false });
                    syncStore.createIndex('synced', 'synced', { unique: false });
                }

                if (!db.objectStoreNames.contains('cache')) {
                    const cacheStore = db.createObjectStore('cache', { keyPath: 'key' });
                    cacheStore.createIndex('expiry', 'expiry', { unique: false });
                }

                console.log('✅ تم إنشاء هيكل قاعدة البيانات');
            };
        });
    }

    /**
     * حفظ البيانات
     */
    async save(storeName, data) {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            
            data.local_timestamp = Date.now();
            data.synced = this.isOnline;

            const request = store.put(data);

            request.onsuccess = () => {
                console.log(`✅ تم حفظ البيانات في ${storeName}`);
                resolve(request.result);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * قراءة البيانات
     */
    async get(storeName, id) {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.get(id);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * قراءة كل البيانات
     */
    async getAll(storeName, indexName = null, query = null) {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            
            let request;
            if (indexName) {
                const index = store.index(indexName);
                request = query ? index.getAll(query) : index.getAll();
            } else {
                request = store.getAll();
            }

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * حذف البيانات
     */
    async delete(storeName, id) {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.delete(id);

            request.onsuccess = () => {
                resolve(request.result);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * إضافة للـ Sync Queue
     */
    async addToSyncQueue(storeName, action, data) {
        const queueItem = {
            storeName,
            action,
            data,
            timestamp: Date.now(),
            synced: false,
            retries: 0
        };

        await this.save('sync_queue', queueItem);
        this.notifyListeners('queue_updated', queueItem);
    }

    /**
     * معالجة Sync Queue عند العودة Online
     */
    async processSyncQueue() {
        const queue = await this.getAll('sync_queue', 'synced', false);
        
        if (queue.length === 0) {
            console.log('✅ لا توجد عمليات في الانتظار');
            return;
        }

        console.log(`🔄 معالجة ${queue.length} عملية من قائمة الانتظار...`);
        
        for (const item of queue) {
            try {
                await this.syncItem(item);
                
                // تحديث حالة العنصر إلى synced
                item.synced = true;
                await this.save('sync_queue', item);
                
                this.notifyListeners('item_synced', item);
            } catch (error) {
                console.error('❌ فشل مزامنة العنصر:', error);
                item.retries = (item.retries || 0) + 1;
                
                if (item.retries >= 3) {
                    this.notifyListeners('sync_failed', item);
                }
                
                await this.save('sync_queue', item);
            }
        }

        console.log('✅ تمت معالجة قائمة الانتظار');
    }

    /**
     * مزامنة عنصر واحد مع السيرفر
     */
    async syncItem(item) {
        const endpoint = this.getEndpointForStore(item.storeName);
        const method = item.action === 'delete' ? 'DELETE' : 'POST';
        
        try {
            const response = await fetch(endpoint, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: method !== 'DELETE' ? JSON.stringify(item.data) : undefined
            });

            if (!response.ok) {
                const errorText = await response.text();
                let errorData;
                
                try {
                    errorData = JSON.parse(errorText);
                } catch {
                    errorData = { message: errorText };
                }
                
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            // حفظ معلومات النجاح
            item.lastSyncAttempt = Date.now();
            item.lastSyncSuccess = true;
            
            return result;
        } catch (error) {
            // حفظ معلومات الخطأ
            item.lastSyncAttempt = Date.now();
            item.lastSyncSuccess = false;
            item.error = {
                message: error.message,
                timestamp: Date.now(),
                endpoint: endpoint,
                method: method
            };
            
            throw error;
        }
    }

    /**
     * الحصول على الـ endpoint المناسب
     */
    getEndpointForStore(storeName) {
        const endpoints = {
            'cars': '/api/cars',
            'transactions': '/api/transactions'
        };
        return endpoints[storeName] || '/api/sync';
    }

    /**
     * Cache API Response
     */
    async cacheResponse(key, data, ttl = 3600000) { // default 1 hour
        const cacheData = {
            key,
            data,
            expiry: Date.now() + ttl,
            timestamp: Date.now()
        };
        await this.save('cache', cacheData);
    }

    /**
     * Get Cached Response
     */
    async getCachedResponse(key) {
        const cached = await this.get('cache', key);
        
        if (!cached) return null;
        
        // تحقق من انتهاء الصلاحية
        if (Date.now() > cached.expiry) {
            await this.delete('cache', key);
            return null;
        }
        
        return cached.data;
    }

    /**
     * مسح الـ cache المنتهية الصلاحية
     */
    async clearExpiredCache() {
        const allCache = await this.getAll('cache');
        const now = Date.now();
        
        for (const item of allCache) {
            if (item.expiry < now) {
                await this.delete('cache', item.key);
            }
        }
    }

    /**
     * معالجة عند العودة Online
     */
    handleOnline() {
        console.log('🌐 الاتصال عاد!');
        this.isOnline = true;
        this.notifyListeners('online');
        this.processSyncQueue();
    }

    /**
     * معالجة عند فقدان الاتصال
     */
    handleOffline() {
        console.log('📴 لا يوجد اتصال - وضع Offline');
        this.isOnline = false;
        this.notifyListeners('offline');
    }

    /**
     * إضافة مستمع للأحداث
     */
    addListener(callback) {
        this.listeners.add(callback);
    }

    /**
     * إزالة مستمع
     */
    removeListener(callback) {
        this.listeners.delete(callback);
    }

    /**
     * إشعار جميع المستمعين
     */
    notifyListeners(event, data = null) {
        this.listeners.forEach(callback => {
            try {
                callback(event, data);
            } catch (error) {
                console.error('Error in listener:', error);
            }
        });
    }

    /**
     * حالة الاتصال
     */
    getConnectionStatus() {
        return {
            isOnline: this.isOnline,
            hasPendingSync: this.syncQueue.length > 0
        };
    }

    /**
     * ========================================
     * وظائف البحث المحلي للسيارات
     * ========================================
     */

    /**
     * البحث عن سيارة بالشاصي
     */
    async searchByChassis(chassis) {
        if (!this.db) await this.init();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['cars'], 'readonly');
            const store = transaction.objectStore('cars');
            const index = store.index('chassis');
            const request = index.getAll(chassis);

            request.onsuccess = () => {
                console.log(`🔍 البحث عن سيارة بالشاصي: ${chassis}`, request.result);
                resolve(request.result);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * البحث عن سيارة بالكاتي (رقم اللوحة)
     */
    async searchByLot(lot) {
        if (!this.db) await this.init();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['cars'], 'readonly');
            const store = transaction.objectStore('cars');
            const index = store.index('lot');
            const request = index.getAll(lot);

            request.onsuccess = () => {
                console.log(`🔍 البحث عن سيارة بالكاتي: ${lot}`, request.result);
                resolve(request.result);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * البحث المتقدم في السيارات (يدعم البحث الجزئي)
     */
    async searchCars(searchTerm) {
        if (!this.db) await this.init();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['cars'], 'readonly');
            const store = transaction.objectStore('cars');
            const request = store.getAll();

            request.onsuccess = () => {
                const allCars = request.result;
                const searchLower = searchTerm.toLowerCase();
                
                const results = allCars.filter(car => {
                    return (
                        (car.chassis && car.chassis.toLowerCase().includes(searchLower)) ||
                        (car.lot && car.lot.toLowerCase().includes(searchLower)) ||
                        (car.make && car.make.toLowerCase().includes(searchLower)) ||
                        (car.model && car.model.toLowerCase().includes(searchLower)) ||
                        (car.year && car.year.toString().includes(searchLower))
                    );
                });
                
                console.log(`🔍 نتائج البحث عن "${searchTerm}": ${results.length} سيارة`);
                resolve(results);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * الحصول على سيارات عميل معين
     */
    async getCarsByUser(userId) {
        if (!this.db) await this.init();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['cars'], 'readonly');
            const store = transaction.objectStore('cars');
            const index = store.index('user_id');
            const request = index.getAll(userId);

            request.onsuccess = () => {
                console.log(`🚗 سيارات العميل ${userId}: ${request.result.length} سيارة`);
                resolve(request.result);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * ========================================
     * وظائف إدارة الدفعات والسحوبات Offline
     * ========================================
     */

    /**
     * إضافة دفعة offline
     */
    async addPayment(paymentData) {
        if (!this.db) await this.init();
        
        const payment = {
            ...paymentData,
            type: 'payment',
            created_at: Date.now(),
            synced: false,
            local_id: `payment_${Date.now()}_${Math.random()}`,
            offline_created: true
        };

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['payments'], 'readwrite');
            const store = transaction.objectStore('payments');
            const request = store.add(payment);

            request.onsuccess = async () => {
                const paymentId = request.result;
                payment.id = paymentId;
                
                console.log('💰 تمت إضافة دفعة محلية:', payment);
                resolve(payment);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * إضافة سحب offline
     */
    async addWithdrawal(withdrawalData) {
        if (!this.db) await this.init();
        
        const withdrawal = {
            ...withdrawalData,
            type: 'withdrawal',
            created_at: Date.now(),
            synced: false,
            local_id: `withdrawal_${Date.now()}_${Math.random()}`,
            offline_created: true
        };

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['payments'], 'readwrite');
            const store = transaction.objectStore('payments');
            const request = store.add(withdrawal);

            request.onsuccess = async () => {
                const withdrawalId = request.result;
                withdrawal.id = withdrawalId;
                
                console.log('💸 تمت إضافة سحب محلي:', withdrawal);
                resolve(withdrawal);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * الحصول على دفعات سيارة معينة
     */
    async getCarPayments(carId) {
        if (!this.db) await this.init();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['payments'], 'readonly');
            const store = transaction.objectStore('payments');
            const index = store.index('car_id');
            const request = index.getAll(carId);

            request.onsuccess = () => {
                console.log(`💰 دفعات السيارة ${carId}: ${request.result.length}`);
                resolve(request.result);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * الحصول على دفعات عميل معين
     */
    async getUserPayments(userId) {
        if (!this.db) await this.init();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['payments'], 'readonly');
            const store = transaction.objectStore('payments');
            const index = store.index('user_id');
            const request = index.getAll(userId);

            request.onsuccess = () => {
                console.log(`💰 دفعات العميل ${userId}: ${request.result.length}`);
                resolve(request.result);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * حساب إجمالي الدفعات والسحوبات لسيارة
     */
    async calculateCarBalance(carId) {
        const payments = await this.getCarPayments(carId);
        
        let totalPayments = 0;
        let totalWithdrawals = 0;
        
        payments.forEach(payment => {
            if (payment.type === 'payment') {
                totalPayments += parseFloat(payment.amount || 0);
            } else if (payment.type === 'withdrawal') {
                totalWithdrawals += parseFloat(payment.amount || 0);
            }
        });
        
        return {
            totalPayments,
            totalWithdrawals,
            balance: totalPayments - totalWithdrawals,
            paymentsCount: payments.filter(p => p.type === 'payment').length,
            withdrawalsCount: payments.filter(p => p.type === 'withdrawal').length
        };
    }

    /**
     * ========================================
     * وظائف تحميل البيانات للعمل Offline
     * ========================================
     */

    /**
     * تحميل جميع السيارات من السيرفر للعمل offline
     */
    async loadCarsForOffline(forceRefresh = false) {
        if (!navigator.onLine && !forceRefresh) {
            console.log('📴 لا يوجد اتصال - استخدام البيانات المحلية');
            return await this.getAll('cars');
        }

        try {
            console.log('📥 جاري تحميل السيارات من السيرفر...');
            
            const response = await fetch('/api/getAllCarsForOffline');
            const data = await response.json();
            
            if (data.success && data.cars) {
                // حفظ في IndexedDB
                for (const car of data.cars) {
                    await this.save('cars', {
                        ...car,
                        synced: true,
                        last_sync: Date.now()
                    });
                }
                
                console.log(`✅ تم تحميل ${data.cars.length} سيارة للعمل offline`);
                return data.cars;
            }
        } catch (error) {
            console.error('❌ فشل تحميل السيارات:', error);
            // استخدام البيانات المحلية
            return await this.getAll('cars');
        }
    }

    /**
     * تحميل سيارات عميل معين
     */
    async loadUserCarsForOffline(userId) {
        if (!navigator.onLine) {
            return await this.getCarsByUser(userId);
        }

        try {
            const response = await fetch(`/api/getUserCarsForOffline/${userId}`);
            const data = await response.json();
            
            if (data.success && data.cars) {
                for (const car of data.cars) {
                    await this.save('cars', {
                        ...car,
                        synced: true,
                        last_sync: Date.now()
                    });
                }
                
                console.log(`✅ تم تحميل ${data.cars.length} سيارة للعميل ${userId}`);
                return data.cars;
            }
        } catch (error) {
            console.error('❌ فشل تحميل سيارات العميل:', error);
            return await this.getCarsByUser(userId);
        }
    }
}

// إنشاء نسخة واحدة (Singleton)
const db = new LocalDatabase();

export default db;

