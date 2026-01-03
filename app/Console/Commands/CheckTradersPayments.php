<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Car;
use App\Models\Wallet;
use App\Services\AccountingCacheService;
use Illuminate\Support\Facades\DB;

class CheckTradersPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'traders:check-payments {--owner_id=} {--export=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'التحقق من دفعات جميع التجار والمقارنة مع المطلوب من السيارات';

    protected $accounting;

    public function __construct(AccountingCacheService $accounting)
    {
        parent::__construct();
        $this->accounting = $accounting;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ownerId = $this->option('owner_id') ? (int)$this->option('owner_id') : 1;
        $exportPath = $this->option('export');
        
        $this->accounting->loadAccounts($ownerId);
        
        $this->info("بدء فحص دفعات التجار لـ owner_id: {$ownerId}");
        $this->info("================================================");
        
        // الحصول على جميع التجار (Clients)
        $clientIds = User::where('type_id', $this->accounting->userClient())
            ->where('owner_id', $ownerId)
            ->pluck('id');
        
        if ($clientIds->count() == 0) {
            $this->error("لا توجد تجار لهذا owner_id");
            return 1;
        }
        
        $this->info("عدد التجار: " . $clientIds->count());
        $this->info("");
        
        $results = [];
        $totalIssues = 0;
        
        $bar = $this->output->createProgressBar($clientIds->count());
        $bar->start();
        
        foreach ($clientIds as $clientId) {
            $client = User::with('wallet')->find($clientId);
            
            if (!$client || !$client->wallet) {
                $bar->advance();
                continue;
            }
            
            // حساب المطلوب من السيارات
            $cars = Car::where('client_id', $clientId)->get();
            $carsSum = $cars->sum('total_s') ?? 0;
            $carsPaid = $cars->sum('paid') ?? 0;
            $carsDiscount = $cars->sum('discount') ?? 0;
            $carsNeedPaid = $carsSum - ($carsPaid + $carsDiscount);
            
            // حساب الدفعات الفعلية
            $walletId = $client->wallet->id;
            $actualPayments = DB::table('transactions')
                ->where('wallet_id', $walletId)
                ->where('type', 'out')
                ->where('is_pay', 1)
                ->where('amount', '<', 0)
                ->where('currency', '$')
                ->sum(DB::raw('ABS(amount)')) ?? 0;
            
            // حساب الدين الحالي
            $currentDebt = $client->wallet->balance ?? 0;
            
            // حساب الفرق
            $expectedPayments = $carsSum - $currentDebt; // المبيعات - الدين = المدفوع المتوقع
            $difference = $actualPayments - $expectedPayments;
            
            // التحقق من السيارات المحذوفة المدفوعة
            // إذا كانت cars_sum = 0 ولكن هناك دفعات فعلية، فهذا يعني وجود سيارات محذوفة
            $hasDeletedCarsIssue = false;
            $paidDeletedCars = collect([]);
            $fullyPaidDeletedCars = collect([]);
            
            // إذا كان لا توجد سيارات ولكن هناك دفعات، فهذا مؤشر على سيارات محذوفة
            if ($carsSum == 0 && $actualPayments > 0) {
                $hasDeletedCarsIssue = true;
            }
            
            // محاولة البحث عن السيارات المحذوفة إذا كان SoftDeletes مفعل
            try {
                if (method_exists(Car::class, 'withTrashed')) {
                    $paidDeletedCars = Car::withTrashed()
                        ->where('client_id', $clientId)
                        ->whereNotNull('deleted_at')
                        ->where(function($q) {
                            $q->where('paid', '>', 0)
                              ->orWhere('discount', '>', 0);
                        })
                        ->get();
                    
                    $fullyPaidDeletedCars = Car::withTrashed()
                        ->where('client_id', $clientId)
                        ->whereNotNull('deleted_at')
                        ->where('results', 2) // مكتمل الدفع
                        ->get();
                }
            } catch (\Exception $e) {
                // إذا لم يكن SoftDeletes مفعل، نستخدم المؤشر من cars_sum
            }
            
            // التحقق من عدم تطابق
            $hasIssues = false;
            $issues = [];
            
            // الفرق الكبير بين الدفعات الفعلية والمتوقعة
            if (abs($difference) > 1) { // تفاوت أكثر من 1 دولار
                $hasIssues = true;
                $issues[] = "فرق بين الدفعات الفعلية والمتوقعة: " . number_format($difference, 2);
            }
            
            // سيارات محذوفة ومدفوعة (مؤشر: لا توجد سيارات ولكن هناك دفعات)
            if ($hasDeletedCarsIssue) {
                $hasIssues = true;
                $issues[] = "مشتبه: سيارات محذوفة ومدفوعة (لا توجد سيارات ولكن هناك دفعات: " . number_format($actualPayments, 2) . ")";
            }
            
            // سيارات محذوفة ومدفوعة (من قاعدة البيانات مع SoftDeletes)
            if ($paidDeletedCars->count() > 0) {
                $hasIssues = true;
                $issues[] = "سيارات محذوفة ومدفوعة (من DB): " . $paidDeletedCars->count();
            }
            
            // سيارات مكتملة الدفع ومحذوفة
            if ($fullyPaidDeletedCars->count() > 0) {
                $hasIssues = true;
                $issues[] = "سيارات مكتملة الدفع ومحذوفة: " . $fullyPaidDeletedCars->count();
            }
            
            // الدين سالب (يعني دفع أكثر من المطلوب)
            if ($currentDebt < -1) {
                $hasIssues = true;
                $issues[] = "دين سالب (دفع زائد): " . number_format($currentDebt, 2);
            }
            
            if ($hasIssues || $hasDeletedCarsIssue || $paidDeletedCars->count() > 0 || $fullyPaidDeletedCars->count() > 0) {
                $totalIssues++;
            }
            
            $result = [
                'client_id' => $clientId,
                'client_name' => $client->name ?? 'N/A',
                'client_phone' => $client->phone ?? 'N/A',
                'cars_count' => $cars->count(),
                'cars_sum' => $carsSum,
                'cars_paid' => $carsPaid,
                'cars_discount' => $carsDiscount,
                'cars_need_paid' => $carsNeedPaid,
                'actual_payments' => $actualPayments,
                'current_debt' => $currentDebt,
                'expected_payments' => $expectedPayments,
                'difference' => $difference,
                'paid_deleted_cars_count' => $paidDeletedCars->count(),
                'fully_paid_deleted_cars_count' => $fullyPaidDeletedCars->count(),
                'has_issues' => $hasIssues || $hasDeletedCarsIssue,
                'issues' => $issues,
                'has_deleted_cars_issue' => $hasDeletedCarsIssue,
                'paid_deleted_cars' => $paidDeletedCars->map(function($car) {
                    return [
                        'id' => $car->id,
                        'car_number' => $car->car_number,
                        'vin' => $car->vin,
                        'total_s' => $car->total_s,
                        'paid' => $car->paid,
                        'discount' => $car->discount,
                        'deleted_at' => $car->deleted_at,
                    ];
                })->toArray(),
                'fully_paid_deleted_cars' => $fullyPaidDeletedCars->map(function($car) {
                    return [
                        'id' => $car->id,
                        'car_number' => $car->car_number,
                        'vin' => $car->vin,
                        'total_s' => $car->total_s,
                        'paid' => $car->paid,
                        'discount' => $car->discount,
                        'results' => $car->results,
                        'deleted_at' => $car->deleted_at,
                    ];
                })->toArray(),
            ];
            
            $results[] = $result;
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->info("");
        $this->info("");
        
        // عرض النتائج
        $this->info("نتائج الفحص:");
        $this->info("================================================");
        $this->info("إجمالي التجار: " . $clientIds->count());
        $this->info("عدد التجار الذين لديهم مشاكل: " . $totalIssues);
        $this->info("");
        
        // عرض التفاصيل
        $headers = ['ID', 'اسم التاجر', 'المبيعات', 'المدفوع', 'الخصم', 'المطلوب', 'الدفعات الفعلية', 'الدين', 'الفرق', 'مشاكل'];
        $tableData = [];
        
        foreach ($results as $result) {
            if ($result['has_issues'] || $result['paid_deleted_cars_count'] > 0 || $result['fully_paid_deleted_cars_count'] > 0) {
                $tableData[] = [
                    $result['client_id'],
                    $result['client_name'],
                    number_format($result['cars_sum'], 2),
                    number_format($result['cars_paid'], 2),
                    number_format($result['cars_discount'], 2),
                    number_format($result['cars_need_paid'], 2),
                    number_format($result['actual_payments'], 2),
                    number_format($result['current_debt'], 2),
                    number_format($result['difference'], 2),
                    implode(' | ', $result['issues']),
                ];
            }
        }
        
        if (count($tableData) > 0) {
            $this->table($headers, $tableData);
        } else {
            $this->info("✓ لا توجد مشاكل في دفعات التجار!");
        }
        
        // تصدير إلى JSON إذا طُلب
        if ($exportPath) {
            $exportData = [
                'owner_id' => $ownerId,
                'scan_date' => now()->toDateTimeString(),
                'total_traders' => $clientIds->count(),
                'traders_with_issues' => $totalIssues,
                'results' => $results,
            ];
            
            file_put_contents($exportPath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info("");
            $this->info("✓ تم تصدير النتائج إلى: " . $exportPath);
        }
        
        // عرض السيارات المحذوفة المدفوعة بالتفصيل
        $this->info("");
        $this->info("تفاصيل السيارات المحذوفة المدفوعة:");
        $this->info("================================================");
        
        $hasDeletedPaidCars = false;
        foreach ($results as $result) {
            if (count($result['paid_deleted_cars']) > 0 || count($result['fully_paid_deleted_cars']) > 0) {
                $hasDeletedPaidCars = true;
                $this->info("");
                $this->info("التاجر: " . $result['client_name'] . " (ID: " . $result['client_id'] . ")");
                
                if (count($result['paid_deleted_cars']) > 0) {
                    $this->info("  سيارات محذوفة ومدفوعة:");
                    foreach ($result['paid_deleted_cars'] as $car) {
                        $this->info("    - ID: {$car['id']}, رقم: {$car['car_number']}, VIN: {$car['vin']}, مدفوع: {$car['paid']}, خصم: {$car['discount']}");
                    }
                }
                
                if (count($result['fully_paid_deleted_cars']) > 0) {
                    $this->info("  سيارات مكتملة الدفع ومحذوفة:");
                    foreach ($result['fully_paid_deleted_cars'] as $car) {
                        $this->info("    - ID: {$car['id']}, رقم: {$car['car_number']}, VIN: {$car['vin']}, total_s: {$car['total_s']}, paid: {$car['paid']}, discount: {$car['discount']}");
                    }
                }
            }
        }
        
        if (!$hasDeletedPaidCars) {
            $this->info("✓ لا توجد سيارات محذوفة ومدفوعة");
        }
        
        return 0;
    }
}

