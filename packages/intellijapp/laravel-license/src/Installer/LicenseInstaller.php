<?php

namespace IntellijApp\License\Installer;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class LicenseInstaller
{
    /**
     * تثبيت Package
     */
    public static function install(): array
    {
        $results = [
            'config' => false,
            'migrations' => false,
            'success' => false,
        ];

        try {
            // نشر Config
            Artisan::call('vendor:publish', [
                '--tag' => 'license-config',
                '--force' => true
            ]);
            $results['config'] = true;

            // نشر Migrations
            Artisan::call('vendor:publish', [
                '--tag' => 'license-migrations',
                '--force' => true
            ]);
            $results['migrations'] = true;

            $results['success'] = true;
            $results['message'] = 'تم تثبيت Package بنجاح';

        } catch (\Exception $e) {
            $results['success'] = false;
            $results['message'] = 'فشل التثبيت: ' . $e->getMessage();
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * التحقق من المتطلبات
     */
    public static function checkRequirements(): array
    {
        $requirements = [
            'php_version' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'laravel_installed' => class_exists(\Illuminate\Foundation\Application::class),
            'storage_writable' => is_writable(storage_path()),
            'config_writable' => is_writable(config_path()),
        ];

        $allMet = array_reduce($requirements, function ($carry, $item) {
            return $carry && $item;
        }, true);

        return [
            'requirements' => $requirements,
            'all_met' => $allMet,
            'message' => $allMet ? 'جميع المتطلبات متوفرة' : 'بعض المتطلبات غير متوفرة',
        ];
    }

    /**
     * إعداد Config الأساسي
     */
    public static function setupConfig(array $options = []): bool
    {
        try {
            $configPath = config_path('license.php');
            
            if (!File::exists($configPath)) {
                return false;
            }

            $config = include $configPath;

            // تحديث Admin Check إذا تم توفيره
            if (isset($options['admin_check'])) {
                $config['admin_check'] = $options['admin_check'];
            }

            // تحديث Secret Key إذا تم توفيره
            if (isset($options['secret_key'])) {
                $config['secret_key'] = $options['secret_key'];
            }

            // حفظ Config
            $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
            File::put($configPath, $content);

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }
}

