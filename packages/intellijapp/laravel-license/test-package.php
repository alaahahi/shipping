<?php

/**
 * سكربت اختبار Package نظام الترخيص
 * 
 * هذا السكربت يتحقق من:
 * 1. وجود جميع الملفات المطلوبة
 * 2. صحة Namespaces
 * 3. صحة الـ Autoloading
 * 4. وجود Config و Migrations
 */

echo "🧪 اختبار Package نظام الترخيص\n";
echo str_repeat("=", 50) . "\n\n";

$errors = [];
$warnings = [];
$success = [];

$packagePath = __DIR__;
$srcPath = $packagePath . '/src';

// 1. التحقق من وجود الملفات الأساسية
echo "📁 التحقق من الملفات الأساسية...\n";

$requiredFiles = [
    'composer.json',
    'src/LicenseServiceProvider.php',
    'src/Models/License.php',
    'src/Services/LicenseService.php',
    'src/Helpers/LicenseHelper.php',
    'src/Http/Controllers/LicenseController.php',
    'src/Http/Controllers/AdminLicenseController.php',
    'src/Http/Middleware/CheckLicense.php',
    'src/Console/Commands/GenerateLicense.php',
    'src/Console/Commands/VerifyLicense.php',
    'src/Console/Commands/InstallLicense.php',
    'src/Config/license.php',
    'src/Database/Migrations/2025_01_01_000000_create_licenses_table.php',
    'routes/web.php',
    'routes/api.php',
];

foreach ($requiredFiles as $file) {
    $fullPath = $packagePath . '/' . $file;
    if (file_exists($fullPath)) {
        $success[] = "✅ $file";
    } else {
        $errors[] = "❌ $file - غير موجود";
    }
}

echo "\n";

// 2. التحقق من Namespaces
echo "🔍 التحقق من Namespaces...\n";

$namespaceChecks = [
    'src/LicenseServiceProvider.php' => 'IntellijApp\\License',
    'src/Models/License.php' => 'IntellijApp\\License\\Models',
    'src/Services/LicenseService.php' => 'IntellijApp\\License\\Services',
    'src/Helpers/LicenseHelper.php' => 'IntellijApp\\License\\Helpers',
    'src/Http/Controllers/LicenseController.php' => 'IntellijApp\\License\\Http\\Controllers',
    'src/Http/Controllers/AdminLicenseController.php' => 'IntellijApp\\License\\Http\\Controllers',
    'src/Http/Middleware/CheckLicense.php' => 'IntellijApp\\License\\Http\\Middleware',
    'src/Console/Commands/GenerateLicense.php' => 'IntellijApp\\License\\Console\\Commands',
    'src/Console/Commands/VerifyLicense.php' => 'IntellijApp\\License\\Console\\Commands',
    'src/Console/Commands/InstallLicense.php' => 'IntellijApp\\License\\Console\\Commands',
];

foreach ($namespaceChecks as $file => $expectedNamespace) {
    $fullPath = $packagePath . '/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        if (strpos($content, "namespace $expectedNamespace") !== false) {
            $success[] = "✅ Namespace صحيح في $file";
        } else {
            $errors[] = "❌ Namespace خاطئ في $file - متوقع: $expectedNamespace";
        }
    }
}

echo "\n";

// 3. التحقق من composer.json
echo "📦 التحقق من composer.json...\n";

$composerPath = $packagePath . '/composer.json';
if (file_exists($composerPath)) {
    $composer = json_decode(file_get_contents($composerPath), true);
    
    if (isset($composer['name']) && $composer['name'] === 'intellijapp/laravel-license') {
        $success[] = "✅ Package name صحيح";
    } else {
        $errors[] = "❌ Package name خاطئ - متوقع: intellijapp/laravel-license";
    }
    
    if (isset($composer['autoload']['psr-4']['IntellijApp\\License\\'])) {
        $success[] = "✅ PSR-4 autoload صحيح";
    } else {
        $errors[] = "❌ PSR-4 autoload غير موجود أو خاطئ";
    }
    
    if (isset($composer['extra']['laravel']['providers'][0]) && 
        strpos($composer['extra']['laravel']['providers'][0], 'IntellijApp\\License\\LicenseServiceProvider') !== false) {
        $success[] = "✅ Service Provider مسجل";
    } else {
        $errors[] = "❌ Service Provider غير مسجل";
    }
}

