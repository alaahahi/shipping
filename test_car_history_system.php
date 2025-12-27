<?php

/**
 * اختبار نظام تتبع تاريخ السيارات
 * هذا الملف يقوم باختبار النظام الجديد بدلاً من PHPUnit
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\Car;
use App\Models\CarHistory;
use App\Models\User;
use App\Models\Transactions;

// إعداد Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚀 بدء اختبار نظام تاريخ السيارات\n";
echo str_repeat("=", 50) . "\n\n";

$results = [
    'passed' => 0,
    'failed' => 0,
    'tests' => []
];

function test($name, $callback) {
    global $results;
    echo "🧪 اختبار: {$name}\n";

    try {
        $result = $callback();
        if ($result === true || $result === null) {
            echo "✅ نجح\n";
            $results['passed']++;
            $results['tests'][] = ['name' => $name, 'status' => 'passed'];
        } else {
            echo "❌ فشل: {$result}\n";
            $results['failed']++;
            $results['tests'][] = ['name' => $name, 'status' => 'failed', 'error' => $result];
        }
    } catch (Exception $e) {
        echo "❌ فشل باستثناء: {$e->getMessage()}\n";
        $results['failed']++;
        $results['tests'][] = ['name' => $name, 'status' => 'failed', 'error' => $e->getMessage()];
    }

    echo "\n";
}

// اختبار 1: فحص وجود جدول car_history
test('فحص وجود جدول car_history', function() {
    $tableExists = Schema::hasTable('car_history');
    return $tableExists ? true : 'جدول car_history غير موجود';
});

// اختبار 2: فحص إنشاء سجل تاريخ
test('إنشاء سجل تاريخ للسيارة', function() {
    $user = User::first();
    if (!$user) {
        $user = User::factory()->create(['owner_id' => 1, 'type_id' => 1]);
    }

    $car = Car::factory()->create(['owner_id' => 1]);

    $history = CarHistory::create([
        'car_id' => $car->id,
        'action' => 'create',
        'description' => 'تم إضافة سيارة جديدة',
        'user_id' => $user->id,
    ]);

    return $history->exists ? true : 'فشل في إنشاء سجل التاريخ';
});

// اختبار 3: فحص علاقة Car مع History
test('علاقة Car مع History', function() {
    $car = Car::factory()->create(['owner_id' => 1]);
    CarHistory::create([
        'car_id' => $car->id,
        'action' => 'create',
        'description' => 'اختبار العلاقة',
    ]);

    $car->load('history');

    return $car->history->count() > 0 ? true : 'لا توجد سجلات تاريخ مرتبطة بالسيارة';
});

// اختبار 4: فحص حذف مكتبة vue-tailwind-datepicker
test('حذف مكتبة vue-tailwind-datepicker', function() {
    $packageJson = file_get_contents(base_path('package.json'));
    $hasLibrary = strpos($packageJson, 'vue-tailwind-datepicker') !== false;

    return !$hasLibrary ? true : 'المكتبة ما زالت موجودة في package.json';
});

// اختبار 5: فحص صفحة sync-monitor
test('صفحة sync-monitor متاحة بدون تسجيل دخول', function() {
    // محاكاة طلب HTTP
    $response = null;
    try {
        // Simple curl simulation
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/sync-monitor');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode == 200 ? true : "HTTP Code: {$httpCode}";
    } catch (Exception $e) {
        return 'فشل في الاتصال بالخادم: ' . $e->getMessage();
    }
});

// اختبار 6: فحص API car history
test('API car history يتطلب مصادقة', function() {
    $car = Car::first();
    if (!$car) {
        $car = Car::factory()->create(['owner_id' => 1]);
    }

    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8000/api/car/{$car->id}/history");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // لا تتبع redirects
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // قد يكون 302 (redirect to login) أو 401 (unauthorized)
        return in_array($httpCode, [302, 401]) ? true : "HTTP Code: {$httpCode} (Expected 302 or 401)";
    } catch (Exception $e) {
        return 'فشل في الاتصال بالخادم: ' . $e->getMessage();
    }
});

// اختبار 7: فحص وجود Trait TracksHistory
test('Trait TracksHistory مُطبق على Car Model', function() {
    $traits = class_uses(Car::class);
    return in_array(App\Traits\TracksHistory::class, $traits) ? true : 'Trait TracksHistory غير مُطبق على Car Model';
});

// اختبار 8: فحص routes
test('Routes الجديدة موجودة', function() {
    $routes = app('router')->getRoutes();
    $routeNames = array_keys($routes->getRoutesByName());

    $requiredRoutes = [
        'car.history',
        'car.history.api',
        'car.history.migrate',
        'sync.monitor'
    ];

    $missingRoutes = [];
    foreach ($requiredRoutes as $route) {
        if (!in_array($route, $routeNames)) {
            $missingRoutes[] = $route;
        }
    }

    return empty($missingRoutes) ? true : 'Routes مفقودة: ' . implode(', ', $missingRoutes);
});

// اختبار 9: فحص إعدادات الجدول
test('إعدادات جدول car_history صحيحة', function() {
    $columns = Schema::getColumnListing('car_history');
    $requiredColumns = ['id', 'car_id', 'action', 'old_data', 'new_data', 'changes', 'description', 'user_id', 'created_at', 'updated_at'];

    $missingColumns = [];
    foreach ($requiredColumns as $column) {
        if (!in_array($column, $columns)) {
            $missingColumns[] = $column;
        }
    }

    return empty($missingColumns) ? true : 'أعمدة مفقودة: ' . implode(', ', $missingColumns);
});

// اختبار 10: فحص Model CarHistory
test('Model CarHistory يعمل بشكل صحيح', function() {
    $car = Car::factory()->create(['owner_id' => 1]);

    $history = CarHistory::create([
        'car_id' => $car->id,
        'action' => 'create', // استخدام قيمة صحيحة من enum
        'description' => 'اختبار Model',
    ]);

    // فحص العلاقات
    $historyWithRelations = CarHistory::with(['car', 'user'])->find($history->id);

    return $historyWithRelations && $historyWithRelations->car ? true : 'علاقات Model لا تعمل';
});

// عرض النتائج النهائية
echo str_repeat("=", 50) . "\n";
echo "📊 نتائج الاختبارات:\n";
echo "✅ نجح: {$results['passed']}\n";
echo "❌ فشل: {$results['failed']}\n";
echo "📈 إجمالي: " . ($results['passed'] + $results['failed']) . "\n\n";

if ($results['failed'] > 0) {
    echo "❌ الاختبارات الفاشلة:\n";
    foreach ($results['tests'] as $test) {
        if ($test['status'] === 'failed') {
            echo "  - {$test['name']}: {$test['error']}\n";
        }
    }
}

echo "\n🎯 الاختبارات الناجحة:\n";
foreach ($results['tests'] as $test) {
    if ($test['status'] === 'passed') {
        echo "  ✅ {$test['name']}\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";

if ($results['failed'] === 0) {
    echo "🎉 جميع الاختبارات نجحت! النظام يعمل بشكل صحيح.\n";
} else {
    echo "⚠️  هناك {$results['failed']} اختبار فاشل. يرجى مراجعة الأخطاء أعلاه.\n";
}

echo "\n💡 لتشغيل الترحيل:\n";
echo "POST /api/car-history/migrate-transactions\n";
echo "{\n";
echo "    \"limit\": 100,\n";
echo "    \"confirm_delete\": false\n";
echo "}\n";
