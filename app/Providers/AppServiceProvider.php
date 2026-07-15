<?php

namespace App\Providers;

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
        if (class_exists(\App\Models\CarContract::class)) {
            \App\Models\CarContract::observe(\App\Observers\CarContractObserver::class);
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
        // تعطيل التبديل التلقائي إلى SQLite عند مشاكل الاتصال — MySQL فقط
        return;
    }
}
