/**
 * إعداد Axios كـ Plugin عالمي
 * يستخدم API Wrapper الجديد تلقائياً
 */

import axios from 'axios';

// إنشاء instance من axios
const axiosInstance = axios.create({
    baseURL: '/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    }
});

// إضافة CSRF token تلقائياً
axiosInstance.interceptors.request.use((config) => {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token.content;
    }
    return config;
});

// تحسين لاستخدام API Wrapper إذا كان متاحاً
const smartAxios = {
    async get(url, config = {}) {
        // استخدام API Wrapper إذا كان متاحاً
        if (window.$api) {
            try {
                const response = await window.$api.get(url, {
                    ...config,
                    cache: true, // تفعيل Cache تلقائياً
                });
                // إرجاع بنفس تنسيق axios
                return {
                    data: response.data,
                    status: response.status || 200,
                    statusText: response.fromCache ? 'OK (Cached)' : 'OK',
                    headers: {},
                    config: config,
                    fromCache: response.fromCache
                };
            } catch (error) {
                // إذا فشل، استخدم axios العادي
                return axiosInstance.get(url, config);
            }
        }
        // استخدام axios العادي
        return axiosInstance.get(url, config);
    },

    async post(url, data, config = {}) {
        if (window.$api) {
            try {
                const response = await window.$api.post(url, data, config);
                return {
                    data: response.data,
                    status: response.status || 200,
                    statusText: response.queued ? 'Accepted (Queued)' : 'OK',
                    headers: {},
                    config: config,
                    queued: response.queued
                };
            } catch (error) {
                return axiosInstance.post(url, data, config);
            }
        }
        return axiosInstance.post(url, data, config);
    },

    async put(url, data, config = {}) {
        if (window.$api) {
            try {
                const response = await window.$api.put(url, data, config);
                return {
                    data: response.data,
                    status: response.status || 200,
                    statusText: 'OK',
                    headers: {},
                    config: config
                };
            } catch (error) {
                return axiosInstance.put(url, data, config);
            }
        }
        return axiosInstance.put(url, data, config);
    },

    async delete(url, config = {}) {
        if (window.$api) {
            try {
                const response = await window.$api.delete(url, config);
                return {
                    data: response.data,
                    status: response.status || 200,
                    statusText: 'OK',
                    headers: {},
                    config: config
                };
            } catch (error) {
                return axiosInstance.delete(url, config);
            }
        }
        return axiosInstance.delete(url, config);
    },

    // طرق إضافية
    request: (config) => axiosInstance.request(config),
    head: (url, config) => axiosInstance.head(url, config),
    options: (url, config) => axiosInstance.options(url, config),
    patch: (url, data, config) => axiosInstance.patch(url, data, config),

    // interceptors للتوافق
    interceptors: axiosInstance.interceptors,

    // defaults
    defaults: axiosInstance.defaults
};

export default smartAxios;

