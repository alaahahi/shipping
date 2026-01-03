<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Transfers;
use App\Models\BuyerPayment;
use App\Models\SalePayment;
use App\Models\Transactions;
use App\Models\User;
use App\Models\Wallet;
use App\Services\AccountingCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportTransfers;
use App\Exports\ExportStatistics;
use App\Exports\ExportPayments;
use Illuminate\Support\Facades\Artisan;

class StatisticsController extends Controller
{
    protected $accounting;
    
    public function __construct(AccountingCacheService $accounting)
    {
        $this->accounting = $accounting;
    }
    
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
        $owner_id_input = $user ? $user->owner_id : ($request->input('owner_id', 1));
        // تحويل owner_id إلى int لضمان التطابق
        $owner_id = is_numeric($owner_id_input) ? (int)$owner_id_input : $owner_id_input;
        
        // معالجة السنة والشهر
        $yearInput = $request->input('year');
        $yearsInput = $request->input('years', []); // array of years
        $monthInput = $request->input('month');
        
        // تحويل year إلى int أو null
        $year = null;
        if ($yearInput !== null && $yearInput !== 'null' && $yearInput !== '' && $yearInput !== 'NaN') {
            $year = is_numeric($yearInput) ? (int)$yearInput : null;
        }
        
        // تحويل years array إلى array of ints
        $years = [];
        if (is_array($yearsInput) && count($yearsInput) > 0) {
            $years = array_map(function($y) {
                return is_numeric($y) ? (int)$y : null;
            }, $yearsInput);
            $years = array_filter($years, function($y) {
                return $y !== null;
            });
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

        // فلترة حسب السنوات (من date) - إذا تم تحديد years array
        if (count($years) > 0) {
            $query->where(function($q) use ($years) {
                foreach ($years as $y) {
                    $q->orWhereYear('date', $y);
                }
            });
        } elseif ($year) {
            // إذا لم يتم تحديد years array، استخدم year الواحد
            $query->whereYear('date', $year);
        }

        // فلترة حسب الشهر (من date) - إذا تم تحديدها
        if ($month) {
            $query->whereMonth('date', $month);
        }

        // 1. عدد السيارات
        $totalCars = (clone $query)->count();
        
        // Debug مؤقت: فحص الفلترة
        $debugQuery = (clone $query);
        $debugSQL = $debugQuery->toSql();
        $debugBindings = $debugQuery->getBindings();
        $sampleCars = $debugQuery->take(3)->get(['id', 'car_number', 'year_date', 'date']);

        // 2. مجموع الجمرك (من dinar و dinar_s)
        $customPurchase = (clone $query)->sum('dinar') ?? 0;
        $customSale = (clone $query)->sum('dinar_s') ?? 0;
        $totalCustom = $customPurchase; // مجموع الجمرك من الشراء
        $customDifference = $customSale - $customPurchase; // الفرق بين البيع والشراء
        
        // السيارات التي يوجد فيها فرق في الجمرك
        $allCarsForCustom = (clone $query)->get(['id', 'car_number', 'vin', 'dinar', 'dinar_s']);
        $carsWithCustomDifference = $allCarsForCustom->map(function($car) {
                $dinar = $car->dinar ?? 0;
                $dinar_s = $car->dinar_s ?? 0;
                $difference = $dinar_s - $dinar;
                return [
                    'car_number' => $car->car_number,
                    'vin' => $car->vin,
                    'dinar' => $dinar,
                    'dinar_s' => $dinar_s,
                    'difference' => $difference,
                ];
            })
            ->filter(function($car) {
                return $car['difference'] != 0;
            })
            ->values();

        // 3. حساب الفائدة من فرق سعر الصرف
        $carsForExchange = (clone $query)->get();
        $exchangeBenefit = 0;
        $sampleCarForDebug = null;
        
        foreach ($carsForExchange as $car) {
            $dinar = $car->dinar ?? 0;
            $dinar_s = $car->dinar_s ?? 0;
            $dolar_price = $car->dolar_price ?? 1;
            $dolar_price_s = $car->dolar_price_s ?? 1;

            // Apply the same rate adjustment logic as in DashboardController
            $calc_rate = $dolar_price;
            if ($calc_rate > 9999) {
                $calc_rate = $calc_rate / 100;
            }
            $calc_rate_s = $dolar_price_s;
            if ($calc_rate_s > 9999) {
                $calc_rate_s = $calc_rate_s / 100;
            }

            if ($calc_rate > 0 && $calc_rate_s > 0 && ($dinar > 0 || $dinar_s > 0)) {
                $purchaseCustom = $dinar / $calc_rate;
                $saleCustom = $dinar_s / $calc_rate_s;
                $exchangeBenefit += ($saleCustom - $purchaseCustom);
                
                // حفظ عينة للـ debug
                if (!$sampleCarForDebug) {
                    $sampleCarForDebug = [
                        'dinar' => $dinar,
                        'dinar_s' => $dinar_s,
                        'dolar_price' => $dolar_price,
                        'dolar_price_s' => $dolar_price_s,
                        'calc_rate' => $calc_rate,
                        'calc_rate_s' => $calc_rate_s,
                        'purchase_custom' => $purchaseCustom,
                        'sale_custom' => $saleCustom,
                    ];
                }
            }
        }

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
        // Profit = (total_s - discount) - total
        $carsWithProfit = (clone $query)->get()->map(function($car) {
            $discount = $car->discount ?? 0;
            $profit = (($car->total_s ?? 0) - $discount) - ($car->total ?? 0);
            
            return [
                'id' => $car->id,
                'car_number' => $car->car_number,
                'vin' => $car->vin,
                'profit' => $profit,
                'total' => $car->total ?? 0,
                'total_s' => $car->total_s ?? 0,
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
            // لا توجد بيانات أربيل
        }

        // 11. الحولات
        $transfersQuery = Transfers::query();
        
        // فلترة حسب السنوات المتعددة
        if (count($years) > 0) {
            $transfersQuery->where(function($q) use ($years) {
                foreach ($years as $y) {
                    $q->orWhereYear('created_at', $y);
                }
            });
        } elseif ($year) {
            $transfersQuery->whereYear('created_at', $year);
        }
        
        if ($month) {
            $transfersQuery->whereMonth('created_at', $month);
        }
        $transfers = $transfersQuery->get();
        
        $grossTransfers = $transfers->sum('amount') ?? 0;
        $transferFees = $transfers->sum('fee') ?? 0;
        $netTransfers = $grossTransfers - $transferFees;
        
        // حولات أربيل
        $erbilTransfers = $transfers->filter(function($transfer) {
            $senderNote = $transfer->sender_note ?? '';
            $receiverNote = $transfer->receiver_note ?? '';
            return stripos($senderNote, 'Erbil') !== false 
                || stripos($senderNote, 'أربيل') !== false 
                || stripos($senderNote, 'اربيل') !== false
                || stripos($receiverNote, 'Erbil') !== false 
                || stripos($receiverNote, 'أربيل') !== false 
                || stripos($receiverNote, 'اربيل') !== false;
        })->sum('amount') ?? 0;
        
        // تفاصيل الحولات
        $transfersDetails = $transfers->map(function($transfer) {
            return [
                'id' => $transfer->id,
                'no' => $transfer->no,
                'amount' => $transfer->amount ?? 0,
                'fee' => $transfer->fee ?? 0,
                'status' => $transfer->stauts ?? '',
                'sender_note' => $transfer->sender_note ?? '',
                'receiver_note' => $transfer->receiver_note ?? '',
                'currency' => $transfer->currency ?? '',
                'created_at' => $transfer->created_at,
            ];
        });

        // 12. التدفقات النقدية (من BuyerPayment و SalePayment و Transfers)
        $buyerPaymentsQuery = BuyerPayment::query();
        $salePaymentsQuery = SalePayment::query();
        
        // فلترة حسب السنوات المتعددة
        if (count($years) > 0) {
            $buyerPaymentsQuery->where(function($q) use ($years) {
                foreach ($years as $y) {
                    $q->orWhereYear('created_at', $y);
                }
            });
            $salePaymentsQuery->where(function($q) use ($years) {
                foreach ($years as $y) {
                    $q->orWhereYear('created_at', $y);
                }
            });
        } elseif ($year) {
            $buyerPaymentsQuery->whereYear('created_at', $year);
            $salePaymentsQuery->whereYear('created_at', $year);
        }
        
        if ($month) {
            $buyerPaymentsQuery->whereMonth('created_at', $month);
            $salePaymentsQuery->whereMonth('created_at', $month);
        }
        
        $cashIn = $salePaymentsQuery->sum('amount') ?? 0;
        $cashOut = $buyerPaymentsQuery->sum('amount') ?? 0;
        $netCash = $cashIn - $cashOut + $netTransfers;

        // 13. الأرباح الشهرية
        $monthlyProfits = [];
        $monthLabels = [];
        
        // تحديد السنوات للاستخدام في الحسابات الشهرية
        $yearsForMonthly = count($years) > 0 ? $years : ($year ? [$year] : [Carbon::now()->format('Y')]);
        $displayYear = count($yearsForMonthly) > 0 ? (int)$yearsForMonthly[0] : (int)Carbon::now()->format('Y');
        
        for ($m = 1; $m <= 12; $m++) {
            $monthQuery = (clone $query)->whereMonth('date', $m);
            $monthCars = $monthQuery->get();
            $monthProfit = $monthCars->sum(function($car) {
                $discount = $car->discount ?? 0;
                return (($car->total_s ?? 0) - $discount) - ($car->total ?? 0);
            });
            
            $monthName = Carbon::create($displayYear, $m, 1)->locale('ar')->translatedFormat('F');
            $monthlyProfits[] = $monthProfit;
            $monthLabels[] = $monthName;
        }
        
        // التدفقات النقدية الشهرية
        $cashInMonthly = [];
        $cashOutMonthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $buyerPaymentsQuery = BuyerPayment::query();
            $salePaymentsQuery = SalePayment::query();
            $transfersQuery = Transfers::query();
            
            // فلترة حسب السنوات المتعددة
            if (count($yearsForMonthly) > 0) {
                $buyerPaymentsQuery->where(function($q) use ($yearsForMonthly, $m) {
                    foreach ($yearsForMonthly as $y) {
                        $q->orWhere(function($subQ) use ($y, $m) {
                            $subQ->whereYear('created_at', $y)->whereMonth('created_at', $m);
                        });
                    }
                });
                $salePaymentsQuery->where(function($q) use ($yearsForMonthly, $m) {
                    foreach ($yearsForMonthly as $y) {
                        $q->orWhere(function($subQ) use ($y, $m) {
                            $subQ->whereYear('created_at', $y)->whereMonth('created_at', $m);
                        });
                    }
                });
                $transfersQuery->where(function($q) use ($yearsForMonthly, $m) {
                    foreach ($yearsForMonthly as $y) {
                        $q->orWhere(function($subQ) use ($y, $m) {
                            $subQ->whereYear('created_at', $y)->whereMonth('created_at', $m);
                        });
                    }
                });
            } else {
                $buyerPaymentsQuery->whereYear('created_at', $displayYear)->whereMonth('created_at', $m);
                $salePaymentsQuery->whereYear('created_at', $displayYear)->whereMonth('created_at', $m);
                $transfersQuery->whereYear('created_at', $displayYear)->whereMonth('created_at', $m);
            }
            
            $monthBuyerPayments = $buyerPaymentsQuery->sum('amount') ?? 0;
            $monthSalePayments = $salePaymentsQuery->sum('amount') ?? 0;
            $monthTransfers = $transfersQuery->get();
            
            $monthNetTransfers = $monthTransfers->sum('amount') - $monthTransfers->sum('fee');
            
            $cashInMonthly[] = $monthSalePayments + $monthNetTransfers;
            $cashOutMonthly[] = $monthBuyerPayments;
        }

        // حساب صافي الربح
        $netProfit = $avgProfit * $totalCars - $totalDiscounts;
        
        // حساب مجموع المبيعات والمشتريات والفرق
        $totalSales = (clone $query)->sum('total_s') ?? 0;
        $totalPurchases = (clone $query)->sum('total') ?? 0;
        $salesPurchaseDifference = $totalSales - $totalPurchases;
        
        // حساب دين التجار (Clients)
        $this->accounting->loadAccounts($owner_id);
        $clientIds = User::where('type_id', $this->accounting->userClient())
            ->where('owner_id', $owner_id)
            ->pluck('id');
        
        // الحصول على wallet_ids للتجار
        $walletIds = Wallet::whereIn('user_id', $clientIds)->pluck('id');
        
        // حساب دين التجار (balance الحالي - سالب يعني دين)
        $tradersDebt = Wallet::whereIn('user_id', $clientIds)->sum('balance') ?? 0;
        
        // حساب مجموع الدفعات التي دفعها التجار
        // استخدام نفس الاستعلام المستخدم في صفحة التاجر:
        // Transactions من نوع 'out' مع is_pay = 1 و amount < 0
        $tradersPayments = 0;
        if ($walletIds->count() > 0) {
            $paymentsQuery = Transactions::whereIn('wallet_id', $walletIds)
                ->where('type', 'out')
                ->where('is_pay', 1)
                ->where('amount', '<', 0)
                ->where('currency', '$');
            
            // فلترة حسب السنوات والشهر إذا كانت محددة
            if (count($years) > 0) {
                $paymentsQuery->where(function($q) use ($years) {
                    foreach ($years as $y) {
                        $q->orWhereYear('created', $y);
                    }
                });
            } elseif ($year) {
                $paymentsQuery->whereYear('created', $year);
            }
            
            if ($month) {
                $paymentsQuery->whereMonth('created', $month);
            }
            
            // الدفعات سالبة، لذلك نستخدم ABS
            $tradersPayments = $paymentsQuery->sum(DB::raw('ABS(amount)')) ?? 0;
        }
        
        // حساب مجموع الخصومات من السيارات المفلترة
        $totalDiscountsFromCars = (clone $query)->sum('discount') ?? 0;
        
        // المبيعات الصافية = total_s - discount (للمقارنة مع الدفعات)
        $netSales = $totalSales - $totalDiscountsFromCars;
        
        // إجمالي الدفعات + الدين (الدين سالب، لذلك نطرحه)
        // إذا كان الدين سالب (مثل -1000)، يعني التاجر مدين 1000
        // مجموع الدفعات - الدين = مجموع الدفعات + القيمة المطلقة للدين
        $totalPaymentsAndDebt = $tradersPayments - $tradersDebt; // نطرح الدين لأنه سالب
        
        // المقارنة: المبيعات - (الدفعات + الدين)
        $salesVsPaymentsDifference = $totalSales - $totalPaymentsAndDebt;

        // تحديث cars_with_profit لإضافة share_1, share_2, share_3 (يمكن إضافتها لاحقاً حسب الحاجة)
        $carsWithProfitUpdated = $carsWithProfit->map(function($car) {
            return array_merge($car, [
                'net_profit' => $car['profit'],
                'share_1' => 0, // يمكن إضافتها لاحقاً
                'share_2' => 0,
                'share_3' => 0,
            ]);
        })->sortByDesc('profit')->values()->take(10);
        
        // أقل 10 سيارات ربحاً
        $carsWithLowestProfit = $carsWithProfit->map(function($car) {
            return array_merge($car, [
                'net_profit' => $car['profit'],
                'share_1' => 0,
                'share_2' => 0,
                'share_3' => 0,
            ]);
        })->sortBy('profit')->values()->take(10);

        $data = [
            'total_cars' => $totalCars,
            'custom' => [
                'purchase' => $customPurchase, // مجموع dinar
                'sale' => $customSale, // مجموع dinar_s
                'total' => $totalCustom, // مجموع الجمرك من الشراء
                'difference' => $customDifference, // الفرق بين البيع والشراء
                'cars_with_difference' => $carsWithCustomDifference, // السيارات التي يوجد فيها فرق
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
            'cars_with_lowest_profit' => $carsWithLowestProfit,
            
            // بيانات StatCards
            'cars_count' => $totalCars,
            'total_customs' => $totalCustom,
            'exchange_profit' => $exchangeBenefit,
            'net_profit' => $netProfit,
            'net_transfers' => $netTransfers,
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'sales_purchase_difference' => $salesPurchaseDifference,
            'traders_debt' => $tradersDebt,
            'traders_payments' => $tradersPayments,
            'total_payments_and_debt' => $totalPaymentsAndDebt,
            'sales_vs_payments_difference' => $salesVsPaymentsDifference,
            
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
        ];
        
        // Debug مؤقت
        if ($request->input('debug') == '1') {
            $data['_debug'] = [
                'year' => $year,
                'month' => $month,
                'owner_id_requested' => $owner_id_input,
                'owner_id_type' => gettype($owner_id_input),
                'owner_id_final' => $owner_id,
                'total_cars' => $totalCars,
                'sql' => $debugSQL,
                'bindings' => $debugBindings,
                'sample_cars' => $sampleCars,
                'sample_exchange_car' => $sampleCarForDebug,
            ];
        }
        
        return response()->json($data);
    }

    /**
     * API لإحصائيات أرباح السيارات
     */
    public function carProfitStats(Request $request)
    {
        $user = Auth::user();
        $owner_id_input = $user ? $user->owner_id : ($request->input('owner_id', 1));
        $owner_id = is_numeric($owner_id_input) ? (int)$owner_id_input : $owner_id_input;
        $year = $request->input('year', Carbon::now()->format('Y'));
        $month = $request->input('month', null);

        $query = Car::where('owner_id', $owner_id);

        if ($year) {
            $query->whereYear('date', $year);
        }

        if ($month) {
            $query->whereMonth('date', $month);
        }

        $cars = $query->get()->map(function($car) {
            $discount = $car->discount ?? 0;
            $profit = (($car->total_s ?? 0) - $discount) - ($car->total ?? 0);
            
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
        $owner_id_input = $user ? $user->owner_id : ($request->input('owner_id', 1));
        $owner_id = is_numeric($owner_id_input) ? (int)$owner_id_input : $owner_id_input;
        $year = $request->input('year', Carbon::now()->format('Y'));
        $month = $request->input('month', null);

        $query = Car::where('owner_id', $owner_id)
            ->whereNotNull('discount')
            ->where('discount', '>', 0);

        if ($year) {
            $query->whereYear('date', $year);
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
     * API لإعادة حساب الربح لكل سيارة
     */
    public function recalculateProfit(Request $request)
    {
        $user = Auth::user();
        $owner_id_input = $user ? $user->owner_id : ($request->input('owner_id', 1));
        $owner_id = is_numeric($owner_id_input) ? (int)$owner_id_input : $owner_id_input;
        $year = $request->input('year', null);
        $yearsInput = $request->input('years', []);
        $month = $request->input('month', null);

        $query = Car::where('owner_id', $owner_id);

        // معالجة years array
        $years = [];
        if (is_array($yearsInput) && count($yearsInput) > 0) {
            $years = array_map(function($y) {
                return is_numeric($y) ? (int)$y : null;
            }, $yearsInput);
            $years = array_filter($years, function($y) {
                return $y !== null;
            });
        }

        if (count($years) > 0) {
            $query->where(function($q) use ($years) {
                foreach ($years as $y) {
                    $q->orWhereYear('date', $y);
                }
            });
        } elseif ($year) {
            $query->whereYear('date', $year);
        }

        if ($month) {
            $query->whereMonth('date', $month);
        }

        $cars = $query->get();
        $updatedCars = [];

        foreach ($cars as $car) {
            $discount = $car->discount ?? 0;
            $newProfit = (($car->total_s ?? 0) - $discount) - ($car->total ?? 0);
            $oldProfit = $car->profit ?? 0;
            
            if ($oldProfit != $newProfit) {
                $car->profit = $newProfit;
                $car->save();
                
                $updatedCars[] = [
                    'id' => $car->id,
                    'car_number' => $car->car_number,
                    'vin' => $car->vin,
                    'old_profit' => $oldProfit,
                    'new_profit' => $newProfit,
                    'total' => $car->total ?? 0,
                    'total_s' => $car->total_s ?? 0,
                    'discount' => $discount,
                ];
            }
        }

        return response()->json([
            'message' => 'تم إعادة حساب الربح بنجاح',
            'total_cars' => $cars->count(),
            'updated_cars' => count($updatedCars),
            'cars' => $updatedCars,
        ]);
    }

    /**
     * API لمجموع الربح من كل تاجر
     */
    public function tradersProfit(Request $request)
    {
        $user = Auth::user();
        $owner_id_input = $user ? $user->owner_id : ($request->input('owner_id', 1));
        $owner_id = is_numeric($owner_id_input) ? (int)$owner_id_input : $owner_id_input;
        $year = $request->input('year', null);
        $yearsInput = $request->input('years', []);
        $month = $request->input('month', null);

        $this->accounting->loadAccounts($owner_id);
        $clientIds = User::where('type_id', $this->accounting->userClient())
            ->where('owner_id', $owner_id)
            ->pluck('id');

        $query = Car::where('owner_id', $owner_id)
            ->whereIn('client_id', $clientIds);

        // معالجة years array
        $years = [];
        if (is_array($yearsInput) && count($yearsInput) > 0) {
            $years = array_map(function($y) {
                return is_numeric($y) ? (int)$y : null;
            }, $yearsInput);
            $years = array_filter($years, function($y) {
                return $y !== null;
            });
        }

        if (count($years) > 0) {
            $query->where(function($q) use ($years) {
                foreach ($years as $y) {
                    $q->orWhereYear('date', $y);
                }
            });
        } elseif ($year) {
            $query->whereYear('date', $year);
        }

        if ($month) {
            $query->whereMonth('date', $month);
        }

        $cars = $query->get();

        // تجميع السيارات حسب client_id
        $tradersProfit = [];
        foreach ($cars as $car) {
            $clientId = $car->client_id;
            if (!$clientId) continue;

            if (!isset($tradersProfit[$clientId])) {
                $client = User::find($clientId);
                $tradersProfit[$clientId] = [
                    'trader_id' => $clientId,
                    'trader_name' => $client->name ?? '',
                    'trader_phone' => $client->phone ?? '',
                    'total_profit' => 0,
                    'cars_count' => 0,
                    'total_sales' => 0,
                    'total_purchases' => 0,
                ];
            }

            $discount = $car->discount ?? 0;
            $profit = (($car->total_s ?? 0) - $discount) - ($car->total ?? 0);
            
            $tradersProfit[$clientId]['total_profit'] += $profit;
            $tradersProfit[$clientId]['cars_count'] += 1;
            $tradersProfit[$clientId]['total_sales'] += ($car->total_s ?? 0);
            $tradersProfit[$clientId]['total_purchases'] += ($car->total ?? 0);
        }

        $result = collect($tradersProfit)->map(function($trader) {
            return $trader;
        })->sortByDesc('total_profit')->values();

        return response()->json([
            'traders' => $result,
            'total_traders' => $result->count(),
        ]);
    }

    /**
     * تصدير الحولات إلى Excel
     */
    public function exportTransfersExcel(Request $request)
    {
        $from = $request->input('from', null);
        $to = $request->input('to', null);
        $year = $request->input('year', null);
        $yearsInput = $request->input('years', []);
        $month = $request->input('month', null);

        // معالجة years array
        $years = [];
        if (is_array($yearsInput) && count($yearsInput) > 0) {
            $years = array_map(function($y) {
                return is_numeric($y) ? (int)$y : null;
            }, $yearsInput);
            $years = array_filter($years, function($y) {
                return $y !== null;
            });
        }

        $fileName = 'الحولات';
        if ($from && $to) {
            $fileName .= '_' . $from . '_' . $to;
        } elseif ($from) {
            $fileName .= '_من_' . $from;
        } elseif (count($years) > 0) {
            $fileName .= '_' . implode('_', $years);
        } elseif ($year) {
            $fileName .= '_' . $year;
        }
        $fileName .= '.xlsx';

        return Excel::download(new ExportTransfers($from, $to, $years, $month), $fileName);
    }

    /**
     * تصدير الإحصائيات العامة إلى Excel
     */
    public function exportExcel(Request $request)
    {
        $year = $request->input('year', null);
        $yearsInput = $request->input('years', []);
        $month = $request->input('month', null);
        $owner_id_input = $request->input('owner_id', null);
        
        // معالجة owner_id
        $user = Auth::user();
        $owner_id_input = $user ? $user->owner_id : ($owner_id_input ?? 1);
        $owner_id = is_numeric($owner_id_input) ? (int)$owner_id_input : $owner_id_input;
        
        // معالجة years array
        $years = [];
        if (is_array($yearsInput) && count($yearsInput) > 0) {
            $years = array_map(function($y) {
                return is_numeric($y) ? (int)$y : null;
            }, $yearsInput);
            $years = array_filter($years, function($y) {
                return $y !== null;
            });
        }
        
        // جلب البيانات باستخدام نفس منطق getStatistics
        $query = Car::where('owner_id', $owner_id);
        
        if (count($years) > 0) {
            $query->where(function($q) use ($years) {
                foreach ($years as $y) {
                    $q->orWhereYear('date', $y);
                }
            });
        } elseif ($year) {
            $query->whereYear('date', $year);
        }
        
        if ($month) {
            $query->whereMonth('date', $month);
        }
        
        // حساب الإحصائيات الأساسية
        $totalCars = $query->count();
        $totalSales = (clone $query)->sum('total_s') ?? 0;
        $totalPurchases = (clone $query)->sum('total') ?? 0;
        $salesPurchaseDifference = $totalSales - $totalPurchases;
        $totalCustom = (clone $query)->sum('dinar') ?? 0;
        
        // حساب exchange benefit
        $carsForExchange = (clone $query)->get();
        $exchangeBenefit = 0;
        foreach ($carsForExchange as $car) {
            $dinar = $car->dinar ?? 0;
            $dinar_s = $car->dinar_s ?? 0;
            $dolar_price = $car->dolar_price ?? 1;
            $dolar_price_s = $car->dolar_price_s ?? 1;

            $calc_rate_purchase = $dolar_price;
            if ($calc_rate_purchase > 9999) {
                $calc_rate_purchase = $calc_rate_purchase / 100;
            }
            $calc_rate_sale = $dolar_price_s;
            if ($calc_rate_sale > 9999) {
                $calc_rate_sale = $calc_rate_sale / 100;
            }

            if ($calc_rate_purchase > 0 && $calc_rate_sale > 0) {
                $purchaseCustom = $dinar / $calc_rate_purchase;
                $saleCustom = $dinar_s / $calc_rate_sale;
                $exchangeBenefit += ($saleCustom - $purchaseCustom);
            }
        }
        
        // حساب net profit
        $cars = (clone $query)->get();
        $totalDiscounts = $cars->sum('discount') ?? 0;
        $avgProfit = $cars->avg(function($car) {
            $discount = $car->discount ?? 0;
            return (($car->total_s ?? 0) - $discount) - ($car->total ?? 0);
        }) ?? 0;
        $netProfit = $avgProfit * $totalCars - $totalDiscounts;
        
        // حساب net transfers
        $transfersQuery = Transfers::query();
        if (count($years) > 0) {
            $transfersQuery->where(function($q) use ($years) {
                foreach ($years as $y) {
                    $q->orWhereYear('created_at', $y);
                }
            });
        } elseif ($year) {
            $transfersQuery->whereYear('created_at', $year);
        }
        if ($month) {
            $transfersQuery->whereMonth('created_at', $month);
        }
        $transfers = $transfersQuery->get();
        $grossTransfers = $transfers->sum('amount') ?? 0;
        $transferFees = $transfers->sum('fee') ?? 0;
        $netTransfers = $grossTransfers - $transferFees;
        
        // حساب traders debt
        $this->accounting->loadAccounts($owner_id);
        $clientIds = User::where('type_id', $this->accounting->userClient())
            ->where('owner_id', $owner_id)
            ->pluck('id');
        $tradersDebt = Wallet::whereIn('user_id', $clientIds)->sum('balance') ?? 0;
        
        // الأرباح الشهرية
        $monthlyProfits = [];
        $monthLabels = [];
        $yearsForMonthly = count($years) > 0 ? $years : ($year ? [$year] : [Carbon::now()->format('Y')]);
        $displayYear = count($yearsForMonthly) > 0 ? (int)$yearsForMonthly[0] : (int)Carbon::now()->format('Y');
        
        for ($m = 1; $m <= 12; $m++) {
            $monthQuery = (clone $query)->whereMonth('date', $m);
            $monthCars = $monthQuery->get();
            $monthProfit = $monthCars->sum(function($car) {
                $discount = $car->discount ?? 0;
                return (($car->total_s ?? 0) - $discount) - ($car->total ?? 0);
            });
            
            $monthName = Carbon::create($displayYear, $m, 1)->locale('ar')->translatedFormat('F');
            $monthlyProfits[] = $monthProfit;
            $monthLabels[] = $monthName;
        }
        
        $statistics = [
            'cars_count' => $totalCars,
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'sales_purchase_difference' => $salesPurchaseDifference,
            'traders_debt' => $tradersDebt,
            'total_customs' => $totalCustom,
            'exchange_profit' => $exchangeBenefit,
            'net_profit' => $netProfit,
            'net_transfers' => $netTransfers,
            'monthly_profits' => $monthlyProfits,
            'month_labels' => $monthLabels,
        ];
        
        $fileName = 'الإحصائيات_العامة';
        if (!empty($years)) {
            $fileName .= '_' . implode('_', $years);
        } elseif ($year) {
            $fileName .= '_' . $year;
        }
        if ($month) {
            $monthName = Carbon::create($displayYear, $month, 1)->locale('ar')->translatedFormat('F');
            $fileName .= '_' . $monthName;
        }
        $fileName .= '.xlsx';
        
        return Excel::download(new ExportStatistics($statistics, $year, $years), $fileName);
    }

    /**
     * تصدير دفعات التجار إلى Excel
     */
    public function exportPaymentsExcel(Request $request)
    {
        $year = $request->input('year', null);
        $yearsInput = $request->input('years', []);
        $month = $request->input('month', null);
        $owner_id_input = $request->input('owner_id', null);
        
        // معالجة owner_id
        $user = Auth::user();
        $owner_id_input = $user ? $user->owner_id : ($owner_id_input ?? 1);
        $owner_id = is_numeric($owner_id_input) ? (int)$owner_id_input : $owner_id_input;
        
        // معالجة years array
        $years = [];
        if (is_array($yearsInput) && count($yearsInput) > 0) {
            $years = array_map(function($y) {
                return is_numeric($y) ? (int)$y : null;
            }, $yearsInput);
            $years = array_filter($years, function($y) {
                return $y !== null;
            });
        }
        
        $fileName = 'دفعات_التجار';
        if (!empty($years)) {
            $fileName .= '_' . implode('_', $years);
        } elseif ($year) {
            $fileName .= '_' . $year;
        }
        if ($month) {
            $monthNames = ['', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
            $fileName .= '_' . ($monthNames[$month] ?? $month);
        }
        $fileName .= '.xlsx';
        
        return Excel::download(new ExportPayments($this->accounting, $owner_id, $year, $years, $month), $fileName);
    }

    /**
     * فحص دفعات جميع التجار والتحقق من المشاكل
     */
    public function checkTradersPayments(Request $request)
    {
        $owner_id_input = $request->input('owner_id', null);
        $user = Auth::user();
        $owner_id_input = $user ? $user->owner_id : ($owner_id_input ?? 1);
        $owner_id = is_numeric($owner_id_input) ? (int)$owner_id_input : $owner_id_input;
        
        $this->accounting->loadAccounts($owner_id);
        
        $clientIds = User::where('type_id', $this->accounting->userClient())
            ->where('owner_id', $owner_id)
            ->pluck('id');
        
        if ($clientIds->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد تجار لهذا owner_id',
            ], 404);
        }
        
        $results = [];
        $totalIssues = 0;
        
        foreach ($clientIds as $clientId) {
            $client = User::with('wallet')->find($clientId);
            
            if (!$client || !$client->wallet) {
                continue;
            }
            
            // حساب المطلوب من السيارات (استبعاد المحذوفة)
            $cars = Car::where('client_id', $clientId)
                ->whereNull('deleted_at')
                ->get();
            $carsSum = $cars->sum('total_s') ?? 0;
            $carsPaid = $cars->sum('paid') ?? 0;
            $carsDiscount = $cars->sum('discount') ?? 0;
            $carsNeedPaid = $carsSum - ($carsPaid + $carsDiscount);
            
            // حساب الدفعات الفعلية من Transactions
            // استخدام نفس الاستعلام المستخدم في getStatistics
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
            // المطلوب من السيارات = total_s (إجمالي المبيعات)
            // المقارنة: الدفعات الفعلية مقابل المطلوب (total_s)
            $expectedPayments = $carsSum;
            $difference = $actualPayments - $expectedPayments;
            
            // التحقق من السيارات المحذوفة المدفوعة
            $hasDeletedCarsIssue = false;
            $paidDeletedCars = collect([]);
            $fullyPaidDeletedCars = collect([]);
            
            if ($carsSum == 0 && $actualPayments > 0) {
                $hasDeletedCarsIssue = true;
            }
            
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
                        ->where('results', 2)
                        ->get();
                }
            } catch (\Exception $e) {
                // SoftDeletes غير مفعل
            }
            
            // التحقق من المشاكل
            $hasIssues = false;
            $issues = [];
            
            if (abs($difference) > 1) {
                $hasIssues = true;
                $issues[] = "فرق بين الدفعات الفعلية والمتوقعة: " . number_format($difference, 2);
            }
            
            if ($hasDeletedCarsIssue) {
                $hasIssues = true;
                $issues[] = "مشتبه: سيارات محذوفة ومدفوعة (لا توجد سيارات ولكن هناك دفعات: " . number_format($actualPayments, 2) . ")";
            }
            
            if ($paidDeletedCars->count() > 0) {
                $hasIssues = true;
                $issues[] = "سيارات محذوفة ومدفوعة (من DB): " . $paidDeletedCars->count();
            }
            
            if ($fullyPaidDeletedCars->count() > 0) {
                $hasIssues = true;
                $issues[] = "سيارات مكتملة الدفع ومحذوفة: " . $fullyPaidDeletedCars->count();
            }
            
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
            
            // إضافة فقط التجار الذين لديهم مشاكل
            if ($result['has_issues']) {
                $results[] = $result;
            }
        }
        
        return response()->json([
            'success' => true,
            'owner_id' => $owner_id,
            'total_traders' => $clientIds->count(),
            'traders_with_issues' => $totalIssues,
            'scan_date' => now()->toDateTimeString(),
            'results' => $results,
        ]);
    }

    /**
     * الحصول على السيارات المحذوفة
     */
    public function getDeletedCars(Request $request)
    {
        $owner_id_input = $request->input('owner_id', null);
        
        // معالجة owner_id
        $user = Auth::user();
        $owner_id_input = $user ? $user->owner_id : ($owner_id_input ?? 1);
        $owner_id = is_numeric($owner_id_input) ? (int)$owner_id_input : $owner_id_input;
        
        $year = $request->input('year', null);
        $yearsInput = $request->input('years', []);
        $month = $request->input('month', null);
        
        // معالجة years array
        $years = [];
        if (is_array($yearsInput) && count($yearsInput) > 0) {
            $years = array_map(function($y) {
                return is_numeric($y) ? (int)$y : null;
            }, $yearsInput);
            $years = array_filter($years, function($y) {
                return $y !== null;
            });
        }
        
        try {
            $deletedCars = [];
            
            // البحث عن Transactions التي تحتوي على نص "مرتج حذف سيارة"
            // هذه هي Transactions الخاصة بحذف السيارات
            $query = Transactions::where('morphed_type', 'App\Models\Car')
                ->whereNotNull('morphed_id')
                ->where('description', 'LIKE', '%مرتج حذف سيارة%')
                ->with(['wallet.user']);
            
            // فلترة حسب السنوات (من تاريخ Transaction)
            if (count($years) > 0) {
                $query->where(function($q) use ($years) {
                    foreach ($years as $y) {
                        $q->orWhereYear('created', $y);
                    }
                });
            } elseif ($year) {
                $query->whereYear('created', $year);
            }
            
            // فلترة حسب الشهر
            if ($month) {
                $query->whereMonth('created', $month);
            }
            
            $deleteTransactions = $query->orderBy('created', 'desc')->get();
            
            // تصفية Transactions التي تنتمي لـ owner_id المحدد
            $filteredTransactions = $deleteTransactions->filter(function($transaction) use ($owner_id) {
                return $transaction->wallet && 
                       $transaction->wallet->user && 
                       $transaction->wallet->user->owner_id == $owner_id;
            });
            
            if ($filteredTransactions->count() > 0) {
                $deletedCarsData = [];
                
                foreach ($filteredTransactions as $deleteTransaction) {
                    $carId = $deleteTransaction->morphed_id;
                    
                    // محاولة الحصول على معلومات السيارة من Transactions الأخرى المرتبطة بنفس السيارة
                    $carTransactions = Transactions::where('morphed_type', 'App\Models\Car')
                        ->where('morphed_id', $carId)
                        ->whereHas('wallet', function($q) use ($owner_id) {
                            $q->whereHas('user', function($uq) use ($owner_id) {
                                $uq->where('owner_id', $owner_id);
                            });
                        })
                        ->orderBy('created', 'desc')
                        ->get();
                    
                    // محاولة استخراج معلومات السيارة من Transaction details
                    $carNumber = 'N/A';
                    $vin = 'N/A';
                    $carType = 'N/A';
                    $total = 0;
                    $totalS = 0;
                    $paid = 0;
                    $discount = 0;
                    
                    foreach ($carTransactions as $trans) {
                        if ($trans->details && is_array($trans->details)) {
                            foreach ($trans->details as $detail) {
                                if (isset($detail['car_number']) && $detail['car_number'] && $carNumber === 'N/A') {
                                    $carNumber = $detail['car_number'];
                                }
                                if (isset($detail['vin']) && $detail['vin'] && $vin === 'N/A') {
                                    $vin = $detail['vin'];
                                }
                                if (isset($detail['car_type']) && $detail['car_type'] && $carType === 'N/A') {
                                    $carType = $detail['car_type'];
                                }
                            }
                        }
                    }
                    
                    // محاولة استخراج المبلغ من description (مرتج حذف سيارةXXXX)
                    $description = $deleteTransaction->description ?? '';
                    if (preg_match('/مرتج حذف سيارة\s*([0-9.]+)/', $description, $matches)) {
                        $total = floatval($matches[1]);
                    }
                    
                    // معلومات التاجر
                    $clientId = null;
                    $clientName = 'N/A';
                    $clientPhone = 'N/A';
                    
                    if ($deleteTransaction->wallet && $deleteTransaction->wallet->user) {
                        $clientId = $deleteTransaction->wallet->user_id;
                        $clientName = $deleteTransaction->wallet->user->name ?? 'N/A';
                        $clientPhone = $deleteTransaction->wallet->user->phone ?? 'N/A';
                    }
                    
                    // تاريخ الحذف
                    $deletedAt = $deleteTransaction->created ?? 
                               ($deleteTransaction->created_at ? $deleteTransaction->created_at->format('Y-m-d H:i:s') : null);
                    
                    // تجنب التكرار (إذا كانت نفس السيارة محذوفة عدة مرات، نأخذ أحدث عملية حذف)
                    $existingIndex = array_search($carId, array_column($deletedCarsData, 'id'));
                    if ($existingIndex === false) {
                        $deletedCarsData[] = [
                            'id' => $carId,
                            'car_number' => $carNumber,
                            'vin' => $vin,
                            'car_type' => $carType,
                            'year' => null,
                            'car_color' => null,
                            'total_s' => $totalS,
                            'total' => $total,
                            'paid' => $paid,
                            'discount' => $discount,
                            'profit' => $totalS - $total,
                            'results' => 0,
                            'deleted_at' => $deletedAt,
                            'date' => null,
                            'client_id' => $clientId,
                            'client_name' => $clientName,
                            'client_phone' => $clientPhone,
                            'transactions_count' => $carTransactions->count(),
                            'delete_description' => $description,
                        ];
                    }
                }
                
                $deletedCars = $deletedCarsData;
            }
            
            return response()->json([
                'success' => true,
                'deleted_cars' => $deletedCars,
                'total' => count($deletedCars),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب السيارات المحذوفة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
