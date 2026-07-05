<?php

namespace App\Http\Controllers;
use App\Http\Controllers\AccountingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use App\Models\Transfers;
use App\Models\User;
use App\Models\Car;
use App\Models\Wallet;
use App\Models\UserType;
use App\Models\ExpensesType;
use Illuminate\Support\Facades\DB;
use App\Models\Transactions;
use App\Models\Expenses;
use App\Models\CarExpenses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\SystemConfig;
use App\Services\AccountingCacheService;


use Carbon\Carbon;

use Inertia\Inertia;

class CarExpensesController extends Controller
{
    public const CAR_HAVE_EXPENSES_LINKED = 4;

    protected $accountingController;
    protected $userClient;
    protected $accounting;

    public function __construct(AccountingController $accountingController, AccountingCacheService $accounting)
    {
    $this->accountingController = $accountingController;
    $this->accounting = $accounting;
    $this->userClient =  UserType::where('name', 'client')->first()->id;
    }
    public function searchVINs(Request $request)
    {
            $vins = $request->input('vins');
            $results = [];
            $noResultsVINs = []; // مصفوفة جديدة للأرقام التي ليس لها نتائج

            foreach ($vins as $vin) {
                // استخراج الأرقام بعد آخر حرف باستخدام التعبير النمطي
                preg_match('/[A-Z]+(\d+)$/i', $vin, $matches);

                // التحقق من وجود أرقام بعد آخر حرف
                $lastNumbers = $matches[1] ?? null;

                if ($lastNumbers) {
                    // البحث التدريجي عن النتائج
                    $cars = [];
                    while (strlen($lastNumbers) >= 5) {
                        $cars = Car::with('client')->where('vin', 'like', '%' . $lastNumbers)->get();

                        if ($cars->isNotEmpty()) {
                            break; // إذا تم العثور على نتائج، توقف عن البحث
                        }

                        // حذف رقم واحد من اليمين
                        $lastNumbers = substr($lastNumbers, 0, -1);
                    }

                    // إضافة النتائج مع رقم الشاصي
                    $results[] = [
                        'vin' => $vin,
                        'message' => $cars->isEmpty() ? 'لا توجد نتائج لهذا الرقم' : 'تم العثور على نتائج',
                        'cars' => $cars
                    ];

                    // إضافة VIN إلى قائمة الأرقام التي ليس لها نتائج
                    if ($cars->isEmpty()) {
                        $noResultsVINs[] = $vin;
                    }
                } else {
                    // إذا لم يتم العثور على أرقام بعد آخر حرف، أضف رقم الشاصي مع رسالة
                    $results[] = [
                        'vin' => $vin,
                        'message' => 'لا توجد أرقام في رقم الشاصي',
                        'cars' => [] // مصفوفة فارغة للسيارات
                    ];

                    // إضافة VIN إلى قائمة الأرقام التي ليس لها نتائج
                    $noResultsVINs[] = $vin;
                }
            }
            return response()->json([
                'results' => $results,
                'noResultsVINs' => $noResultsVINs,  // إعادة المصفوفة للأرقام التي ليس لها نتائج
            ]);
    }
    public function index(Request $request)
    {
        $owner_id=Auth::user()->owner_id;
        $client = User::where('type_id', $this->userClient)->where('owner_id',$owner_id)->get();
        return Inertia::render('CarExpenses/index', ['client'=>$client ]);   
    }
    public function car_check(Request $request)
    {
        $owner_id=Auth::user()->owner_id;
        $client = User::where('type_id', $this->userClient)->where('owner_id',$owner_id)->get();
        return Inertia::render('CarCheck/index', ['client'=>$client ]);   
    }
    public function addCarFavorite(Request $request)
    {
        $car = Car::find($request->carId);
        if($car){
          $car_edited =  $car->update(['car_have_expenses'=>1]);
        }else{
            return Response::json('car not found', 200);    
        }
        return Response::json($car, 200);    
    }
    public function confirmExpensesCar(Request $request){
        $user=Auth::user();
        $expenses = new CarExpenses;
        $expenses->user_id = $user->id;
        $expenses->owner_id = $user->owner_id;
        $expenses->car_id = $request->id;
        $expenses->created =Carbon::now()->format('Y-m-d');
        $expenses->note = $request->amountNote;
        $expenses->amount_dinar = (float) ($request->amountDinar ?? 0);
        $expenses->amount_dollar = (float) ($request->amountDollar ?? 0);
        $expenses->save();


        return Response::json($expenses, 200);    


        
    }
    public function delExpensesCar(Request $request){
        try {
            $expenses = CarExpenses::findOrFail($request->id);
            $expenses->delete();
    
            return response()->json('ok', 200);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the record is not found
            return response()->json(['error' => 'Expense not found'], 404);
        } catch (\Exception $e) {
            // Handle other exceptions that might occur during deletion
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function confirmArchiveCar(Request $request){
        try {
            $car = Car::findOrFail($request->id);
            $car_edited =  $car->update(['car_have_expenses'=>2]);
            return response()->json('ok', 200);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the record is not found
            return response()->json(['error' => 'Expense not found'], 404);
        } catch (\Exception $e) {
            // Handle other exceptions that might occur during deletion
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function confirmArchiveCarBack(Request $request){
        try {
            $car = Car::findOrFail($request->id);
            $car_edited =  $car->update(['car_have_expenses'=>1]);
            return response()->json('ok', 200);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the record is not found
            return response()->json(['error' => 'Expense not found'], 404);
        } catch (\Exception $e) {
            // Handle other exceptions that might occur during deletion
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function confirmArchiveAllCars(Request $request)
    {
        try {
            $owner_id = Auth::user()->owner_id;
            $user_id = $request->get('user_id') ?? '';
            $q = $request->get('q') ?? '';

            $query = Car::where('owner_id', $owner_id)->where('car_have_expenses', 1);

            if ($user_id) {
                $query->where('client_id', $user_id);
            }

            if ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('car_number', 'LIKE', '%' . $q . '%')
                        ->orWhere('vin', 'LIKE', '%' . $q . '%')
                        ->orWhere('car_type', 'LIKE', '%' . $q . '%')
                        ->orWhereHas('client', function ($clientQuery) use ($q) {
                            $clientQuery->where('name', 'LIKE', '%' . $q . '%');
                        });
                });
            }

            $count = $query->update(['car_have_expenses' => 2]);

            return response()->json(['ok' => true, 'count' => $count], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function confirmLinkArchiveCar(Request $request)
    {
        try {
            $car = Car::with('carexpenses')->findOrFail($request->id);
            $owner_id = Auth::user()->owner_id;

            if ((int) $car->owner_id !== (int) $owner_id) {
                return response()->json(['error' => 'غير مصرح'], 403);
            }

            if ((int) $car->car_have_expenses !== 2) {
                return response()->json(['error' => 'السيارة ليست في الأرشيف أو تم ربطها مسبقاً'], 422);
            }

            $exchangeRate = (float) ($request->exchangeRate ?? 0);
            if ($exchangeRate <= 0) {
                return response()->json(['error' => 'سعر الصرف مطلوب'], 422);
            }
            if (!self::isValidLinkExchangeRate($exchangeRate)) {
                return response()->json(['error' => 'يجب أن يكون سعر الصرف 6 أرقام'], 422);
            }

            $calc_rate = $exchangeRate;
            if ($calc_rate > 9999) {
                $calc_rate = $calc_rate / 100;
            }

            $unlinkedExpenses = $car->carexpenses->filter(function ($expense) {
                return !str_contains($expense->note ?? '', '[مربوط]');
            });

            $totalDollarPaid = $unlinkedExpenses->sum('amount_dollar');
            $totalDinarPaid = $unlinkedExpenses->sum('amount_dinar');
            $dollarFromDinar = (int) ($totalDinarPaid / $calc_rate);
            $expenseToAdd = (int) ($totalDollarPaid + $dollarFromDinar);

            if ($expenseToAdd <= 0) {
                return response()->json(['error' => 'لا توجد مصاريف غير مربوطة للربط'], 422);
            }

            $oldTotal = (int) ($car->total ?? 0);
            $oldTotalS = (int) ($car->total_s ?? 0);
            $newExpenses = (int) (($car->expenses ?? 0) + $expenseToAdd);
            $newExpensesS = (int) (($car->expenses_s ?? 0) + $expenseToAdd);

            $dolar_price_input = $car->dolar_price ?? 1;
            $car_calc_rate = $dolar_price_input;
            if ($car_calc_rate == 0) {
                $car_calc_rate = 1;
            } elseif ($car_calc_rate > 9999) {
                $car_calc_rate = $car_calc_rate / 100;
            }

            $newTotal = (int) (
                ($car->checkout ?? 0)
                + ($car->shipping_dolar ?? 0)
                + ($car->coc_dolar ?? 0)
                + (int) (($car->dinar ?? 0) / $car_calc_rate)
                + (int) (($car->land_shipping_dinar ?? 0) / $car_calc_rate)
                + $newExpenses
                + ($car->land_shipping ?? 0)
            );

            $dolar_price_s_input = $car->dolar_price_s ?? 1;
            $car_calc_rate_s = $dolar_price_s_input;
            if ($car_calc_rate_s == 0) {
                $car_calc_rate_s = 1;
            } elseif ($car_calc_rate_s > 9999) {
                $car_calc_rate_s = $car_calc_rate_s / 100;
            }

            $newTotalS = (int) (
                ($car->checkout_s ?? 0)
                + ($car->shipping_dolar_s ?? 0)
                + ($car->coc_dolar_s ?? 0)
                + (int) (($car->dinar_s ?? 0) / $car_calc_rate_s)
                + (int) (($car->land_shipping_dinar_s ?? 0) / $car_calc_rate_s)
                + $newExpensesS
                + ($car->land_shipping_s ?? 0)
            );

            $desc = 'ربط مصاريف السيارة ' . $car->car_type . ' ' . $car->vin;

            if ($newTotal > $oldTotal) {
                $this->accountingController->decreaseWallet(
                    ($newTotal - $oldTotal),
                    $desc,
                    $this->accounting->mainAccount()->id,
                    $car->id,
                    'App\Models\Car'
                );
            }

            if ($newTotalS > $oldTotalS) {
                $this->accountingController->increaseWallet(
                    ($newTotalS - $oldTotalS),
                    $desc,
                    $car->client_id,
                    $car->id,
                    'App\Models\User'
                );
            }

            $isFirstLinkedExpense = true;
            foreach ($unlinkedExpenses as $expense) {
                $baseNote = preg_replace('/\s*\[مربوط@\d+\]/u', '', $expense->note ?? '');
                $baseNote = str_replace(' [مربوط]', '', $baseNote);
                $baseNote = trim($baseNote);
                $linkSuffix = $isFirstLinkedExpense
                    ? ' [مربوط@' . (int) $exchangeRate . ']'
                    : ' [مربوط]';
                $isFirstLinkedExpense = false;
                $expense->update([
                    'note' => trim($baseNote . $linkSuffix),
                ]);
            }

            $car->update([
                'expenses' => $newExpenses,
                'expenses_s' => $newExpensesS,
                'total' => $newTotal,
                'total_s' => $newTotalS,
                'profit' => $newTotalS - $newTotal,
                'car_have_expenses' => self::CAR_HAVE_EXPENSES_LINKED,
            ]);

            return response()->json([
                'ok' => true,
                'expense_added' => $expenseToAdd,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'السيارة غير موجودة'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function getCarRegistrationDetails(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $car = Car::with(['carexpenses.user:id,name'])
            ->where('owner_id', $owner_id)
            ->findOrFail($request->car_id);

        $expenses = $car->carexpenses;
        $totalDollar = $expenses->sum('amount_dollar');
        $totalDinar = $expenses->sum('amount_dinar');

        return response()->json([
            'car' => [
                'id' => $car->id,
                'car_type' => $car->car_type,
                'vin' => $car->vin,
                'car_number' => $car->car_number,
                'car_have_expenses' => $car->car_have_expenses,
            ],
            'expenses' => $expenses,
            'total_dollar' => $totalDollar,
            'total_dinar' => $totalDinar,
            'has_registration' => $expenses->isNotEmpty(),
            'link_exchange_rate' => self::parseLinkExchangeRate($expenses),
            'is_linked' => self::isCarLinked($car),
        ], 200);
    }

    public function confirmUnlinkArchiveCar(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $car = Car::with('carexpenses')->findOrFail($request->id);
                $owner_id = Auth::user()->owner_id;

                if ((int) $car->owner_id !== (int) $owner_id) {
                    return response()->json(['error' => 'غير مصرح'], 403);
                }

                if (!self::isCarLinked($car)) {
                    return response()->json(['error' => 'السيارة غير مربوطة'], 422);
                }

                $linkedExpenses = $car->carexpenses->filter(function ($expense) {
                    return str_contains($expense->note ?? '', '[مربوط]');
                });

                if ($linkedExpenses->isEmpty()) {
                    return response()->json(['error' => 'لا توجد مصاريف مربوطة لإلغاء الربط'], 422);
                }

                $exchangeRate = self::parseLinkExchangeRate($linkedExpenses);
                if (!$exchangeRate || $exchangeRate <= 0) {
                    return response()->json(['error' => 'سعر الصرف المستخدم في الربط غير متوفر'], 422);
                }

                $calc_rate = $exchangeRate;
                if ($calc_rate > 9999) {
                    $calc_rate = $calc_rate / 100;
                }

                $totalDollarPaid = $linkedExpenses->sum('amount_dollar');
                $totalDinarPaid = $linkedExpenses->sum('amount_dinar');
                $dollarFromDinar = (int) ($totalDinarPaid / $calc_rate);
                $expenseToRemove = (int) ($totalDollarPaid + $dollarFromDinar);

                if ($expenseToRemove <= 0) {
                    return response()->json(['error' => 'لا توجد مصاريف صالحة لإلغاء الربط'], 422);
                }

                $newExpenses = (int) ($car->expenses ?? 0) - $expenseToRemove;
                $newExpensesS = (int) ($car->expenses_s ?? 0) - $expenseToRemove;

                if ($newExpenses < 0 || $newExpensesS < 0) {
                    return response()->json(['error' => 'تعذر التراجع: المصاريف المسجلة أقل من المربوطة'], 422);
                }

                $oldTotal = (int) ($car->total ?? 0);
                $oldTotalS = (int) ($car->total_s ?? 0);

                $dolar_price_input = $car->dolar_price ?? 1;
                $car_calc_rate = $dolar_price_input;
                if ($car_calc_rate == 0) {
                    $car_calc_rate = 1;
                } elseif ($car_calc_rate > 9999) {
                    $car_calc_rate = $car_calc_rate / 100;
                }

                $newTotal = (int) (
                    ($car->checkout ?? 0)
                    + ($car->shipping_dolar ?? 0)
                    + ($car->coc_dolar ?? 0)
                    + (int) (($car->dinar ?? 0) / $car_calc_rate)
                    + (int) (($car->land_shipping_dinar ?? 0) / $car_calc_rate)
                    + $newExpenses
                    + ($car->land_shipping ?? 0)
                );

                $dolar_price_s_input = $car->dolar_price_s ?? 1;
                $car_calc_rate_s = $dolar_price_s_input;
                if ($car_calc_rate_s == 0) {
                    $car_calc_rate_s = 1;
                } elseif ($car_calc_rate_s > 9999) {
                    $car_calc_rate_s = $car_calc_rate_s / 100;
                }

                $newTotalS = (int) (
                    ($car->checkout_s ?? 0)
                    + ($car->shipping_dolar_s ?? 0)
                    + ($car->coc_dolar_s ?? 0)
                    + (int) (($car->dinar_s ?? 0) / $car_calc_rate_s)
                    + (int) (($car->land_shipping_dinar_s ?? 0) / $car_calc_rate_s)
                    + $newExpensesS
                    + ($car->land_shipping_s ?? 0)
                );

                $desc = 'إلغاء ربط مصاريف السيارة ' . $car->car_type . ' ' . $car->vin;

                if ($oldTotal > $newTotal) {
                    $this->accountingController->increaseWallet(
                        ($oldTotal - $newTotal),
                        $desc,
                        $this->accounting->mainAccount()->id,
                        $car->id,
                        'App\Models\Car'
                    );
                }

                if ($oldTotalS > $newTotalS) {
                    $this->accountingController->decreaseWallet(
                        ($oldTotalS - $newTotalS),
                        $desc,
                        $car->client_id,
                        $car->id,
                        'App\Models\User'
                    );
                }

                foreach ($linkedExpenses as $expense) {
                    $cleanNote = preg_replace('/\s*\[مربوط@\d+\]/u', '', $expense->note ?? '');
                    $cleanNote = str_replace(' [مربوط]', '', $cleanNote);
                    $expense->update(['note' => trim($cleanNote)]);
                }

                $car->update([
                    'expenses' => $newExpenses,
                    'expenses_s' => $newExpensesS,
                    'total' => $newTotal,
                    'total_s' => $newTotalS,
                    'profit' => $newTotalS - $newTotal,
                    'car_have_expenses' => 2,
                ]);

                return response()->json([
                    'ok' => true,
                    'expense_removed' => $expenseToRemove,
                ], 200);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'السيارة غير موجودة'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public static function parseLinkExchangeRate($expenses): ?float
    {
        $rates = [];

        foreach ($expenses as $expense) {
            if (preg_match_all('/\[مربوط@(\d+)\]/u', $expense->note ?? '', $matches)) {
                foreach ($matches[1] as $rawRate) {
                    $rates[] = (float) $rawRate;
                }
            }
        }

        if (empty($rates)) {
            return null;
        }

        // Use the last tag in note order (most recent link rate).
        return (float) end($rates);
    }

    public static function isValidLinkExchangeRate($exchangeRate): bool
    {
        if (!is_numeric($exchangeRate) || (float) $exchangeRate <= 0) {
            return false;
        }

        $rate = (float) $exchangeRate;
        if ($rate != (int) $rate) {
            return false;
        }

        return strlen((string) (int) $rate) === 6;
    }

    public static function applyLinkedCarsFilter($query)
    {
        return $query->where(function ($q) {
            $q->where('car_have_expenses', self::CAR_HAVE_EXPENSES_LINKED)
                ->orWhere(function ($q2) {
                    $q2->where('car_have_expenses', 0)
                        ->whereHas('carexpenses', function ($e) {
                            $e->where('note', 'like', '%[مربوط]%');
                        });
                });
        });
    }

    public static function isCarLinked(Car $car): bool
    {
        if ((int) $car->car_have_expenses === self::CAR_HAVE_EXPENSES_LINKED) {
            return true;
        }

        if ((int) $car->car_have_expenses === 0) {
            if ($car->relationLoaded('carexpenses')) {
                return $car->carexpenses->contains(function ($expense) {
                    return str_contains($expense->note ?? '', '[مربوط]');
                });
            }

            return CarExpenses::where('car_id', $car->id)
                ->where('note', 'like', '%[مربوط]%')
                ->exists();
        }

        return false;
    }

    public static function enrichCarsWithLinkRates($cars)
    {
        if (!$cars || (is_countable($cars) && count($cars) === 0)) {
            return $cars;
        }

        $collection = $cars instanceof \Illuminate\Support\Collection ? $cars : collect($cars);
        $carIds = $collection->pluck('id')->filter()->values();

        if ($carIds->isEmpty()) {
            return $cars;
        }

        $rateByCarId = CarExpenses::whereIn('car_id', $carIds)
            ->where('note', 'like', '%[مربوط@%')
            ->get(['car_id', 'note'])
            ->mapWithKeys(function ($expense) {
                $rate = self::parseLinkExchangeRate(collect([$expense]));
                return $rate ? [$expense->car_id => $rate] : [];
            });

        return $collection->map(function ($car) use ($rateByCarId) {
            $car->link_exchange_rate = $rateByCarId[$car->id] ?? null;
            return $car;
        });
    }
    public function confirmDelCarFav(Request $request){
        $car = Car::find($request->id);
        if($car){
          $car_edited =  $car->update(['car_have_expenses'=>3]);
        }else{
            return Response::json('car not found', 200);    
        }
        return Response::json($car, 200);    
    }
    public function getIndexExpensesPrint(Request $request){
        $data = Car::with('contract', 'exitcar','client','carexpenses.user')->where('id', $request->car_id)->first();
        if($data){

            $config=SystemConfig::first();
    
            return view('receiptCarsExpensesTotal',compact('data','config'));
        }else{
            return Response::json('car not found', 200);    
        }
        return Response::json($car, 200);    
    }
    public function getIndexExpensesSummaryPrint(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $user_id = $request->get('user_id') ?? '';
        $q = $request->get('q') ?? '';

        $query = Car::with(['client:id,name', 'carexpenses'])
            ->where('owner_id', $owner_id)
            ->where('car_have_expenses', 1);

        if ($user_id) {
            $query->where('client_id', $user_id);
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('car_number', 'LIKE', '%' . $q . '%')
                    ->orWhere('vin', 'LIKE', '%' . $q . '%')
                    ->orWhere('car_type', 'LIKE', '%' . $q . '%')
                    ->orWhereHas('client', function ($clientQuery) use ($q) {
                        $clientQuery->where('name', 'LIKE', '%' . $q . '%');
                    });
            });
        }

        $cars = $query->orderBy('no', 'DESC')->get();
        $totalDollar = 0;
        $totalDinar = 0;
        $rows = [];

        foreach ($cars as $car) {
            $sumDollar = $car->carexpenses->sum('amount_dollar');
            $sumDinar = $car->carexpenses->sum('amount_dinar');
            $totalDollar += $sumDollar;
            $totalDinar += $sumDinar;
            $rows[] = [
                'no' => $car->no,
                'car_type' => $car->car_type,
                'vin' => $car->vin,
                'car_number' => $car->car_number,
                'client' => $car->client,
                'sum_dollar' => $sumDollar,
                'sum_dinar' => $sumDinar,
            ];
        }

        $config = SystemConfig::first();
        $filterLabel = trim(($user_id ? 'مالك محدد' : 'كل الملاك') . ($q ? ' | بحث: ' . $q : ''));

        return view('receiptCarsExpensesSummary', [
            'cars' => $rows,
            'totalDollar' => $totalDollar,
            'totalDinar' => $totalDinar,
            'config' => $config,
            'filterLabel' => $filterLabel,
        ]);
    }
}