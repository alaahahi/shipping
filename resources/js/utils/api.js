/**
 * API Wrapper مع دعم Offline/Online
 * يتعامل تلقائياً مع فقدان الاتصال ويحفظ البيانات محلياً
 */

import db from './db';
import axios from 'axios';

class ApiService {
    constructor() {
        this.baseURL = '';
        this.isOnline = navigator.onLine;
        this.requestQueue = [];
        
        // تكوين Axios
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['Accept'] = 'application/json';
        
        // Interceptor للطلبات
        axios.interceptors.request.use(
            config => {
                // إضافة CSRF token
                const token = document.head.querySelector('meta[name="csrf-token"]');
                if (token) {
                    config.headers['X-CSRF-TOKEN'] = token.content;
                }
                return config;
            },
            error => Promise.reject(error)
        );

        // Interceptor للردود
        axios.interceptors.response.use(
            response => response,
            async error => {
                // إذا كان خطأ في الشبكة، حاول من الـ cache
                if (!error.response && error.message === 'Network Error') {
                    console.warn('⚠️ خطأ في الشبكة - محاولة القراءة من الـ cache');
                    
                    const cacheKey = this.getCacheKey(error.config);
                    const cachedData = await db.getCachedResponse(cacheKey);
                    
                    if (cachedData) {
                        console.log('✅ تم إيجاد البيانات في الـ cache');
                        return { data: cachedData, fromCache: true };
                    }
                }
                
                return Promise.reject(error);
            }
        );
    }

    /**
     * GET Request مع دعم Cache
     */
    async get(url, options = {}) {
        const cacheKey = `get_${url}`;
        const cacheTTL = options.cacheTTL || 600000; // 10 minutes default

        try {
            // إذا كنا online، جرّب من السيرفر
            if (this.isOnline) {
                const response = await axios.get(url, options);
                
                // احفظ في الـ cache
                if (options.cache !== false) {
                    await db.cacheResponse(cacheKey, response.data, cacheTTL);
                }
                
                return { 
                    data: response.data, 
                    fromCache: false,
                    status: response.status 
                };
            }
        } catch (error) {
            console.warn('⚠️ فشل الطلب من السيرفر:', error.message);
        }

        // محاولة القراءة من الـ cache
        const cachedData = await db.getCachedResponse(cacheKey);
        
        if (cachedData) {
            console.log('📦 تم إرجاع البيانات من الـ cache');
            return { 
                data: cachedData, 
                fromCache: true,
                status: 200
            };
        }

        throw new Error('لا توجد بيانات متاحة - Offline ولا يوجد cache');
    }

    /**
     * POST Request مع دعم Offline Queue
     */
    async post(url, data, options = {}) {
        if (!this.isOnline) {
            throw new Error('لا يوجد اتصال بالإنترنت');
        }

        const response = await axios.post(url, data, options);

        if (options.saveLocal !== false) {
            await this.saveToLocalStore(url, data, response.data);
        }

        return {
            data: response.data,
            queued: false,
            status: response.status
        };
    }

    /**
     * PUT/PATCH Request
     */
    async put(url, data, options = {}) {
        if (!this.isOnline) {
            throw new Error('لا يوجد اتصال بالإنترنت');
        }

        const response = await axios.put(url, data, options);

        if (options.saveLocal !== false) {
            await this.saveToLocalStore(url, data, response.data);
        }

        return {
            data: response.data,
            queued: false,
            status: response.status
        };
    }

    /**
     * DELETE Request
     */
    async delete(url, options = {}) {
        if (!this.isOnline) {
            throw new Error('لا يوجد اتصال بالإنترنت');
        }

        const id = this.getIdFromUrl(url);
        const response = await axios.delete(url, options);

        const storeName = this.getStoreNameFromUrl(url);
        if (storeName && id) {
            await db.delete(storeName, id);
        }

        return {
            data: response.data,
            queued: false,
            status: response.status
        };
    }

    /**
     * حفظ في المخزن المحلي المناسب
     */
    async saveToLocalStore(url, requestData, responseData) {
        const storeName = this.getStoreNameFromUrl(url);
        
        if (storeName) {
            const dataToSave = { ...requestData, ...responseData };
            await db.save(storeName, dataToSave);
        }
    }

    /**
     * استخراج اسم المخزن من URL
     */
    getStoreNameFromUrl(url) {
        // تحويل URL إلى اسم store
        // مثال: /api/cars => cars
        // مثال: /api/contracts => contracts
        
        const match = url.match(/\/api\/(\w+)/);
        return match ? match[1] : null;
    }

    /**
     * استخراج ID من URL
     */
    getIdFromUrl(url) {
        const match = url.match(/\/(\d+)$/);
        return match ? parseInt(match[1]) : null;
    }

    /**
     * إنشاء مفتاح cache
     */
    getCacheKey(config) {
        return `${config.method}_${config.url}_${JSON.stringify(config.params || {})}`;
    }

    /**
     * جلب البيانات من المخزن المحلي
     */
    async getLocal(storeName, id = null) {
        if (id) {
            return await db.get(storeName, id);
        }
        return await db.getAll(storeName);
    }

    /**
     * التحقق من حالة المزامنة
     */
    async getSyncStatus() {
        const queue = await db.getAll('sync_queue', 'synced', false);
        return {
            pendingCount: queue.length,
            items: queue,
            isOnline: this.isOnline
        };
    }

    /**
     * مزامنة يدوية
     */
    async syncNow() {
        if (!this.isOnline) {
            throw new Error('لا يمكن المزامنة - لا يوجد اتصال بالإنترنت');
        }
        
        await db.processSyncQueue();
        
        return {
            success: true,
            message: 'تمت المزامنة بنجاح'
        };
    }

    /**
     * مسح الـ cache
     */
    async clearCache() {
        await db.clearExpiredCache();
    }
}

// إنشاء نسخة واحدة
const api = new ApiService();

// تحديث حالة الاتصال فقط — بدون مزامنة تلقائية
window.addEventListener('online', () => {
    api.isOnline = true;
    console.log('🌐 الاتصال عاد');
});

window.addEventListener('offline', () => {
    api.isOnline = false;
    console.log('📴 فقدان الاتصال - وضع Offline مفعل');
});

export default api;

