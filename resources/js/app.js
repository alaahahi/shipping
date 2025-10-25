import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { createI18n } from 'vue-i18n';
import { createPinia } from 'pinia';
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import axios from 'axios';

// استيراد نظام Offline/Online
import { registerServiceWorker } from './utils/registerServiceWorker';
import db from './utils/db';
import api from './utils/api';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';
import en from './lang/en.json';
import ar from './lang/ar.json';
import kr from './lang/kr.json';

const i18n = createI18n({
  legacy: false,
  locale: localStorage.getItem('lang')|| 'ar', // Set the default locale
  messages: {
    en, // English translations
    ar, // Arabic translations
    kr
  },
});

// إنشاء Pinia Store
const pinia = createPinia();

// تهيئة قاعدة البيانات المحلية
db.init().then(() => {
    console.log('✅ قاعدة البيانات المحلية جاهزة');
}).catch(err => {
    console.error('❌ فشل تهيئة قاعدة البيانات:', err);
});

// تحسين axios للعمل مع API Wrapper تلقائياً
// Interceptor للطلبات - يستخدم API Wrapper إذا كان متاحاً
const originalGet = axios.get;
const originalPost = axios.post;
const originalPut = axios.put;
const originalDelete = axios.delete;

axios.get = async function(url, config = {}) {
    if (window.$api && !config.skipWrapper) {
        try {
            const response = await window.$api.get(url, { ...config, cache: true });
            return { data: response.data, status: response.status || 200, fromCache: response.fromCache };
        } catch (error) {
            return originalGet.call(this, url, config);
        }
    }
    return originalGet.call(this, url, config);
};

axios.post = async function(url, data, config = {}) {
    if (window.$api && !config.skipWrapper) {
        try {
            const response = await window.$api.post(url, data, config);
            return { data: response.data, status: response.status || 200, queued: response.queued };
        } catch (error) {
            return originalPost.call(this, url, data, config);
        }
    }
    return originalPost.call(this, url, data, config);
};

axios.put = async function(url, data, config = {}) {
    if (window.$api && !config.skipWrapper) {
        try {
            const response = await window.$api.put(url, data, config);
            return { data: response.data, status: response.status || 200 };
        } catch (error) {
            return originalPut.call(this, url, data, config);
        }
    }
    return originalPut.call(this, url, data, config);
};

axios.delete = async function(url, config = {}) {
    if (window.$api && !config.skipWrapper) {
        try {
            const response = await window.$api.delete(url, config);
            return { data: response.data, status: response.status || 200 };
        } catch (error) {
            return originalDelete.call(this, url, config);
        }
    }
    return originalDelete.call(this, url, config);
};

// تسجيل Service Worker
if (import.meta.env.PROD) {
    registerServiceWorker().then(() => {
        console.log('✅ Service Worker جاهز');
    }).catch(err => {
        console.error('❌ فشل تسجيل Service Worker:', err);
    });
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, app, props, plugin }) {
        const vueApp = createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(pinia)
            .use(i18n)
            .use(Toast)
            .use(ZiggyVue, Ziggy);
        
        // إضافة الأدوات للـ window للوصول الشامل
        window.$db = db;
        window.$api = api;
        window.$toast = vueApp.config.globalProperties.$toast;
        
        return vueApp.mount(el);
    },
});

InertiaProgress.init({ color: '#f00' });

// مراقبة حالة الاتصال وإظهار إشعارات
let wasOffline = false;

window.addEventListener('online', () => {
    if (wasOffline && window.$toast) {
        window.$toast.success('عاد الاتصال! جاري المزامنة...', {
            timeout: 3000,
            position: 'top-center'
        });
        
        // مزامنة تلقائية
        db.processSyncQueue().catch(err => {
            console.error('فشلت المزامنة:', err);
        });
    }
    wasOffline = false;
});

window.addEventListener('offline', () => {
    wasOffline = true;
    if (window.$toast) {
        window.$toast.warning('لا يوجد اتصال - وضع Offline مفعّل', {
            timeout: 5000,
            position: 'top-center'
        });
    }
});