echo "\n";

// 4. التحقق من Config
echo "⚙️  التحقق من Config...\n";

$configPath = $packagePath . '/src/Config/license.php';
if (file_exists($configPath)) {
    // تعريف Laravel helper functions مؤقتاً للاختبار
    if (!function_exists('env')) {
        function env($key, $default = null) {
            return $default;
        }
    }
    if (!function_exists('storage_path')) {
        function storage_path($path = '') {
            return __DIR__ . '/storage' . ($path ? '/' . $path : '');
        }
    }
    
    try {
        $config = include $configPath;
    } catch (Exception $e) {
        $config = [];
        $warnings[] = "⚠️  لا يمكن تحميل Config: " . $e->getMessage();
    }
    
    $requiredConfigKeys = ['enabled', 'offline_mode', 'secret_key', 'route_prefix', 'admin_route_prefix', 'admin_check'];
    foreach ($requiredConfigKeys as $key) {
        if (isset($config[$key])) {
            $success[] = "✅ Config key موجود: $key";
        } else {
            $warnings[] = "⚠️  Config key مفقود: $key";
        }
    }
}

echo "\n";

// 5. التحقق من Routes
echo "🛣️  التحقق من Routes...\n";

$webRoutesPath = $packagePath . '/routes/web.php';
$apiRoutesPath = $packagePath . '/routes/api.php';

if (file_exists($webRoutesPath)) {
    $content = file_get_contents($webRoutesPath);
    if (strpos($content, 'LicenseController') !== false && strpos($content, 'AdminLicenseController') !== false) {
        $success[] = "✅ Web routes موجودة";
    } else {
        $errors[] = "❌ Web routes غير مكتملة";
    }
}

if (file_exists($apiRoutesPath)) {
    $content = file_get_contents($apiRoutesPath);
    if (strpos($content, 'LicenseController') !== false && strpos($content, 'AdminLicenseController') !== false) {
        $success[] = "✅ API routes موجودة";
    } else {
        $errors[] = "❌ API routes غير مكتملة";
    }
}

echo "\n";

// 6. التحقق من Migration
echo "🗄️  التحقق من Migration...\n";

$migrationPath = $packagePath . '/src/Database/Migrations/2025_01_01_000000_create_licenses_table.php';
if (file_exists($migrationPath)) {
    $content = file_get_contents($migrationPath);
    if (strpos($content, "Schema::create('licenses'") !== false) {
        $success[] = "✅ Migration موجود وصحيح";
    } else {
        $errors[] = "❌ Migration غير صحيح";
    }
}

echo "\n";

// عرض النتائج
echo str_repeat("=", 50) . "\n";
echo "📊 النتائج:\n\n";

if (!empty($success)) {
    echo "✅ النجاحات (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  التحذيرات (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ الأخطاء (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "   $msg\n";
    }
    echo "\n";
}

// الخلاصة
$totalChecks = count($success) + count($warnings) + count($errors);
$successRate = count($success) / $totalChecks * 100;

echo str_repeat("=", 50) . "\n";
echo "📈 معدل النجاح: " . number_format($successRate, 2) . "%\n";
echo "✅ نجاح: " . count($success) . " | ⚠️  تحذيرات: " . count($warnings) . " | ❌ أخطاء: " . count($errors) . "\n\n";

if (empty($errors)) {
    echo "🎉 Package جاهز للاستخدام!\n";
    exit(0);
} else {
    echo "⚠️  يرجى إصلاح الأخطاء قبل الاستخدام.\n";
    exit(1);
}

