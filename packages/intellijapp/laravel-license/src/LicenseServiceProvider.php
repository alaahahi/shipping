<?php

namespace IntellijApp\License;

use Illuminate\Support\ServiceProvider;
use IntellijApp\License\Console\Commands\GenerateLicense;
use IntellijApp\License\Console\Commands\VerifyLicense;
use IntellijApp\License\Console\Commands\InstallLicense;
use IntellijApp\License\Console\Commands\SyncLicense;
use IntellijApp\License\Http\Middleware\CheckLicense;

class LicenseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/Config/license.php',
            'license'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/Config/license.php' => config_path('license.php'),
        ], 'license-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/Database/Migrations' => database_path('migrations'),
        ], 'license-migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'license');

        // Register middleware
        $this->app['router']->aliasMiddleware('check.license', CheckLicense::class);

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateLicense::class,
                VerifyLicense::class,
                InstallLicense::class,
                SyncLicense::class,
            ]);
        }

        // دمج مع نظام المزامنة الموجود (إذا كان متوفراً)
        $this->integrateWithSyncSystem();
    }

    /**
     * دمج Package مع نظام المزامنة الموجود
     */
    protected function integrateWithSyncSystem(): void
    {
        // إضافة جدول licenses إلى قائمة المزامنة
        // (سيتم استدعاؤه تلقائياً عند استخدام DatabaseSyncService)
        if (class_exists(\App\Services\DatabaseSyncService::class)) {
            // يمكن إضافة logic هنا للدمج مع DatabaseSyncService
            // على سبيل المثال: إضافة 'licenses' إلى excludedTables إذا لزم الأمر
        }
    }
    }
}

