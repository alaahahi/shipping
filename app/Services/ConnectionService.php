<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ConnectionService
{
    /**
     * مفتاح Cache لحالة الاتصال
     */
    const CACHE_KEY = 'connection:status';
    const CACHE_KEY_MANUAL = 'connection:manual_mode';

    /**
     * الحصول على URL السيرفر من .env
     *
     * @return string
     */
    public static function getOnlineUrl(): string
    {
        $url = env('ONLINE_URL', env('APP_URL', 'https://system.intellijapp.com'));
        
        // إضافة /dashboard إذا لم يكن موجوداً
        $url = rtrim($url, '/');
        if (substr($url, -9) !== '/dashboard') {
            $url .= '/dashboard';
        }
        
        return $url;
    }

    /**
     * الحصول على URL المحلي من .env
     *
     * @return string
     */
    public static function getLocalUrl(): string
    {
        return rtrim(env('LOCAL_URL', env('APP_URL', config('app.url'))), '/');
    }

    /**
     * التحقق من الاتصال بالإنترنت
     *
     * @return bool
     */
    public static function checkConnection(): bool
    {
        try {
            // محاولة الاتصال بخادم خارجي
            $ch = curl_init('https://www.google.com');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $isOnline = $httpCode > 0 && $httpCode < 500;
            
            // حفظ الحالة في Cache لمدة 30 ثانية
            Cache::put(self::CACHE_KEY, $isOnline, 30);
            
            return $isOnline;
        } catch (\Exception $e) {
            Log::debug('Connection check failed', ['error' => $e->getMessage()]);
            Cache::put(self::CACHE_KEY, false, 30);
            return false;
        }
    }

    /**
     * الحصول على حالة الاتصال من Cache أو التحقق
     *
     * @return bool
     */
    public static function isOnline(): bool
    {
        return Cache::get(self::CACHE_KEY, function () {
            return self::checkConnection();
        });
    }

    /**
     * الحصول على URL المناسب بناءً على حالة الاتصال
     *
     * @return string
     */
    public static function getAppropriateUrl(): string
    {
        // إذا كان هناك وضع يدوي محدد
        $manualMode = Cache::get(self::CACHE_KEY_MANUAL);
        
        if ($manualMode === 'local') {
            return self::getLocalUrl();
        }
        
        if ($manualMode === 'online') {
            return self::getOnlineUrl();
        }
        
        // الوضع التلقائي
        return self::isOnline() ? self::getOnlineUrl() : self::getLocalUrl();
    }

    /**
     * تعيين الوضع اليدوي
     *
     * @param string $mode 'local' | 'online' | 'auto'
     * @return void
     */
    public static function setManualMode(?string $mode): void
    {
        if ($mode === null || $mode === 'auto') {
            Cache::forget(self::CACHE_KEY_MANUAL);
        } else {
            Cache::put(self::CACHE_KEY_MANUAL, $mode, 86400); // 24 ساعة
        }
    }

    /**
     * الحصول على الوضع اليدوي الحالي
     *
     * @return string|null
     */
    public static function getManualMode(): ?string
    {
        return Cache::get(self::CACHE_KEY_MANUAL);
    }

    /**
     * التحقق من أن URL الحالي مناسب
     *
     * @param string $currentUrl
     * @return bool
     */
    public static function isUrlAppropriate(string $currentUrl): bool
    {
        $appropriateUrl = self::getAppropriateUrl();
        
        // التحقق من أن URL الحالي يبدأ بـ URL المناسب
        return strpos($currentUrl, $appropriateUrl) === 0;
    }

    /**
     * التحقق من تفعيل النقل التلقائي
     *
     * @return bool
     */
    public static function isAutoSwitchEnabled(): bool
    {
        return env('AUTO_SWITCH_ENABLED', env('APP_ENV') === 'production');
    }

    /**
     * الحصول على معلومات الاتصال للعرض في الواجهة
     *
     * @return array
     */
    public static function getConnectionInfo(): array
    {
        $isOnline = self::isOnline();
        $manualMode = self::getManualMode();
        $currentUrl = request()->getSchemeAndHttpHost() . request()->getPathInfo();
        $isLocal = strpos($currentUrl, '127.0.0.1') !== false || strpos($currentUrl, 'localhost') !== false;
        $autoSwitchEnabled = self::isAutoSwitchEnabled();
        
        return [
            'is_online' => $isOnline,
            'manual_mode' => $manualMode,
            'current_url' => $currentUrl,
            'is_local' => $isLocal,
            'online_url' => self::getOnlineUrl(),
            'local_url' => self::getLocalUrl(),
            'appropriate_url' => self::getAppropriateUrl(),
            'should_redirect' => $autoSwitchEnabled && !self::isUrlAppropriate($currentUrl),
            'auto_switch_enabled' => $autoSwitchEnabled,
        ];
    }
}

