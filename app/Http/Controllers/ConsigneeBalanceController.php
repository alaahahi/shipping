<?php

namespace App\Http\Controllers;

use App\Models\TripCar;
use App\Models\TripCompany;
use App\Models\ConsigneePayment;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Carbon\Carbon;

class ConsigneeBalanceController extends Controller
{
    /**
     * عرض صفحة أرصدة الزبائن
     */
    public function index()
    {
        return inertia('ConsigneeBalances/Index');
    }

    /**
     * عرض تفاصيل شركة شحن معينة
     */
    public function show($companyId)
    {
        $owner_id = Auth::user()->owner_id;

        try {
            // جلب بيانات الشركة
            $company = User::where('id', $companyId)
                ->where('owner_id', $owner_id)
                ->firstOrFail();

            // جلب جميع TripCompany لهذه الشركة
            $tripCompanies = TripCompany::where('company_id', $companyId)
                ->where('owner_id', $owner_id)
                ->with(['trip', 'company'])
                ->get();

            // جلب جميع السيارات المرتبطة بهذه الشركة
            $tripCompanyIds = $tripCompanies->pluck('id');
            $tripCars = TripCar::where('owner_id', $owner_id)
                ->whereIn('trip_company_id', $tripCompanyIds)
                ->with(['tripCompany.trip', 'tripCompany.company', 'car', 'consignee'])
                ->orderBy('trip_company_id')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // تجميع السيارات حسب الرحلة (trip_company_id)
            $carsByTrip = [];
            foreach ($tripCars as $car) {
                $tripCompanyId = $car->trip_company_id;
                $tripId = $car->tripCompany->trip_id ?? 0;
                $tripName = $car->tripCompany->trip 
                    ? ($car->tripCompany->trip->ship_name . ' - ' . $car->tripCompany->trip->voy_no)
                    : 'رحلة غير معروفة';
                
                $key = $tripId . '_' . $tripName;
                
                if (!isset($carsByTrip[$key])) {
                    $carsByTrip[$key] = [
                        'trip_id' => $tripId,
                        'trip_name' => $tripName,
                        'trip_company_id' => $tripCompanyId,
                        'cars' => [],
                    ];
                }
                
                $carsByTrip[$key]['cars'][] = $car;
            }
            
            // تحويل إلى array مرقم
            $carsByTripArray = array_values($carsByTrip);

            // حساب إجمالي المبلغ المستحق
            $totalShippingCost = 0;
            $totalCars = 0;
            $trips = [];

            foreach ($tripCars->groupBy('trip_company_id') as $tripCompanyId => $companyCars) {
                $tripCompany = TripCompany::find($tripCompanyId);
                if (!$tripCompany || !$tripCompany->shipping_price_per_car) continue;

                $carsCount = $companyCars->count();
                $shippingCost = $tripCompany->shipping_price_per_car * $carsCount;
                
                $totalShippingCost += $shippingCost;
                $totalCars += $carsCount;

                $trips[] = [
                    'trip_id' => $tripCompany->trip_id,
                    'trip_name' => $tripCompany->trip ? $tripCompany->trip->ship_name . ' - ' . $tripCompany->trip->voy_no : 'رحلة غير معروفة',
                    'company_name' => $tripCompany->company ? $tripCompany->company->name : 'شركة غير معروفة',
                    'cars_count' => $carsCount,
                    'price_per_car' => $tripCompany->shipping_price_per_car,
                    'total' => $shippingCost,
                ];
            }

            // جلب جميع الدفعات للزبائن الذين لديهم سيارات في هذه الشركة
            $consigneeIds = $tripCars->pluck('consignee_id')->unique()->filter();
            $allPayments = ConsigneePayment::whereIn('consignee_id', $consigneeIds)
                ->where('owner_id', $owner_id)
                ->with('trip')
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // حساب نسبة الدفعات لهذه الشركة
            $totalPaidDollar = 0;
            $totalPaidDinar = 0;
            $payments = collect();
            
            // جلب إجمالي سيارات كل زبون
            $allTripCars = TripCar::where('owner_id', $owner_id)
                ->whereIn('consignee_id', $consigneeIds)
                ->get();

            foreach ($consigneeIds as $consigneeId) {
                $consigneeCarsInCompany = $tripCars->where('consignee_id', $consigneeId);
                $consigneeCarsCount = $consigneeCarsInCompany->count();
                $consigneeTotalCars = $allTripCars->where('consignee_id', $consigneeId)->count();
                
                if ($consigneeTotalCars > 0) {
                    $ratio = $consigneeCarsCount / $consigneeTotalCars;
                    
                    $consigneePayments = $allPayments->where('consignee_id', $consigneeId);
                    $consigneePaidDollar = $consigneePayments->where('currency', 'dollar')->sum('amount');
                    $consigneePaidDinar = $consigneePayments->where('currency', 'dinar')->sum('amount');
                    
                    $totalPaidDollar += $consigneePaidDollar * $ratio;
                    $totalPaidDinar += $consigneePaidDinar * $ratio;
                    
                    // إضافة الدفعات مع نسبة
                    foreach ($consigneePayments as $payment) {
                        $payment->adjusted_amount = $payment->amount * $ratio;
                        $payments->push($payment);
                    }
                }
            }
            
            $balanceDollar = $totalShippingCost - $totalPaidDollar;
            $balanceDinar = 0 - $totalPaidDinar;

            return inertia('ConsigneeBalances/Show', [
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'phone' => $company->phone ?? '',
                ],
                'carsByTrip' => $carsByTripArray,
                'payments' => $payments->values(),
                'trips' => $trips,
                'stats' => [
                    'total_cars' => $totalCars,
                    'total_shipping_cost' => $totalShippingCost,
                    'total_paid_dollar' => $totalPaidDollar,
                    'total_paid_dinar' => $totalPaidDinar,
                    'balance_dollar' => $balanceDollar,
                    'balance_dinar' => $balanceDinar,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing company balance: ' . $e->getMessage());
            abort(404);
        }
    }

    /**
     * جلب جميع أرصدة الشركات
     */
    public function getBalances(Request $request)
    {
        $owner_id = Auth::user()->owner_id;

        try {
            // جلب جميع السيارات مع الشركات والرحلات
            $tripCars = TripCar::where('owner_id', $owner_id)
                ->whereNotNull('consignee_id')
                ->with(['consignee', 'tripCompany.trip', 'tripCompany.company'])
                ->get();

            // حساب الأرصدة لكل شركة شحن من سعر الشحن × عدد السيارات
            $balances = [];
            
            foreach ($tripCars->groupBy('trip_company_id') as $tripCompanyId => $cars) {
                $tripCompany = TripCompany::find($tripCompanyId);
                if (!$tripCompany) continue;
                
                $company = $tripCompany->company;
                if (!$company) continue;

                // حساب إجمالي المبلغ المستحق من سعر الشحن
                if (!$tripCompany->shipping_price_per_car) continue;

                $carsCount = $cars->count();
                $shippingCost = $tripCompany->shipping_price_per_car * $carsCount;
                
                // جمع جميع الدفعات من الزبائن الذين لديهم سيارات في هذه الشركة
                $consigneeIds = $cars->pluck('consignee_id')->unique()->filter()->values();
                $payments = ConsigneePayment::whereIn('consignee_id', $consigneeIds)
                    ->where('owner_id', $owner_id)
                    ->get();

                // حساب نسبة الدفعات لهذه الشركة (نقسم الدفعات حسب عدد السيارات لكل زبون)
                $totalPaidDollar = 0;
                $totalPaidDinar = 0;
                
                foreach ($consigneeIds as $consigneeId) {
                    $consigneeCars = $cars->where('consignee_id', $consigneeId);
                    $consigneeCarsCount = $consigneeCars->count();
                    $consigneeTotalCars = $tripCars->where('consignee_id', $consigneeId)->count();
                    
                    // حساب نسبة السيارات لهذه الشركة من إجمالي سيارات الزبون
                    if ($consigneeTotalCars > 0) {
                        $ratio = $consigneeCarsCount / $consigneeTotalCars;
                        
                        $consigneePayments = $payments->where('consignee_id', $consigneeId);
                        $consigneePaidDollar = $consigneePayments->where('currency', 'dollar')->sum('amount');
                        $consigneePaidDinar = $consigneePayments->where('currency', 'dinar')->sum('amount');
                        
                        $totalPaidDollar += $consigneePaidDollar * $ratio;
                        $totalPaidDinar += $consigneePaidDinar * $ratio;
                    }
                }

                // الرصيد المستحق = إجمالي المبلغ - الدفعات
                $balanceDollar = $shippingCost - $totalPaidDollar;
                $balanceDinar = 0 - $totalPaidDinar;

                // تجميع الرحلات
                $trips = [
                    [
                        'trip_id' => $tripCompany->trip_id,
                        'trip_name' => $tripCompany->trip ? $tripCompany->trip->ship_name . ' - ' . $tripCompany->trip->voy_no : 'رحلة غير معروفة',
                        'cars_count' => $carsCount,
                        'price_per_car' => $tripCompany->shipping_price_per_car,
                        'total' => $shippingCost,
                    ]
                ];

                $balances[] = [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'company_phone' => $company->phone ?? '',
                    'consignee_ids' => $consigneeIds->toArray(), // قائمة بجميع الزبائن المرتبطين بهذه الشركة
                    'primary_consignee_id' => $consigneeIds->first(), // أول زبون للاستخدام الافتراضي
                    'total_cars' => $carsCount,
                    'total_shipping_cost' => $shippingCost,
                    'total_shipping_cost_dinar' => 0,
                    'total_paid_dollar' => $totalPaidDollar,
                    'total_paid_dinar' => $totalPaidDinar,
                    'balance_dollar' => $balanceDollar,
                    'balance_dinar' => $balanceDinar,
                    'trips' => $trips,
                    'payments_count' => $payments->whereIn('consignee_id', $consigneeIds)->count(),
                ];
            }

            // ترتيب حسب الرصيد المستحق (من الأعلى للأقل)
            usort($balances, function($a, $b) {
                return $b['balance_dollar'] <=> $a['balance_dollar'];
            });

            return Response::json([
                'success' => true,
                'balances' => $balances,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting company balances: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الأرصدة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب دفعات زبون معين
     */
    public function getPayments($consigneeId)
    {
        $owner_id = Auth::user()->owner_id;

        try {
            $payments = ConsigneePayment::where('consignee_id', $consigneeId)
                ->where('owner_id', $owner_id)
                ->with(['trip', 'consignee'])
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return Response::json([
                'success' => true,
                'payments' => $payments,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting payments: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الدفعات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * إضافة دفعة جديدة
     */
    public function addPayment(Request $request)
    {
        $validated = $request->validate([
            'consignee_id' => 'required|exists:users,id',
            'trip_id' => 'nullable|exists:trips,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:dollar,dinar',
            'notes' => 'nullable|string|max:1000',
            'payment_date' => 'required|date',
        ]);

        $owner_id = Auth::user()->owner_id;

        // التحقق من أن الزبون موجود ومملوك للمستخدم
        $consignee = User::where('id', $validated['consignee_id'])
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // إنشاء رقم وصل تلقائي
            $receiptNumber = 'REC-' . date('Ymd') . '-' . str_pad(ConsigneePayment::where('owner_id', $owner_id)->count() + 1, 4, '0', STR_PAD_LEFT);

            $payment = ConsigneePayment::create([
                'consignee_id' => $validated['consignee_id'],
                'trip_id' => $validated['trip_id'] ?? null,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'notes' => $validated['notes'] ?? null,
                'payment_date' => $validated['payment_date'],
                'receipt_number' => $receiptNumber,
                'owner_id' => $owner_id,
            ]);

            // تحديث Wallet للزبون (إن وجد)
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $consignee->id],
                ['balance' => 0]
            );

            // تحديث الرصيد حسب العملة
            if ($validated['currency'] === 'dollar') {
                $wallet->balance += $validated['amount'];
            }
            $wallet->save();

            DB::commit();

            return Response::json([
                'success' => true,
                'message' => 'تم إضافة الدفعة بنجاح',
                'payment' => $payment->load(['consignee', 'trip']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding payment: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة الدفعة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * حذف دفعة
     */
    public function deletePayment($paymentId)
    {
        $owner_id = Auth::user()->owner_id;

        try {
            $payment = ConsigneePayment::where('id', $paymentId)
                ->where('owner_id', $owner_id)
                ->firstOrFail();

            DB::beginTransaction();

            // تحديث Wallet
            if ($payment->consignee && $payment->consignee->wallet) {
                if ($payment->currency === 'dollar') {
                    $payment->consignee->wallet->balance -= $payment->amount;
                    $payment->consignee->wallet->save();
                }
            }

            $payment->delete();

            DB::commit();

            return Response::json([
                'success' => true,
                'message' => 'تم حذف الدفعة بنجاح',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting payment: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الدفعة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * طباعة وصل دفع
     */
    public function printReceipt($paymentId)
    {
        $owner_id = Auth::user()->owner_id;

        try {
            $payment = ConsigneePayment::where('id', $paymentId)
                ->where('owner_id', $owner_id)
                ->with(['consignee', 'trip'])
                ->firstOrFail();

            // هنا يمكن إضافة كود طباعة PDF
            // في الوقت الحالي نرجع البيانات
            return Response::json([
                'success' => true,
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            Log::error('Error printing receipt: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء طباعة الوصل: ' . $e->getMessage(),
            ], 500);
        }
    }
}