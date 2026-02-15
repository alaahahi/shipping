<!DOCTYPE >
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" html style="direction: rtl;">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- PWA Meta Tags -->
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="الشحن">
        <meta name="application-name" content="الشحن">
        <meta name="theme-color" content="#4f46e5">
        <meta name="description" content="نظام إدارة الشحن والعقود - يعمل بدون إنترنت">
        
        <!-- Manifest -->
        <link rel="manifest" href="/manifest.json">
        
        <!-- Icons -->
        <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512x512.png">
        <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
        
        <title inertia>Car Sale</title>
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">
        <script type="module" src="https://cdn.jsdelivr.net/npm/@duetds/date-picker@1.3.0/dist/duet/duet.esm.js"></script>
<script nomodule src="https://cdn.jsdelivr.net/npm/@duetds/date-picker@1.3.0/dist/duet/duet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@revolist/revo-dropdown@latest/dist/revo-dropdown/revo-dropdown.js"></script>
        @routes
        @vite('resources/js/app.js')
        @inertiaHead
        <style>
            .spaner{
                display: flex;
                justify-content: center;
                margin: 25px ;
            }
            body::-webkit-scrollbar {
                width: 12px;
                }

                body::-webkit-scrollbar-track {
                background: #f1f1f1;
                }

                body::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 6px;
                }

                /* Style the scrollbars for Firefox */
                body {
                scrollbar-width: thin;
                scrollbar-color: #888 #f1f1f1;
                }
                .hydrated::-webkit-scrollbar {
                width: 12px;
                }

                .hydrated::-webkit-scrollbar-track {
                background: #f1f1f1;
                }

                .hydrated::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 6px;
                }
                .scroll-rgCol
                /* Style the scrollbars for Firefox */
                .hydrated {
                scrollbar-width: thin;
                scrollbar-color: #888 #f1f1f1;
                }
                .Vue-Toastification__container {
                width: unset !important;
                }
                .duet-date__dialog {
                direction: ltr;
                    right: 0;
                    top: 44px;
                }
                .header-rgRow{
                text-align: center;
                }
                .rgRow > div {
                text-align: center !important;
                }
                .rgCell.disabled {
                    background-color: unset !important;
                }
                .rgCell{
                padding-top: 7px !important;
                }
                    .ui.fluid.search.selection.dropdown{
                    justify-content: revert!important;
                    display: flex!important;
                    min-height: 40px!important;
                }
                .ui.dropdown .menu .selected.item{
                    background-color: #e012035d !important;
                }
                .ui.dropdown .menu>.item {
                    text-align: right !important;
                }
        </style>
    </head>
    <body class="font-sans antialiased">
        @inertia
        
        <!-- نظام التبديل التلقائي بين Online/Offline -->
        <script>
            (function() {
                // قراءة URLs من Laravel
                const connectionInfo = @json($connection ?? [
                    'online_url' => config('app.url') . '/dashboard',
                    'local_url' => rtrim(config('app.url'), '/') . '/'
                ]);
                const onlineUrl = connectionInfo.online_url || (window.location.origin + '/dashboard');
                const localUrl = connectionInfo.local_url || (window.location.origin + '/');
                let isChecking = false;
                let lastCheckTime = 0;
                const CHECK_INTERVAL = 5000; // 5 ثوانٍ
                const CHECK_COOLDOWN = 2000; // 2 ثانية بين الفحوصات

                // التحقق من الاتصال الفعلي
                async function checkRealConnection() {
                    try {
                        const controller = new AbortController();
                        const timeoutId = setTimeout(() => controller.abort(), 3000);
                        
                        const response = await fetch('https://www.google.com/favicon.ico', {
                            method: 'HEAD',
                            mode: 'no-cors',
                            signal: controller.signal,
                            cache: 'no-cache'
                        });
                        
                        clearTimeout(timeoutId);
                        return true;
                    } catch (error) {
                        // محاولة بديلة
                        try {
                            const response = await fetch('https://www.google.com', {
                                method: 'HEAD',
                                mode: 'no-cors',
                                cache: 'no-cache'
                            });
                            return true;
                        } catch (e) {
                            return false;
                        }
                    }
                }

                // التحقق من تفعيل النقل التلقائي
                const autoSwitchEnabled = connectionInfo.auto_switch_enabled !== false;

                // التحقق من الاتصال والتبديل
                async function checkConnection() {
                    // إذا كان النقل التلقائي معطلاً، لا نفعل شيء
                    if (!autoSwitchEnabled) {
                        console.log('⚠️ النقل التلقائي معطّل - يمكنك التبديل يدوياً');
                        return;
                    }

                    // منع الفحص المتكرر
                    const now = Date.now();
                    if (isChecking || (now - lastCheckTime < CHECK_COOLDOWN)) {
                        return;
                    }

                    isChecking = true;
                    lastCheckTime = now;

                    try {
                        const isOnline = navigator.onLine && await checkRealConnection();
                        const currentUrl = window.location.href;
                        const isLocal = currentUrl.startsWith("http://127.0.0.1") || currentUrl.startsWith("http://localhost");

                        // إذا كان الاتصال متاحاً وكنت على اللوكل
                        if (isOnline && isLocal) {
                            // التحقق من أن السيرفر متاح قبل التبديل
                            try {
                                const testResponse = await fetch(onlineUrl.replace('/dashboard', '/api/health'), {
                                    method: 'GET',
                                    mode: 'no-cors',
                                    cache: 'no-cache'
                                });
                                
                                // الانتقال إلى السيرفر بعد تأكيد الاتصال
                                setTimeout(() => {
                                    window.location.href = onlineUrl;
                                }, 1000);
                            } catch (e) {
                                // السيرفر غير متاح، البقاء على اللوكل
                                console.log('السيرفر غير متاح، البقاء على اللوكل');
                            }
                        }
                        // إذا فقد الاتصال وكنت على السيرفر
                        else if (!isOnline && !isLocal) {
                            // الانتقال إلى اللوكل
                            setTimeout(() => {
                                window.location.href = localUrl;
                            }, 1000);
                        }
                    } catch (error) {
                        console.error('خطأ في فحص الاتصال:', error);
                    } finally {
                        isChecking = false;
                    }
                }

                // عند تغيير حالة الاتصال
                window.addEventListener('online', () => {
                    console.log('✅ الاتصال متاح');
                    setTimeout(checkConnection, 1000);
                });

                window.addEventListener('offline', () => {
                    console.log('❌ الاتصال غير متاح');
                    setTimeout(checkConnection, 1000);
                });

                // فحص عند تحميل الصفحة
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => {
                        setTimeout(checkConnection, 2000);
                    });
                } else {
                    setTimeout(checkConnection, 2000);
                }

                // فحص دوري كل 5 ثوانٍ
                setInterval(checkConnection, CHECK_INTERVAL);

                // حفظ معلومات الاتصال في window للوصول من المكونات
                window.connectionInfo = connectionInfo;

                // إضافة وظيفة عامة للتبديل اليدوي
                window.switchToLocal = function() {
                    window.location.href = localUrl;
                };

                window.switchToOnline = function() {
                    window.location.href = onlineUrl;
                };

                // إضافة وظيفة للتحقق من الاتصال يدوياً
                window.checkConnectionManually = checkConnection;
            })();
        </script>
    </body>
</html>
