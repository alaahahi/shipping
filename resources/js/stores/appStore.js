/**
 * Pinia Store لإدارة حالة التطبيق
 * يدير الاتصال، المزامنة، والبيانات المحلية
 */

import { defineStore } from 'pinia';
import db from '../utils/db';
import api from '../utils/api';

export const useAppStore = defineStore('app', {
    state: () => ({
        // حالة الاتصال
        isOnline: navigator.onLine,
        
        // حالة المزامنة
        syncStatus: {
            syncing: false,
            pendingCount: 0,
            lastSync: null,
            errors: []
        },
        
        // البيانات المحلية
        localData: {
            cars: [],
            contracts: [],
            transactions: []
        },
        
        // الإعدادات
        settings: {
            autoSync: true,
            cacheTimeout: 600000, // 10 minutes
            offlineMode: false
        },
        
        // حالة التطبيق
        loading: false,
        notifications: []
    }),

    getters: {
        /**
         * هل يوجد بيانات في انتظار المزامنة؟
         */
        hasPendingSync: (state) => state.syncStatus.pendingCount > 0,
        
        /**
         * هل التطبيق جاهز؟
         */
        isReady: (state) => !state.loading,
        
        /**
         * حالة الاتصال مع تفاصيل
         */
        connectionStatus: (state) => ({
            online: state.isOnline,
            offline: !state.isOnline,
            syncing: state.syncStatus.syncing,
            hasPendingSync: state.syncStatus.pendingCount > 0
        }),

        /**
         * آخر وقت مزامنة بصيغة قابلة للقراءة
         */
        lastSyncFormatted: (state) => {
            if (!state.syncStatus.lastSync) return 'لم تتم المزامنة بعد';
            
            const date = new Date(state.syncStatus.lastSync);
            const now = new Date();
            const diff = now - date;
            
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            
            if (minutes < 1) return 'منذ لحظات';
            if (minutes < 60) return `منذ ${minutes} دقيقة`;
            if (hours < 24) return `منذ ${hours} ساعة`;
            return `منذ ${days} يوم`;
        }
    },

    actions: {
        /**
         * تهيئة التطبيق
         */
        async initialize() {
            this.loading = true;
            
            try {
                // تهيئة قاعدة البيانات المحلية
                await db.init();
                console.log('✅ تم تهيئة قاعدة البيانات المحلية');
                
                // تحميل الإعدادات المحفوظة
                await this.loadSettings();
                
                // تحميل البيانات المحلية
                await this.loadLocalData();
                
                // التحقق من حالة المزامنة
                await this.updateSyncStatus();
                
                // إضافة مستمعين للأحداث
                this.setupEventListeners();
                
                // لا مزامنة تلقائية عند التحميل
                
                console.log('✅ التطبيق جاهز!');
            } catch (error) {
                console.error('❌ فشل تهيئة التطبيق:', error);
                this.addNotification({
                    type: 'error',
                    message: 'حدث خطأ في تهيئة التطبيق'
                });
            } finally {
                this.loading = false;
            }
        },

        /**
         * إعداد مستمعي الأحداث
         */
        setupEventListeners() {
            // مراقبة حالة الاتصال
            window.addEventListener('online', () => this.handleOnline());
            window.addEventListener('offline', () => this.handleOffline());
            
            // مراقبة أحداث قاعدة البيانات
            db.addListener((event, data) => {
                this.handleDatabaseEvent(event, data);
            });
            
            // مراقبة رسائل Service Worker
            if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                navigator.serviceWorker.addEventListener('message', (event) => {
                    this.handleServiceWorkerMessage(event.data);
                });
            }
        },

        /**
         * معالجة العودة Online
         */
        handleOnline() {
            console.log('🌐 الاتصال عاد!');
            this.isOnline = true;
            
            this.addNotification({
                type: 'success',
                message: 'عاد الاتصال!'
            });
        },

        /**
         * معالجة فقدان الاتصال
         */
        handleOffline() {
            console.log('📴 فقدان الاتصال');
            this.isOnline = false;
            
            this.addNotification({
                type: 'warning',
                message: 'لا يوجد اتصال - وضع Offline مفعّل'
            });
        },

        /**
         * معالجة أحداث قاعدة البيانات
         */
        handleDatabaseEvent(event, data) {
            switch (event) {
                case 'queue_updated':
                    this.updateSyncStatus();
                    break;
                    
                case 'item_synced':
                    console.log('✅ تمت مزامنة العنصر:', data);
                    this.updateSyncStatus();
                    break;
                    
                case 'sync_failed':
                    console.error('❌ فشلت مزامنة العنصر:', data);
                    this.syncStatus.errors.push(data);
                    break;
            }
        },

        /**
         * معالجة رسائل Service Worker
         */
        handleServiceWorkerMessage(data) {
            switch (data.type) {
                case 'SYNC_COMPLETE':
                    this.addNotification({
                        type: 'success',
                        message: 'تمت المزامنة بنجاح'
                    });
                    this.updateSyncStatus();
                    break;
            }
        },

        /**
         * مزامنة البيانات
         */
        async syncData(force = false) {
            if (this.syncStatus.syncing) {
                console.log('⏳ المزامنة قيد التنفيذ...');
                return;
            }
            
            if (!this.isOnline && !force) {
                console.warn('⚠️ لا يمكن المزامنة - غير متصل');
                return;
            }
            
            this.syncStatus.syncing = true;
            
            try {
                console.log('🔄 بدء المزامنة...');
                
                // مزامنة قائمة الانتظار
                await db.processSyncQueue();
                
                // تحديث البيانات المحلية من السيرفر
                if (this.isOnline) {
                    await this.fetchLatestData();
                }
                
                // تحديث حالة المزامنة
                this.syncStatus.lastSync = Date.now();
                await this.updateSyncStatus();
                
                console.log('✅ اكتملت المزامنة');
                
                this.addNotification({
                    type: 'success',
                    message: 'تمت المزامنة بنجاح',
                    timeout: 3000
                });
            } catch (error) {
                console.error('❌ فشلت المزامنة:', error);
                
                this.addNotification({
                    type: 'error',
                    message: 'فشلت المزامنة: ' + error.message
                });
            } finally {
                this.syncStatus.syncing = false;
            }
        },

        /**
         * تحديث حالة المزامنة
         */
        async updateSyncStatus() {
            try {
                const status = await api.getSyncStatus();
                this.syncStatus.pendingCount = status.pendingCount;
            } catch (error) {
                console.error('فشل تحديث حالة المزامنة:', error);
            }
        },

        /**
         * جلب آخر البيانات من السيرفر
         */
        async fetchLatestData() {
            // يمكن تخصيص هذا حسب احتياجات التطبيق
            console.log('📥 جلب آخر البيانات...');
            
            // مثال: جلب السيارات الحديثة
            // const cars = await api.get('/api/cars', { cache: true });
            // this.localData.cars = cars.data;
        },

        /**
         * تحميل البيانات المحلية
         */
        async loadLocalData() {
            try {
                this.localData.cars = await db.getAll('cars') || [];
                this.localData.contracts = []; // لم نعد نخزن العقود في الفرونت
                this.localData.transactions = await db.getAll('transactions') || [];
                
                console.log('✅ تم تحميل البيانات المحلية');
            } catch (error) {
                console.error('❌ فشل تحميل البيانات المحلية:', error);
            }
        },

        /**
         * حفظ البيانات محلياً
         */
        async saveLocal(storeName, data) {
            try {
                await db.save(storeName, data);
                await this.loadLocalData();
                return true;
            } catch (error) {
                console.error('فشل الحفظ المحلي:', error);
                return false;
            }
        },

        /**
         * تحميل الإعدادات
         */
        async loadSettings() {
            try {
                const savedSettings = localStorage.getItem('app_settings');
                if (savedSettings) {
                    this.settings = { ...this.settings, ...JSON.parse(savedSettings) };
                }
            } catch (error) {
                console.error('فشل تحميل الإعدادات:', error);
            }
        },

        /**
         * حفظ الإعدادات
         */
        async saveSettings() {
            try {
                localStorage.setItem('app_settings', JSON.stringify(this.settings));
                console.log('✅ تم حفظ الإعدادات');
            } catch (error) {
                console.error('فشل حفظ الإعدادات:', error);
            }
        },

        /**
         * تبديل المزامنة التلقائية
         */
        toggleAutoSync() {
            this.settings.autoSync = !this.settings.autoSync;
            this.saveSettings();
        },

        /**
         * إضافة إشعار
         */
        addNotification(notification) {
            const id = Date.now();
            this.notifications.push({ id, ...notification });
            
            // إزالة تلقائية بعد المدة المحددة
            if (notification.timeout !== 0) {
                setTimeout(() => {
                    this.removeNotification(id);
                }, notification.timeout || 5000);
            }
        },

        /**
         * إزالة إشعار
         */
        removeNotification(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }
        },

        /**
         * مسح كل الإشعارات
         */
        clearNotifications() {
            this.notifications = [];
        },

        /**
         * إعادة تعيين التطبيق
         */
        async reset() {
            if (confirm('هل أنت متأكد من إعادة تعيين التطبيق؟ سيتم حذف كل البيانات المحلية.')) {
                try {
                    // مسح IndexedDB
                    await indexedDB.deleteDatabase('ShippingSystemDB');
                    
                    // مسح localStorage
                    localStorage.clear();
                    
                    // مسح sessionStorage
                    sessionStorage.clear();
                    
                    // إعادة تحميل الصفحة
                    window.location.reload();
                } catch (error) {
                    console.error('فشل إعادة التعيين:', error);
                }
            }
        }
    }
});

