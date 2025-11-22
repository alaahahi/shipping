<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->booted(function () {
            $this->ensureSyncDatabaseFileExists();
            $this->configureDatabaseFailover();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // تسجيل Observers للمزامنة
        if (class_exists(\App\Models\Car::class)) {
            \App\Models\Car::observe(\App\Observers\CarObserver::class);
        }
        
        // التحقق من الترخيص عند بدء التطبيق (إذا كان مفعلاً)
        if (config('license.enabled') && !$this->app->runningInConsole()) {
            $this->checkLicenseOnBoot();
        }

        if ($this->app->runningInConsole()) {
            return;
        }

        $request = $this->app->make('request');

        if ($request) {
            if ($request->isSecure()) {
                URL::forceScheme('https');
            }

            $host = $request->getHost();
            config(['session.domain' => $host]);

            $httpHost = $request->getHttpHost();
            $statefulDomains = config('sanctum.stateful', []);
            $statefulDomains[] = $host;
            $statefulDomains[] = $httpHost;
            $statefulDomains = array_values(array_unique(array_filter($statefulDomains)));
            config(['sanctum.stateful' => $statefulDomains]);

            $rootUrl = $request->getSchemeAndHttpHost();

            URL::forceRootUrl($rootUrl);
            config(['app.url' => $rootUrl]);
        }
    }

    /**
     * التحقق من الترخيص عند بدء التطبيق
     */
    protected function checkLicenseOnBoot(): void
    {
        // السماح بالوصول لصفحات التفعيل والـ API بدون ترخيص
        $request = $this->app->make('request');
        
        if (!$request) {
            return;
        }

        $excludedPaths = [
            '/license/activate',
            '/license/status',
            '/api/license',
            '/login',
            '/register',
        ];

        $currentPath = $request->getPathInfo();

        foreach ($excludedPaths as $path) {
            if (strpos($currentPath, $path) === 0) {
                return; // السماح بالوصول
            }
        }

        // التحقق من الترخيص فقط إذا كان مفعلاً وليس في Console
        if (config('license.check_on_every_request', false)) {
            try {
                $licenseService = app(\App\Services\LicenseService::class);
                
                if (!$licenseService::isActivated()) {
                    // إذا كان الطلب API، لا نفعل شيء هنا (يتم التعامل معه في Middleware)
                    if (!$request->expectsJson() && !$request->is('api/*')) {
                        // سيتم التعامل معه في Middleware
                    }
                }
            } catch (\Exception $e) {
                Log::error('License check failed on boot', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Ensure the local sync database file exists before using it.
     *
     * @return void
     */
    protected function ensureSyncDatabaseFileExists(): void
    {
        $path = env('SYNC_SQLITE_PATH');

        if (!$path) {
            return;
        }

        $directory = dirname($path);

        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        if (!file_exists($path)) {
            @touch($path);
        }
    }

    /**
     * Dynamically switch the database connection to the fallback (SQLite)
     * whenever the primary server becomes unreachable (e.g. offline mode).
     *
     * @return void
     */
    protected function configureDatabaseFailover(): void
    {
        if (!env('DB_FAILOVER_ENABLED', false)) {
            return;
        }

        $fallbackConnection = env('DB_FALLBACK_CONNECTION', 'sync_sqlite');
        $primaryConnection = env('DB_PRIMARY_CONNECTION', 'mysql');
        
        // الأولوية 1: في البيئة المحلية (Local)، استخدم SQLite دائماً حتى مع وجود اتصال
        if (app()->environment('local')) {
            config(['database.default' => $fallbackConnection]);
            Log::channel(env('LOG_CHANNEL', 'stack'))->info('Database switched to SQLite (Local Environment)', [
                'fallback' => $fallbackConnection,
                'mode' => 'local_environment',
                'env' => app()->environment()
            ]);
            return;
        }
        
        // الأولوية 2: التحقق من الوضع اليدوي من ConnectionService
        // إذا كان المستخدم اختار Local من الواجهة، استخدم SQLite دائماً
        if (class_exists(\App\Services\ConnectionService::class)) {
            $manualMode = \App\Services\ConnectionService::getManualMode();
            
            if ($manualMode === 'local') {
                // الوضع اليدوي: Local - استخدم SQLite دائماً
                config(['database.default' => $fallbackConnection]);
                Log::channel(env('LOG_CHANNEL', 'stack'))->info('Database switched to SQLite (Manual Local Mode)', [
                    'fallback' => $fallbackConnection,
                    'mode' => 'manual_local'
                ]);
                return;
            }
            
            if ($manualMode === 'online') {
                // الوضع اليدوي: Online - استخدم MySQL دائماً
                config(['database.default' => $primaryConnection]);
                Log::channel(env('LOG_CHANNEL', 'stack'))->info('Database switched to MySQL (Manual Online Mode)', [
                    'mode' => 'manual_online'
                ]);
                return;
            }
        }

        // الأولوية 3: الوضع التلقائي - التحقق من توفر MySQL
        $cacheKey = 'db-failover:use-fallback';
        $ttl = max((int) env('DB_FAILOVER_CACHE_TTL', 60), 15);
        $shouldUseFallback = Cache::get($cacheKey, false);

        if (!$shouldUseFallback) {
            $shouldUseFallback = !$this->primaryDatabaseReachable();

            if ($shouldUseFallback) {
                Cache::put($cacheKey, true, $ttl);
            }
        } else {
            // Re-check after TTL expires by clearing the cache when the primary is back.
            if ($this->primaryDatabaseReachable()) {
                Cache::forget($cacheKey);
                $shouldUseFallback = false;
            }
        }

        if ($shouldUseFallback) {
            config(['database.default' => $fallbackConnection]);
            Log::channel(env('LOG_CHANNEL', 'stack'))->info('Database failover activated', [
                'fallback' => $fallbackConnection,
                'mode' => 'auto_failover'
            ]);
        }
    }

    /**
     * Attempt to reach the primary database host quickly using a socket ping.
     *
     * @return bool
     */
    protected function primaryDatabaseReachable(): bool
    {
        $primaryConnection = env('DB_PRIMARY_CONNECTION', config('database.default'));
        $connectionConfig = config("database.connections.{$primaryConnection}");

        if (empty($connectionConfig)) {
            return false;
        }

        $host = Arr::get($connectionConfig, 'host');
        $port = (int) Arr::get($connectionConfig, 'port', 3306);
        $timeout = (float) env('DB_FAILOVER_TIMEOUT', 2);

        if (!$host) {
            return false;
        }

        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if ($socket) {
            fclose($socket);
            return true;
        }

        Log::debug('Primary database unreachable', [
            'host' => $host,
            'port' => $port,
            'errno' => $errno ?? null,
            'error' => $errstr ?? null,
        ]);

        return false;
    }
}
