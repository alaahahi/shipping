/**
 * Composable لإدارة العقود Offline
 * نظام احترافي للعمل بدون إنترنت مع مزامنة تلقائية
 */

import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const CONTRACTS_STORAGE_KEY = 'offline_contracts';
const PENDING_SYNC_KEY = 'pending_contracts_sync';
const LAST_SYNC_KEY = 'last_contracts_sync';

export function useOfflineContracts() {
    const toast = useToast();
    const isOnline = ref(navigator.onLine);
    const isSyncing = ref(false);
    const pendingCount = ref(0);
    const lastSyncTime = ref(null);
    const syncErrors = ref([]);

    /**
     * حفظ عقد محلياً
     */
    const saveContractOffline = async (contractData) => {
        try {
            // إضافة metadata للعقد
            const contract = {
                ...contractData,
                _offline: true,
                _id: generateOfflineId(),
                _createdAt: new Date().toISOString(),
                _status: 'pending',
                _retryCount: 0
            };

            // الحصول على العقود المحفوظة
            const contracts = getOfflineContracts();
            contracts.push(contract);

            // حفظ في localStorage
            localStorage.setItem(CONTRACTS_STORAGE_KEY, JSON.stringify(contracts));

            // تحديث العداد
            updatePendingCount();

            console.log('✅ تم حفظ العقد محلياً:', contract._id);
            
            return {
                success: true,
                offlineId: contract._id,
                message: 'تم حفظ العقد محلياً - سيتم الإرسال عند الاتصال بالإنترنت'
            };
        } catch (error) {
            console.error('❌ فشل حفظ العقد محلياً:', error);
            throw error;
        }
    };

    /**
     * الحصول على العقود المحفوظة محلياً
     */
    const getOfflineContracts = () => {
        try {
            const data = localStorage.getItem(CONTRACTS_STORAGE_KEY);
            return data ? JSON.parse(data) : [];
        } catch (error) {
            console.error('❌ فشل قراءة العقود المحلية:', error);
            return [];
        }
    };

    /**
     * حذف عقد من التخزين المحلي
     */
    const removeOfflineContract = (offlineId) => {
        try {
            const contracts = getOfflineContracts();
            const filtered = contracts.filter(c => c._id !== offlineId);
            localStorage.setItem(CONTRACTS_STORAGE_KEY, JSON.stringify(filtered));
            updatePendingCount();
            return true;
        } catch (error) {
            console.error('❌ فشل حذف العقد:', error);
            return false;
        }
    };

    /**
     * تحديث حالة عقد
     */
    const updateContractStatus = (offlineId, status, error = null) => {
        try {
            const contracts = getOfflineContracts();
            const contract = contracts.find(c => c._id === offlineId);
            
            if (contract) {
                contract._status = status;
                contract._lastAttempt = new Date().toISOString();
                
                if (error) {
                    contract._error = error;
                    contract._retryCount = (contract._retryCount || 0) + 1;
                }
                
                localStorage.setItem(CONTRACTS_STORAGE_KEY, JSON.stringify(contracts));
            }
        } catch (error) {
            console.error('❌ فشل تحديث حالة العقد:', error);
        }
    };

    /**
     * مزامنة عقد واحد
     */
    const syncSingleContract = async (contract) => {
        try {
            console.log('🔄 مزامنة العقد:', contract._id);
            
            // تحديث الحالة
            updateContractStatus(contract._id, 'syncing');

            // إرسال للخادم
            const response = await axios.post('/api/addCarContract', contract, {
                timeout: 30000, // 30 ثانية timeout
                headers: {
                    'X-Offline-Sync': 'true',
                    'X-Offline-ID': contract._id
                }
            });

            // نجح الإرسال
            console.log('✅ تمت مزامنة العقد:', contract._id);
            
            // حذف من التخزين المحلي
            removeOfflineContract(contract._id);

            return {
                success: true,
                serverId: response.data.id,
                offlineId: contract._id
            };
        } catch (error) {
            console.error('❌ فشلت مزامنة العقد:', contract._id, error.message);
            
            // تحديث الحالة
            updateContractStatus(contract._id, 'failed', error.message);

            return {
                success: false,
                offlineId: contract._id,
                error: error.message
            };
        }
    };

    /**
     * مزامنة جميع العقود
     */
    const syncAllContracts = async () => {
        if (!navigator.onLine) {
            toast.warning('لا يوجد اتصال بالإنترنت', {
                timeout: 3000,
                position: 'bottom-right',
                rtl: true
            });
            return;
        }

        const contracts = getOfflineContracts();
        
        if (contracts.length === 0) {
            console.log('✅ لا توجد عقود للمزامنة');
            return;
        }

        isSyncing.value = true;
        syncErrors.value = [];
        
        console.log(`🔄 بدء مزامنة ${contracts.length} عقد...`);
        
        toast.info(`جاري مزامنة ${contracts.length} عقد...`, {
            timeout: 5000,
            position: 'bottom-right',
            rtl: true
        });

        const results = {
            success: 0,
            failed: 0,
            total: contracts.length
        };

        // مزامنة العقود واحداً تلو الآخر (تجنب التحميل الزائد)
        for (const contract of contracts) {
            const result = await syncSingleContract(contract);
            
            if (result.success) {
                results.success++;
            } else {
                results.failed++;
                syncErrors.value.push({
                    contractId: contract._id,
                    error: result.error
                });
            }

            // توقف قصير بين الطلبات
            await new Promise(resolve => setTimeout(resolve, 500));
        }

        isSyncing.value = false;
        lastSyncTime.value = new Date().toISOString();
        localStorage.setItem(LAST_SYNC_KEY, lastSyncTime.value);
        updatePendingCount();

        // إظهار النتائج
        if (results.success > 0) {
            toast.success(`✅ تمت مزامنة ${results.success} عقد بنجاح`, {
                timeout: 5000,
                position: 'bottom-right',
                rtl: true
            });
        }

        if (results.failed > 0) {
            toast.error(`❌ فشلت مزامنة ${results.failed} عقد`, {
                timeout: 5000,
                position: 'bottom-right',
                rtl: true
            });
        }

        console.log('📊 نتائج المزامنة:', results);
        
        return results;
    };

    /**
     * حفظ عقد (online أو offline تلقائياً)
     */
    const saveContract = async (contractData) => {
        // التحقق من صحة البيانات
        const validation = validateContractData(contractData);
        if (!validation.valid) {
            throw new Error(validation.message);
        }

        // إذا كان online، حاول الإرسال مباشرة
        if (navigator.onLine) {
            try {
                const response = await axios.post('/api/addCarContract', contractData, {
                    timeout: 10000 // 10 ثوان
                });

                toast.success('✅ تم حفظ العقد بنجاح', {
                    timeout: 3000,
                    position: 'bottom-right',
                    rtl: true
                });

                return {
                    success: true,
                    online: true,
                    id: response.data.id,
                    data: response.data
                };
            } catch (error) {
                // إذا فشل، احفظ offline
                console.warn('⚠️ فشل الحفظ online، جاري الحفظ offline...');
                return await saveContractOfflineWithNotification(contractData);
            }
        } else {
            // إذا كان offline، احفظ محلياً مباشرة
            return await saveContractOfflineWithNotification(contractData);
        }
    };

    /**
     * حفظ offline مع إشعار
     */
    const saveContractOfflineWithNotification = async (contractData) => {
        const result = await saveContractOffline(contractData);

        toast.warning('⚠️ لا يوجد اتصال - تم الحفظ محلياً', {
            timeout: 5000,
            position: 'bottom-right',
            rtl: true
        });

        return {
            success: true,
            online: false,
            offlineId: result.offlineId,
            message: result.message
        };
    };

    /**
     * التحقق من صحة بيانات العقد
     */
    const validateContractData = (data) => {
        const requiredFields = [
            { field: 'name_seller', label: 'اسم البائع' },
            { field: 'name_buyer', label: 'اسم المشتري' },
            { field: 'vin', label: 'رقم الشاصي' },
            { field: 'car_name', label: 'اسم السيارة' },
            { field: 'no', label: 'رقم السيارة' }
        ];

        const missing = requiredFields.filter(f => !data[f.field]);

        if (missing.length > 0) {
            return {
                valid: false,
                message: `يرجى تعبئة: ${missing.map(m => m.label).join(', ')}`
            };
        }

        return { valid: true };
    };

    /**
     * توليد ID فريد للعقود المحلية
     */
    const generateOfflineId = () => {
        return `offline_${Date.now()}_${Math.random().toString(36).substring(2, 9)}`;
    };

    /**
     * تحديث عداد العقود المعلقة
     */
    const updatePendingCount = () => {
        const contracts = getOfflineContracts();
        pendingCount.value = contracts.filter(c => c._status === 'pending').length;
    };

    /**
     * مراقبة حالة الاتصال
     */
    const handleOnline = async () => {
        console.log('🌐 عاد الاتصال بالإنترنت');
        isOnline.value = true;

        const contracts = getOfflineContracts();
        
        if (contracts.length > 0) {
            toast.info('🔄 جاري مزامنة العقود...', {
                timeout: 3000,
                position: 'bottom-right',
                rtl: true
            });

            // انتظار قليلاً قبل المزامنة (للتأكد من استقرار الاتصال)
            setTimeout(() => {
                syncAllContracts();
            }, 2000);
        }
    };

    const handleOffline = () => {
        console.log('📡 فُقد الاتصال بالإنترنت');
        isOnline.value = false;

        toast.warning('⚠️ وضع Offline - يمكنك الاستمرار في العمل', {
            timeout: 5000,
            position: 'bottom-right',
            rtl: true
        });
    };

    /**
     * تهيئة المراقبة
     */
    onMounted(() => {
        // مراقبة حالة الاتصال
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        // تحديث العداد
        updatePendingCount();

        // الحصول على آخر وقت مزامنة
        const lastSync = localStorage.getItem(LAST_SYNC_KEY);
        if (lastSync) {
            lastSyncTime.value = lastSync;
        }

        // مزامنة تلقائية عند التحميل إذا كان online
        if (navigator.onLine && pendingCount.value > 0) {
            setTimeout(() => {
                syncAllContracts();
            }, 3000);
        }
    });

    /**
     * تنظيف عند الخروج
     */
    onUnmounted(() => {
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);
    });

    /**
     * حذف عقد محلي
     */
    const deleteOfflineContract = (offlineId) => {
        if (confirm('هل أنت متأكد من حذف هذا العقد؟ لن يتم إرساله للخادم.')) {
            const removed = removeOfflineContract(offlineId);
            
            if (removed) {
                toast.success('تم حذف العقد المحلي', {
                    timeout: 2000,
                    position: 'bottom-right',
                    rtl: true
                });
                updatePendingCount();
            }
        }
    };

    /**
     * إعادة محاولة مزامنة عقد فاشل
     */
    const retryFailedContract = async (offlineId) => {
        const contracts = getOfflineContracts();
        const contract = contracts.find(c => c._id === offlineId);

        if (contract) {
            if (!navigator.onLine) {
                toast.error('لا يوجد اتصال بالإنترنت', {
                    timeout: 2000,
                    position: 'bottom-right',
                    rtl: true
                });
                return;
            }

            const result = await syncSingleContract(contract);
            
            if (result.success) {
                toast.success('✅ تمت المزامنة بنجاح', {
                    timeout: 2000,
                    position: 'bottom-right',
                    rtl: true
                });
                updatePendingCount();
            } else {
                toast.error('❌ فشلت المزامنة', {
                    timeout: 2000,
                    position: 'bottom-right',
                    rtl: true
                });
            }
        }
    };

    /**
     * مسح جميع العقود المزامنة
     */
    const clearSyncedContracts = () => {
        const contracts = getOfflineContracts();
        const pending = contracts.filter(c => c._status === 'pending' || c._status === 'failed');
        
        localStorage.setItem(CONTRACTS_STORAGE_KEY, JSON.stringify(pending));
        updatePendingCount();
        
        toast.info('تم مسح العقود المزامنة', {
            timeout: 2000,
            position: 'bottom-right',
            rtl: true
        });
    };

    /**
     * تصدير العقود كـ JSON
     */
    const exportContractsAsJSON = () => {
        const contracts = getOfflineContracts();
        const dataStr = JSON.stringify(contracts, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        
        const url = URL.createObjectURL(dataBlob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `contracts_backup_${Date.now()}.json`;
        link.click();
        
        URL.revokeObjectURL(url);
        
        toast.success('تم تصدير العقود', {
            timeout: 2000,
            position: 'bottom-right',
            rtl: true
        });
    };

    /**
     * استيراد عقود من JSON
     */
    const importContractsFromJSON = (file) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                try {
                    const importedContracts = JSON.parse(e.target.result);
                    const existingContracts = getOfflineContracts();
                    
                    // دمج العقود
                    const merged = [...existingContracts, ...importedContracts];
                    localStorage.setItem(CONTRACTS_STORAGE_KEY, JSON.stringify(merged));
                    
                    updatePendingCount();
                    
                    toast.success(`تم استيراد ${importedContracts.length} عقد`, {
                        timeout: 3000,
                        position: 'bottom-right',
                        rtl: true
                    });
                    
                    resolve(importedContracts.length);
                } catch (error) {
                    toast.error('فشل استيراد العقود - ملف غير صالح', {
                        timeout: 3000,
                        position: 'bottom-right',
                        rtl: true
                    });
                    reject(error);
                }
            };
            
            reader.readAsText(file);
        });
    };

    /**
     * إحصائيات
     */
    const stats = computed(() => {
        const contracts = getOfflineContracts();
        
        return {
            total: contracts.length,
            pending: contracts.filter(c => c._status === 'pending').length,
            syncing: contracts.filter(c => c._status === 'syncing').length,
            failed: contracts.filter(c => c._status === 'failed').length,
            synced: contracts.filter(c => c._status === 'synced').length
        };
    });

    /**
     * وقت آخر مزامنة (formatted)
     */
    const lastSyncFormatted = computed(() => {
        if (!lastSyncTime.value) return 'لم تتم المزامنة بعد';
        
        const date = new Date(lastSyncTime.value);
        return date.toLocaleString('ar-EG', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    });

    // Return API
    return {
        // State
        isOnline,
        isSyncing,
        pendingCount,
        lastSyncTime,
        lastSyncFormatted,
        syncErrors,
        stats,
        
        // Methods
        saveContract,
        saveContractOffline,
        getOfflineContracts,
        deleteOfflineContract,
        retryFailedContract,
        syncAllContracts,
        clearSyncedContracts,
        exportContractsAsJSON,
        importContractsFromJSON
    };
}

