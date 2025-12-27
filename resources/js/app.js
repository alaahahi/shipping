import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { createI18n } from 'vue-i18n';
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';
import en from './lang/en.json';
import ar from './lang/ar.json';
import kr from './lang/kr.json';

const i18n = createI18n({
  legacy: false,
  locale: localStorage.getItem('lang')|| 'ar',
  messages: { en, ar, kr },
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .use(Toast)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
});

InertiaProgress.init({ color: '#f00' });

// 🚀 PWA: تسجيل Service Worker - نسخة محسّنة ومستقرة
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            // فحص وجود ملف sw.js قبل التسجيل
            const swExists = await fetch('/sw.js', { method: 'HEAD' })
                .then(response => response.ok)
                .catch(() => false);
            
            if (!swExists) {
                console.warn('⚠️ Service Worker file not found, skipping registration');
                // إلغاء تسجيل أي SW قديمة
                const registrations = await navigator.serviceWorker.getRegistrations();
                for (let registration of registrations) {
                    await registration.unregister();
                }
                console.log('🗑️ Old Service Workers unregistered');
                return;
            }
            
            // تسجيل SW جديد
            const registration = await navigator.serviceWorker.register('/sw.js', {
                updateViaCache: 'none', // لا تخزن SW نفسه في cache
                scope: '/' // نطاق التطبيق كامل
            });
            
            console.log('✅ Service Worker v2.0 registered:', registration.scope);
            
            // التحقق من التحديثات كل 5 دقائق (تقليل التردد لتحسين الأداء)
            setInterval(() => {
                registration.update().catch(err => {
                    console.warn('⚠️ SW update check failed:', err);
                });
            }, 300000); // 5 دقائق
            
            // معالجة التحديثات
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                console.log('🔄 New Service Worker found!');
                
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        console.log('✅ New version available!');
                        // إرسال رسالة للـ SW الجديد لتفعيله فوراً
                        newWorker.postMessage({ type: 'SKIP_WAITING' });
                    }
                });
            });
            
            // عند تفعيل SW جديد
            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (!refreshing) {
                    refreshing = true;
                    console.log('🔄 SW controller changed, reloading...');
                    // تأخير صغير قبل إعادة التحميل
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                }
            });
            
        } catch (error) {
            console.warn('⚠️ Service Worker registration failed (app will work without it):', error.message);
            // التطبيق يعمل بشكل طبيعي بدون Service Worker
        }
    });
}

// 🎯 PWA: Install Prompt
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    // منع عرض الـ mini-infobar الافتراضي
    e.preventDefault();
    // حفظ الـ event لاستخدامه لاحقاً
    deferredPrompt = e;
    console.log('💾 PWA Install prompt ready!');
    
    // يمكنك هنا إظهار زر "تثبيت" مخصص
    // showInstallButton();
});

window.addEventListener('appinstalled', () => {
    console.log('🎉 PWA installed successfully!');
    deferredPrompt = null;
});
