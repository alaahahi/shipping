<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Transfers;
use App\Models\BuyerPayment;
use App\Models\SalePayment;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class StatisticsController extends Controller
{
    /**
     * عرض صفحة الإحصائيات
     */
    public function index(Request $request)
    {
        return Inertia::render('Dashboard/Statistics/Index');
    }

    /**
     * API للحصول على جميع الإحصائيات
     */
    public function getStatistics(Request $request)
    {
        // الحصول على owner_id من المستخدم الحالي - جميع الاستعلامات تستخدم هذا الـ owner_id
        // تم تعديله مؤقتاً للاختبار - إذا لم يكن هناك مستخدم، استخدم owner_id = 1
        $user = Auth::user();
        $owner_id = $user ? $user->owner_id : ($request->input('owner_id', 1));
        
        // معالجة السنة والشهر
        $yearInput = $request->input('year');
        $monthInput = $request->input('month');
        
        // تحويل year إلى int أو null
        $year = null;
        if ($yearInput !== null && $yearInput !== 'null' && $yearInput !== '' && $yearInput !== 'NaN') {
            $year = is_numeric($yearInput) ? (int)$yearInput : null;
        }
        
        // تحويل month إلى int أو null
        $month = null;
        if ($monthInput !== null && $monthInput !== 'null' && $monthInput !== '' && $monthInput !== 'NaN' && is_numeric($monthInput)) {
            $monthInt = (int)$monthInput;
            if ($monthInt >= 1 && $monthInt <= 12) {
                $month = $monthInt;
            }
        }

        // استعلام أساسي للسيارات مع فلترة حسب owner_id فقط
        $query = Car::where('owner_id', $owner_id);

        // فلترة حسب السنة (إذا تم تحديدها)
        if ($year) {
            $query->whereYear('year_date', $year);
        }

        // فلترة حسب الشهر (من date) - إذا تم تحديدها
        if ($month) {
            $query->whereMonth('date', $month);
        }
        
        // إذا تم تحديد سنة مع الشهر، يجب تطبيق فلترة السنة على date أيضاً للشهر
        if ($year && $month) {
            $query->whereYear('date', $year);
        }

        // 1. عدد السيارات
        $totalCars = (clone $query)->count();

        // 2. مجموع الجمرك (شراء + بيع)
        $customPurchase = (clone $query)->sum('dolar_custom') ?? 0;
        $customSale = (clone $query)->sum('dolar_custom_s') ?? 0;
        $totalCustom = $customPurchase + $customSale;

        // 3. الفائدة من فرق سعر الصرف
        $exchangeBenefit = (clone $query)->selectRaw('SUM((dolar_price * dinar) - (dolar_price_s * dinar_s)) as benefit')
            ->value('benefit') ?? 0;

        // 4. مصاريف أربيل (من المشتريات والمبيعات)
        $erbilExpensesPurchase = (clone $query)
            ->where(function($q) {
                // محاولة البحث في note إذا كان city موجوداً في note أو في عمود منفصل
                $q->where('note', 'LIKE', '%Erbil%')
                  ->orWhere('note', 'LIKE', '%أربيل%')
                  ->orWhere('note', 'LIKE', '%اربيل%');
            })
            ->sum('expenses') ?? 0;

        $erbilExpensesSale = (clone $query)
            ->where(function($q) {
                $q->where('note', 'LIKE', '%Erbil%')
                  ->orWhere('note', 'LIKE', '%أربيل%')
                  ->orWhere('note', 'LIKE', '%اربيل%');
            })
            ->sum('expenses_s') ?? 0;

        // محاولة استخدام عمود city إذا كان موجوداً
        try {
            $erbilExpensesPurchaseCity = (clone $query)
                ->where('city', 'Erbil')
                ->sum('expenses') ?? 0;
            $erbilExpensesSaleCity = (clone $query)
                ->where('city', 'Erbil')
                ->sum('expenses_s') ?? 0;
            
            if ($erbilExpensesPurchaseCity > 0 || $erbilExpensesSaleCity > 0) {
                $erbilExpensesPurchase = $erbilExpensesPurchaseCity;
                $erbilExpensesSale = $erbilExpensesSaleCity;
            }
        } catch (\Exception $e) {
            // العمود city غير موجود، نستخدم note
        }

        $totalErbilExpenses = $erbilExpensesPurchase + $erbilExpensesSale;

        // 5. النقل الداخلي (يجب طرح 15 من expenses و expenses_s عند وجود "داخلي" في note)
        $internalShipping = (clone $query)
            ->where('note', 'LIKE', '%داخلي%')
            ->count() * 15; // عدد السيارات * 15

        // 6. حساب الربح الحقيقي لكل سيارة
        $carsWithProfit = (clone $query)->get()->map(function($car) {
            // طرح 15 من expenses و expenses_s إذا كانت note تحتوي على "داخلي"
            $adjustedExpenses = $car->expenses ?? 0;
            $adjustedExpensesS = $car->expenses_s ?? 0;
            
            if ($car->note && stripos($car->note, 'داخلي') !== false) {
                $adjustedExpenses = max(0, $adjustedExpenses - 15);
                $adjustedExpensesS = max(0, $adjustedExpensesS - 15);
            }
            
            // حساب الربح لكل سيارة حسب المعادلة المطلوبة
            // Profit = (total_s - expenses_s - discount - land_shipping_s) - (total + expenses - discount + land_shipping)
            $discount = $car->discount ?? 0;
            $profit = ($car->total_s - $adjustedExpensesS - $discount - ($car->land_shipping_s ?? 0))
                    - ($car->total + $adjustedExpenses - $discount + ($car->land_shipping ?? 0));
            
            return [
                'id' => $car->id,
                'car_number' => $car->car_number,
                'vin' => $car->vin,
                'profit' => $profit,
                'total' => $car->total ?? 0,
                'total_s' => $car->total_s ?? 0,
                'expenses' => $adjustedExpenses,
                'expenses_s' => $adjustedExpensesS,
                'discount' => $discount,
            ];
        });

        // 7. إحصائيات الأرباح
        $profits = $carsWithProfit->pluck('profit')->filter(function($p) {
            return $p !== null && $p !== '';
        });
        
        $maxProfit = $profits->max() ?? 0;
        $minProfit = $profits->min() ?? 0;
        $avgProfit = $profits->avg() ?? 0;

        // 8. تحليل الخصومات
        $maxDiscount = (clone $query)->max('discount') ?? 0;
        $minDiscount = (clone $query)->min('discount') ?? 0;
        $totalDiscounts = (clone $query)->sum('discount') ?? 0;
        $bestDiscountCar = (clone $query)->orderBy('discount', 'desc')->first();

        // 9. سجل الخصومات
        $discountRecords = (clone $query)
            ->whereNotNull('discount')
            ->where('discount', '>', 0)
            ->select('car_number', 'vin', 'discount', 'total', 'total_s')
            ->get()
            ->map(function($car) use ($carsWithProfit) {
                $carProfit = $carsWithProfit->firstWhere('id', $car->id);
                return [
                    'car_number' => $car->car_number,
                    'vin' => $car->vin,
                    'discount' => $car->discount ?? 0,
                    'total' => $car->total ?? 0,
                    'total_s' => $car->total_s ?? 0,
                    'profit' => $carProfit['profit'] ?? 0,
                ];
            })
            ->sortByDesc('discount')
            ->values();

        // 10. أرباح أربيل فقط
        $erbilProfit = 0;
        try {
            $erbilCars = (clone $query)
                ->where(function($q) {
                    $q->where('note', 'LIKE', '%Erbil%')
                      ->orWhere('note', 'LIKE', '%أربيل%')
                      ->orWhere('note', 'LIKE', '%اربيل%');
                })
                ->get();
            
            // محاولة استخدام city إذا كان موجوداً
            try {
                $erbilCarsCity = (clone $query)->where('city', 'Erbil')->get();
                if ($erbilCarsCity->count() > 0) {
                    $erbilCars = $erbilCarsCity;
                }
            } catch (\Exception $e) {
                // city غير موجود
            }

            $erbilProfit = $erbilCars->sum(function($car) use ($carsWithProfit) {
                $carProfit = $carsWithProfit->firstWhere('id', $car->id);
                return $carProfit['profit'] ?? 0;
            });
        } catch (\Exception $e) {
            // في حالة الخطأ
        }

        // الأرباح الشهرية للرسم البياني
        $monthlyProfits = [];
        $monthLabels = [];
        $selectedYear = $year ?: Carbon::now()->format('Y');
        for ($m = 1; $m <= 12; $m++) {
            $monthQuery = Car::where('owner_id', $owner_id);
            
            // إذا تم تحديد سنة، فلتر حسب year_date و date
            if ($year) {
                $monthQuery->whereYear('year_date', $year)
                           ->whereYear('date', $year);
            }
            
            $monthQuery->whereMonth('date', $m);
            
            $monthCars = $monthQuery->get();
            $monthProfit = $monthCars->sum(function($car) {
                $adjustedExpenses = $car->expenses ?? 0;
                $adjustedExpensesS = $car->expenses_s ?? 0;
                
                if ($car->note && stripos($car->note, 'داخلي') !== false) {
                    $adjustedExpenses = max(0, $adjustedExpenses - 15);
                    $adjustedExpensesS = max(0, $adjustedExpensesS - 15);
                }
                
                $discount = $car->discount ?? 0;
                return ($car->total_s - $adjustedExpensesS - $discount - ($car->land_shipping_s ?? 0))
                    - ($car->total + $adjustedExpenses - $discount + ($car->land_shipping ?? 0));
            });
            
            $monthName = Carbon::create($selectedYear, $m, 1)->locale('ar')->translatedFormat('F');
            $monthlyProfits[] = $monthProfit;
            $monthLabels[] = $monthName;
        }

        // حساب الحولات (Transfers) - فلترة حسب owner_id من خلال sender_id و receiver_id
        // Transfers لا يحتوي على owner_id مباشرة، لذلك نستخدم subquery للتحقق من owner_id للـ users
        $transfersQuery = Transfers::where(function($q) use ($owner_id) {
            $q->whereIn('sender_id', function($query) use ($owner_id) {
                $query->select('id')->from('users')->where('owner_id', $owner_id);
            })
            ->orWhereIn('receiver_id', function($query) use ($owner_id) {
                $query->select('id')->from('users')->where('owner_id', $owner_id);
            });
        });

        if ($year) {
            $transfersQuery->whereYear('created_at', $year);
        }
        if ($month) {
            $transfersQuery->whereMonth('created_at', $month);
        }

        $grossTransfers = $transfersQuery->sum('amount') ?? 0;
        $transferFees = $transfersQuery->sum('fee') ?? 0;
        $netTransfers = $grossTransfers - $transferFees;

        // حولات أربيل
        $erbilTransfers = (clone $transfersQuery)
            ->where(function($q) {
                $q->where('note', 'LIKE', '%Erbil%')
                  ->orWhere('note', 'LIKE', '%أربيل%')
                  ->orWhere('note', 'LIKE', '%اربيل%')
                  ->orWhere('sender_note', 'LIKE', '%Erbil%')
                  ->orWhere('sender_note', 'LIKE', '%أربيل%')
                  ->orWhere('receiver_note', 'LIKE', '%Erbil%')
                  ->orWhere('receiver_note', 'LIKE', '%أربيل%');
            })
            ->sum('amount') ?? 0;

        // جلب تفاصيل الحولات للعرض
        $transfersDetails = (clone $transfersQuery)
            ->select('id', 'no', 'amount', 'fee', 'stauts', 'sender_note', 'receiver_note', 'note', 'currency', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function($transfer) {
                return [
                    'id' => $transfer->id,
                    'no' => $transfer->no,
                    'amount' => $transfer->amount ?? 0,
                    'fee' => $transfer->fee ?? 0,
                    'stauts' => $transfer->stauts,
                    'status' => $transfer->stauts,
                    'sender_note' => $transfer->sender_note,
                    'receiver_note' => $transfer->receiver_note,
                    'note' => $transfer->note,
                    'currency' => $transfer->currency,
                    'created_at' => $transfer->created_at,
                ];
            });

        // حساب الدفعات (BuyerPayment + SalePayment) - فلترة حسب owner_id
        $buyerPaymentsQuery = BuyerPayment::where('owner_id', $owner_id);
        $salePaymentsQuery = SalePayment::where('owner_id', $owner_id);

        if ($year) {
            $buyerPaymentsQuery->whereYear('created_at', $year);
            $salePaymentsQuery->whereYear('created_at', $year);
        }
        if ($month) {
            $buyerPaymentsQuery->whereMonth('created_at', $month);
            $salePaymentsQuery->whereMonth('created_at', $month);
        }

        $totalBuyerPayments = $buyerPaymentsQuery->sum('amount') ?? 0;
        $totalSalePayments = $salePaymentsQuery->sum('amount') ?? 0;
        $totalPayments = $totalBuyerPayments + $totalSalePayments;

        // حساب التدفقات النقدية (Cash Flow)
        $cashIn = $totalSalePayments + $netTransfers; // الدفعات المستلمة + الحولات الصافية
        $cashOut = $totalBuyerPayments; // الدفعات المدفوعة
        $netCash = $cashIn - $cashOut;

        // التدفقات النقدية الشهرية
        $cashInMonthly = [];
        $cashOutMonthly = [];
        $yearForCashFlow = $year ?: Carbon::now()->format('Y');
        for ($m = 1; $m <= 12; $m++) {
            $buyerPaymentsQuery = BuyerPayment::where('owner_id', $owner_id);
            $salePaymentsQuery = SalePayment::where('owner_id', $owner_id);
            $transfersQuery = Transfers::where(function($q) use ($owner_id) {
                    $q->whereIn('sender_id', function($query) use ($owner_id) {
                        $query->select('id')->from('users')->where('owner_id', $owner_id);
                    })
                    ->orWhereIn('receiver_id', function($query) use ($owner_id) {
                        $query->select('id')->from('users')->where('owner_id', $owner_id);
                    });
                });
            
            if ($year) {
                $buyerPaymentsQuery->whereYear('created_at', $year);
                $salePaymentsQuery->whereYear('created_at', $year);
                $transfersQuery->whereYear('created_at', $year);
            }
            
            $buyerPaymentsQuery->whereMonth('created_at', $m);
            $salePaymentsQuery->whereMonth('created_at', $m);
            $transfersQuery->whereMonth('created_at', $m);
            
            $monthBuyerPayments = $buyerPaymentsQuery->sum('amount') ?? 0;
            $monthSalePayments = $salePaymentsQuery->sum('amount') ?? 0;
            $monthTransfers = $transfersQuery->get();
            
            $monthNetTransfers = $monthTransfers->sum('amount') - $monthTransfers->sum('fee');
            
            $cashInMonthly[] = $monthSalePayments + $monthNetTransfers;
            $cashOutMonthly[] = $monthBuyerPayments;
        }

        // حساب صافي الربح
        $netProfit = $avgProfit * $totalCars - $totalDiscounts;

        // تحديث cars_with_profit لإضافة share_1, share_2, share_3 (يمكن إضافتها لاحقاً حسب الحاجة)
        $carsWithProfitUpdated = $carsWithProfit->map(function($car) {
            return array_merge($car, [
                'net_profit' => $car['profit'],
                'share_1' => 0, // يمكن إضافتها لاحقاً
                'share_2' => 0,
                'share_3' => 0,
            ]);
        })->sortByDesc('profit')->values()->take(10);

        return response()->json([
            'total_cars' => $totalCars,
            'custom' => [
                'purchase' => $customPurchase,
                'sale' => $customSale,
                'total' => $totalCustom,
            ],
            'exchange_benefit' => $exchangeBenefit,
            'erbil_expenses' => [
                'purchase' => $erbilExpensesPurchase,
                'sale' => $erbilExpensesSale,
                'total' => $totalErbilExpenses,
            ],
            'internal_shipping' => $internalShipping,
            'profit_stats' => [
                'max' => $maxProfit,
                'min' => $minProfit,
                'avg' => $avgProfit,
            ],
            'discount_stats' => [
                'max' => $maxDiscount,
                'min' => $minDiscount,
                'total' => $totalDiscounts,
                'best_car' => $bestDiscountCar ? [
                    'car_number' => $bestDiscountCar->car_number,
                    'vin' => $bestDiscountCar->vin,
                    'discount' => $bestDiscountCar->discount,
                ] : null,
            ],
            'discount_records' => $discountRecords,
            'erbil_profit' => $erbilProfit,
            'monthly_profits' => $monthlyProfits,
            'month_labels' => $monthLabels,
            'yearly_profit' => array_sum($monthlyProfits),
            'cars_with_profit' => $carsWithProfitUpdated,
            
            // بيانات StatCards
            'cars_count' => $totalCars,
            'total_customs' => $totalCustom,
            'exchange_profit' => $exchangeBenefit,
            'net_profit' => $netProfit,
            'net_transfers' => $netTransfers,
            'cash_balance' => $netCash,
            
            // بيانات TransfersSummary
            'transfers_summary' => [
                'gross_transfers' => $grossTransfers,
                'transfer_fees' => $transferFees,
                'net_transfers' => $netTransfers,
                'erbil_transfers' => $erbilTransfers,
                'details' => $transfersDetails,
            ],
            
            // بيانات CashFlowCards
            'cash_flow' => [
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'net_cash' => $netCash,
            ],
            
            // بيانات CashFlowChart
            'cash_flow_chart' => [
                'labels' => $monthLabels,
                'cash_in_data' => $cashInMonthly,
                'cash_out_data' => $cashOutMonthly,
            ],
            
            // بيانات YearClosingSummary (يمكن إضافتها لاحقاً)
            'year_closing' => [
                'year' => (int)$year,
                'total_income' => $cashIn,
                'total_expenses' => $cashOut + $totalErbilExpenses,
                'total_discounts' => $totalDiscounts,
                'net_year_profit' => $netProfit,
                'carried_profit' => 0, // يمكن إضافتها لاحقاً
                'is_closed' => false, // يمكن إضافتها لاحقاً
            ],
        ]);
    }

    /**
     * API لإحصائيات أرباح السيارات
     */
    public function carProfitStats(Request $request)
    {
        $user = Auth::user();
        $owner_id = $user ? $user->owner_id : ($request->input('owner_id', 1));
        $year = $request->input('year', Carbon::now()->format('Y'));
        $month = $request->input('month', null);

        $query = Car::where('owner_id', $owner_id);

        if ($year) {
            $query->whereYear('year_date', $year);
        }

        if ($month) {
            $query->whereMonth('date', $month);
        }

        $cars = $query->get()->map(function($car) {
            $adjustedExpenses = $car->expenses ?? 0;
            $adjustedExpensesS = $car->expenses_s ?? 0;
            
            if ($car->note && stripos($car->note, 'داخلي') !== false) {
                $adjustedExpenses = max(0, $adjustedExpenses - 15);
                $adjustedExpensesS = max(0, $adjustedExpensesS - 15);
            }
            
            $discount = $car->discount ?? 0;
            $profit = ($car->total_s - $adjustedExpensesS - $discount - ($car->land_shipping_s ?? 0))
                    - ($car->total + $adjustedExpenses - $discount + ($car->land_shipping ?? 0));
            
            return [
                'id' => $car->id,
                'car_number' => $car->car_number,
                'vin' => $car->vin,
                'profit' => $profit,
                'total' => $car->total ?? 0,
                'total_s' => $car->total_s ?? 0,
            ];
        });

        return response()->json([
            'cars' => $cars->sortByDesc('profit')->values(),
        ]);
    }

    /**
     * API لإحصائيات الخصومات
     */
    public function discountStats(Request $request)
    {
        $user = Auth::user();
        $owner_id = $user ? $user->owner_id : ($request->input('owner_id', 1));
        $year = $request->input('year', Carbon::now()->format('Y'));
        $month = $request->input('month', null);

        $query = Car::where('owner_id', $owner_id)
            ->whereNotNull('discount')
            ->where('discount', '>', 0);

        if ($year) {
            $query->whereYear('year_date', $year);
        }

        if ($month) {
            $query->whereMonth('date', $month);
        }

        $cars = $query->select('car_number', 'vin', 'discount', 'total', 'total_s')
            ->orderBy('discount', 'desc')
            ->get()
            ->map(function($car) {
                return [
                    'car_number' => $car->car_number,
                    'vin' => $car->vin,
                    'discount' => $car->discount ?? 0,
                    'total' => $car->total ?? 0,
                    'total_s' => $car->total_s ?? 0,
                ];
            });

        return response()->json([
            'cars' => $cars,
        ]);
    }

    /**
     * تصدير البيانات إلى Excel
     */
    public function exportExcel(Request $request)
    {
        // يمكن إضافة تصدير Excel لاحقاً
        return response()->json(['message' => 'Excel export not implemented yet']);
    }
}