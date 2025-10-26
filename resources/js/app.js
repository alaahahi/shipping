import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { Inertia } from '@inertiajs/inertia';
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

// استيراد نظام القفل للطلبات الحساسة
import { lockSensitiveRequest, createRequestKey, isSensitiveRequest } from './utils/requestLock';

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

// 🔴 نظام حماية الطلبات الحساسة (خط أحمر)
// يمنع تكرار أي طلب حساس تماماً

// Request Interceptor - قبل إرسال الطلب
axios.interceptors.request.use(
    (config) => {
        const url = config.url || '';
        const method = config.method || 'get';
        
        // إضافة timestamp فريد لمنع الكاش
        if (isSensitiveRequest(url)) {
            config.params = config.params || {};
            config.params._t = Date.now();
            
            // إضافة علامة للطلب الحساس
            config.headers['X-Sensitive-Request'] = 'true';
            
            console.log('🔒 طلب حساس:', method.toUpperCase(), url);
        }
        
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response Interceptor - بعد استلام الرد
axios.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        const url = error.config?.url || '';
        
        if (isSensitiveRequest(url)) {
            console.error('❌ فشل طلب حساس:', url, error.message);
        }
        
        return Promise.reject(error);
    }
);

// الاحتفاظ بالمرجع الأصلي
const originalPost = axios.post;
const originalPut = axios.put;
const originalPatch = axios.patch;
const originalDelete = axios.delete;

// تغليف الطلبات الحساسة بنظام القفل
axios.post = function(url, data, config) {
    if (isSensitiveRequest(url)) {
        const key = createRequestKey('POST', url, data);
        return lockSensitiveRequest(key, () => originalPost.call(this, url, data, config));
    }
    return originalPost.call(this, url, data, config);
};

axios.put = function(url, data, config) {
    if (isSensitiveRequest(url)) {
        const key = createRequestKey('PUT', url, data);
        return lockSensitiveRequest(key, () => originalPut.call(this, url, data, config));
    }
    return originalPut.call(this, url, data, config);
};

axios.patch = function(url, data, config) {
    if (isSensitiveRequest(url)) {
        const key = createRequestKey('PATCH', url, data);
        return lockSensitiveRequest(key, () => originalPatch.call(this, url, data, config));
    }
    return originalPatch.call(this, url, data, config);
};

axios.delete = function(url, config) {
    if (isSensitiveRequest(url)) {
        const key = createRequestKey('DELETE', url);
        return lockSensitiveRequest(key, () => originalDelete.call(this, url, config));
    }
    return originalDelete.call(this, url, config);
};

// تسجيل Service Worker (في التطوير والإنتاج)
// يعمل الآن في كل الأوضاع لاختبار offline mode
registerServiceWorker().then(() => {
    console.log('✅ Service Worker جاهز');
}).catch(err => {
    console.error('❌ فشل تسجيل Service Worker:', err);
});

// إعدادات Inertia للتنقل السلس (SPA mode)
Inertia.on('start', (event) => {
    console.log('🚀 Inertia navigation started');
});

Inertia.on('finish', (event) => {
    console.log('✅ Inertia navigation finished');
    // التمرير للأعلى عند الانتهاء من التنقل
    window.scrollTo(0, 0);
});

Inertia.on('navigate', (event) => {
    console.log('📄 Navigated to:', event.detail.page.url);
});

Inertia.on('success', (event) => {
    console.log('✅ Inertia success - page updated');
});

Inertia.on('error', (errors) => {
    console.error('❌ Inertia navigation error:', errors);
});

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

InertiaProgress.init({ 
    color: '#f00',
    showSpinner: true,
    delay: 250,
    includeCSS: true
});

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
