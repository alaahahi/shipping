// =====================================
// 🧪 اختبار بسيط لنظام الكلفة والربح
// =====================================
// للتشغيل: انسخ الكود في artisan tinker
// الأمر: php artisan tinker

// 1️⃣ اختبار سعر الصرف
echo "\n=== اختبار سعر الصرف ===\n";
$config = App\Models\SystemConfig::first();
if ($config) {
    echo "✅ سعر الصرف: 1 USD = {$config->usd_to_aed_rate} AED\n";
    
    // تحويل مثال
    $usd = 100;
    $aed = $usd * $config->usd_to_aed_rate;
    echo "💱 {$usd} USD = {$aed} AED\n";
} else {
    echo "❌ لم يتم العثور على إعدادات النظام\n";
}

// 2️⃣ اختبار آخر رحلة
echo "\n=== اختبار الرحلات ===\n";
$trip = App\Models\Trip::latest()->first();
if ($trip) {
    echo "✅ الرحلة: {$trip->ship_name}\n";
    echo "   التاريخ: {$trip->sailing_date}\n";
    echo "   عدد السيارات: {$trip->total_cars}\n";
    echo "   سعر الكلفة: " . ($trip->cost_per_car_aed ?? 'غير محدد') . " درهم\n";
    echo "   عمولة القبطان: " . ($trip->captain_commission_aed ?? 'غير محدد') . " درهم\n";
    echo "   سعر الشراء: " . ($trip->purchase_price_aed ?? 'غير محدد') . " درهم\n";
} else {
    echo "❌ لا توجد رحلات\n";
}

// 3️⃣ اختبار الشركات
echo "\n=== اختبار الشركات ===\n";
$company = App\Models\TripCompany::with('company')->latest()->first();
if ($company) {
    echo "✅ الشركة: " . ($company->company->name ?? 'غير معروف') . "\n";
    echo "   السعر بالدولار: " . ($company->shipping_price_per_car ?? 'غير محدد') . " USD\n";
    echo "   السعر بالدرهم: " . ($company->shipping_price_aed ?? 'غير محدد') . " AED\n";
    echo "   عدد السيارات: " . $company->cars()->count() . "\n";
    
    // حساب الربح
    if ($company->shipping_price_aed && $trip && $trip->purchase_price_aed) {
        $profit = $company->shipping_price_aed - $trip->purchase_price_aed;
        $profitPercent = ($profit / $trip->purchase_price_aed) * 100;
        echo "\n💰 الربح:\n";
        echo "   - لكل سيارة: " . number_format($profit, 2) . " درهم\n";
        echo "   - نسبة الربح: " . number_format($profitPercent, 2) . "%\n";
        
        $carsCount = $company->cars()->count();
        if ($carsCount > 0) {
            echo "   - الربح الإجمالي: " . number_format($profit * $carsCount, 2) . " درهم\n";
        }
    }
} else {
    echo "❌ لا توجد شركات\n";
}

// 4️⃣ مثال حسابي
echo "\n=== مثال حسابي ===\n";
echo "📊 المدخلات:\n";
echo "   سعر الكلفة: 360 درهم\n";
echo "   عمولة القبطان: 20 درهم\n";
echo "   سعر الشراء: 340 درهم\n";
echo "   سعر البيع: 400 درهم\n";
echo "   عدد السيارات: 50\n";

$cost = 360;
$commission = 20;
$purchase = $cost - $commission;
$sale = 400;
$cars = 50;
$profit = $sale - $purchase;
$totalProfit = $profit * $cars;
$profitPercent = ($profit / $purchase) * 100;

echo "\n✅ النتائج:\n";
echo "   الربح لكل سيارة: {$profit} درهم\n";
echo "   الربح الإجمالي: " . number_format($totalProfit) . " درهم\n";
echo "   نسبة الربح: " . number_format($profitPercent, 2) . "%\n";

echo "\n=== ✅ انتهى الاختبار ===\n\n";
