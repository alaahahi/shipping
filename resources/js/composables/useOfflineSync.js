/**
 * @deprecated لم يعد يُستخدم التخزين على SQLite عند فشل الاتصال.
 * يُبقى للتوافق فقط — الحفظ عبر MySQL مباشرة.
 */

import { ref, onMounted, onUnmounted } from 'vue';
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

    const saveContract = async (contractData) => {
        const payload = { ...contractData };
        if (!payload.uuid && (!payload.id || payload.id === 0)) {
            payload.uuid = generateUuid();
        }

        try {
            const response = await axios.post('/api/addCarContract', payload, {
                timeout: 30000,
                headers: { Accept: 'application/json' },
            });

            return {
                success: true,
                online: true,
                data: response.data,
                id: response.data?.id || response.data?.data?.id,
            };
        } catch (error) {
            if (error.response?.status === 422) {
                return {
                    success: false,
                    validation: true,
                    errors: error.response.data.errors || error.response.data.message || {},
                };
            }

            return {
                success: false,
                validation: false,
                errors: error.response?.data?.errors || {},
                message: error.response?.data?.message || error.message || 'حدث خطأ أثناء الحفظ',
            };
        }
    };

    const handleOnline = () => { isOnline.value = true; };
    const handleOffline = () => { isOnline.value = false; };

    onMounted(() => {
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);
    });

    onUnmounted(() => {
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);
    });

    return {
        isOnline,
        isSyncing,
        saveContract,
    };
}
