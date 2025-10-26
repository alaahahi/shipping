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

// استيراد دالة التحقق من الطلبات الحساسة
import { isSensitiveRequest } from './utils/requestLock';

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

// تهيئة قاعدة البيانات المحلية (في الخلفية)
db.init().then(() => {
    console.log('✅ قاعدة البيانات المحلية جاهزة');
}).catch(err => {
    console.warn('⚠️ قاعدة البيانات المحلية غير متاحة:', err);
    // التطبيق يعمل حتى لو فشلت قاعدة البيانات المحلية
});

// 🔴 نظام حماية الطلبات الحساسة (خط أحمر) - مبسط
// Request Interceptor - فقط للتتبع والحماية
axios.interceptors.request.use(
    (config) => {
        const url = config.url || '';
        
        // تأكد من وجود headers
        config.headers = config.headers || {};
        
        // إضافة timestamp فريد لمنع الكاش للطلبات الحساسة
        if (isSensitiveRequest(url)) {
            config.params = config.params || {};
            config.params._t = Date.now();
            config.headers['X-Sensitive-Request'] = 'true';
        }
        
        return config;
    },
    (error) => {
        console.error('❌ خطأ في الطلب:', error);
        return Promise.reject(error);
    }
);

// Response Interceptor - معالجة الأخطاء
axios.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // معالجة Network Errors
        if (!error.response) {
            console.error('❌ Network Error - لا يوجد رد من الخادم');
        } else {
            console.error('❌ خطأ في الرد:', error.response.status, error.response.data);
        }
        
        return Promise.reject(error);
    }
);

// تسجيل Service Worker (فقط في الإنتاج)
// في localhost/127.0.0.1 يُعطّل لتجنب مشاكل الكاش أثناء التطوير
const isLocalDevelopment = window.location.hostname === 'localhost' || 
                          window.location.hostname === '127.0.0.1' ||
                          window.location.hostname.includes('local');

if (import.meta.env.PROD && !isLocalDevelopment) {
    registerServiceWorker().then(() => {
        console.log('✅ Service Worker جاهز (الإنتاج)');
    }).catch(err => {
        console.error('❌ فشل تسجيل Service Worker:', err);
    });
} else {
    console.log('🔧 وضع التطوير على', window.location.hostname, '- Service Worker معطل');
}

// إعدادات Inertia للتنقل السلس (SPA mode)
Inertia.on('start', (event) => {
    console.log('🚀 Inertia navigation started');
});

Inertia.on('progress', (event) => {
    // console.log('Progress:', event.detail.progress);
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

Inertia.on('exception', (event) => {
    console.error('💥 Inertia exception:', event.detail);
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
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
