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

        $inWorkflow = self::isCarInRegistrationWorkflow($car);
        $expenses = $inWorkflow ? $car->carexpenses : collect();
        $totalDollar = $expenses->sum('amount_dollar');
        $totalDinar = $expenses->sum('amount_dinar');

        $expensesPayload = $expenses->map(function ($expense) {
            $parsed = self::parseRegistrationNote($expense->note ?? '');
            $row = $expense->toArray();
            $row['line_items'] = array_map(function ($item) {
                return [
                    'index' => $item['index'],
                    'label' => $item['raw'],
                    'type' => $item['type'],
                    'currency' => $item['currency'],
                    'amount' => $item['amount'],
                ];
            }, $parsed['items']);

            return $row;
        });

        return response()->json([
            'car' => [
                'id' => $car->id,
                'car_type' => $car->car_type,
                'vin' => $car->vin,
                'car_number' => $car->car_number,
                'car_have_expenses' => $car->car_have_expenses,
            ],
            'expenses' => $expensesPayload,
            'total_dollar' => $totalDollar,
            'total_dinar' => $totalDinar,
            'has_registration' => $inWorkflow && $expenses->isNotEmpty(),
            'link_exchange_rate' => $inWorkflow ? self::parseLinkExchangeRate($expenses) : null,
            'is_linked' => self::isCarLinked($car),
            'can_edit' => $inWorkflow,
        ], 200);
    }

    public function deleteRegistrationExpenseLine(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $expense = CarExpenses::findOrFail($request->expense_id);
                $car = Car::with('carexpenses')->findOrFail($expense->car_id);
                $owner_id = Auth::user()->owner_id;

                if ((int) $expense->owner_id !== (int) $owner_id || (int) $car->owner_id !== (int) $owner_id) {
                    return response()->json(['error' => 'غير مصرح'], 403);
                }

                if (!self::isCarInRegistrationWorkflow($car)) {
                    return response()->json(['error' => 'السيارة ليست ضمن تسجيل المصاريف'], 422);
                }

                $lineIndex = (int) $request->line_index;
                $parsed = self::parseRegistrationNote($expense->note ?? '');

                if (!isset($parsed['items'][$lineIndex])) {
                    return response()->json(['error' => 'البند غير موجود'], 422);
                }

                $isLinked = self::isCarLinked($car);
                $linkRate = self::parseLinkExchangeRate($car->carexpenses);
                $hasTaggedNotes = $car->carexpenses->contains(function ($expense) {
                    return str_contains($expense->note ?? '', '[مربوط]');
                });
                $forceUntaggedLinked = $isLinked && !$hasTaggedNotes;
                $oldContribution = ($isLinked && $linkRate)
                    ? self::calcExpenseLinkDollars(
                        $expense,
                        $linkRate,
                        self::expenseCountsAsLinked($expense, $forceUntaggedLinked)
                    )
                    : 0;

                unset($parsed['items'][$lineIndex]);
                $remainingItems = array_values($parsed['items']);

                if (empty($remainingItems)) {
                    $expense->delete();
                } else {
                    $amounts = self::sumLineItemAmounts($remainingItems);
                    $expense->update([
                        'note' => self::rebuildRegistrationNote($parsed['user_prefix'], $remainingItems, $parsed['link_suffix']),
                        'amount_dollar' => $amounts['dollar'],
                        'amount_dinar' => $amounts['dinar'],
                    ]);
                    $expense->refresh();
                }

                if ($isLinked && $linkRate && $oldContribution > 0) {
                    $newContribution = $expense->exists
                        ? self::calcExpenseLinkDollars($expense, $linkRate, true)
                        : 0;
                    $delta = $newContribution - $oldContribution;
                    if ($delta !== 0) {
                        $desc = 'تعديل بند تسجيل السيارة ' . $car->car_type . ' ' . $car->vin;
                        $this->applyLinkedExpenseDelta($car->fresh(), $delta, $desc);
                    }
                }

                return response()->json(['ok' => true], 200);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'البند غير موجود'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function addRegistrationExpenseLine(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $owner_id = Auth::user()->owner_id;
                $car = Car::with('carexpenses')->findOrFail($request->car_id);

                if ((int) $car->owner_id !== (int) $owner_id) {
                    return response()->json(['error' => 'غير مصرح'], 403);
                }

                if (!self::isCarInRegistrationWorkflow($car)) {
                    return response()->json(['error' => 'السيارة ليست ضمن تسجيل المصاريف'], 422);
                }

                $currency = $request->currency === 'dollar' ? 'dollar' : 'dinar';
                $amount = (float) ($request->amount ?? 0);
                if ($amount <= 0) {
                    return response()->json(['error' => 'المبلغ مطلوب'], 422);
                }

                $itemType = $request->item_type === 'registration' ? 'registration' : 'repair';
                $itemNote = trim((string) ($request->item_note ?? ''));
                $createNew = (bool) $request->create_new;

                $isLinked = self::isCarLinked($car);
                $linkRate = self::parseLinkExchangeRate($car->carexpenses);
                $hasTaggedNotes = $car->carexpenses->contains(function ($expense) {
                    return str_contains($expense->note ?? '', '[مربوط]');
                });
                $forceUntaggedLinked = $isLinked && !$hasTaggedNotes;

                $linePart = self::formatRegistrationLineItem($itemType, $currency, $amount, $itemNote);
                $oldContribution = 0;

                if ($createNew || $car->carexpenses->isEmpty()) {
                    $linkSuffix = '';
                    if ($isLinked) {
                        $linkSuffix = $linkRate ? ' [مربوط]' : '';
                    }

                    $note = trim($linePart . $linkSuffix);
                    $expense = CarExpenses::create([
                        'user_id' => Auth::id(),
                        'owner_id' => $owner_id,
                        'car_id' => $car->id,
                        'created' => Carbon::now()->format('Y-m-d'),
                        'note' => $note,
                        'amount_dollar' => $currency === 'dollar' ? $amount : 0,
                        'amount_dinar' => $currency === 'dinar' ? $amount : 0,
                    ]);

                    if ($isLinked && $linkRate) {
                        $oldContribution = 0;
                        $newContribution = self::calcExpenseLinkDollars($expense, $linkRate, true);
                        if ($newContribution !== 0) {
                            $desc = 'إضافة مصروف تسجيل السيارة ' . $car->car_type . ' ' . $car->vin;
                            $this->applyLinkedExpenseDelta($car->fresh(), $newContribution, $desc);
                        }
                    }

                    return response()->json(['ok' => true, 'expense_id' => $expense->id], 200);
                }

                $expense = $request->filled('expense_id')
                    ? $car->carexpenses->firstWhere('id', (int) $request->expense_id)
                    : $car->carexpenses->sortByDesc('id')->first();

                if (!$expense) {
                    return response()->json(['error' => 'دفعة التسجيل غير موجودة'], 422);
                }

                if ($isLinked && $linkRate) {
                    $oldContribution = self::calcExpenseLinkDollars(
                        $expense,
                        $linkRate,
                        self::expenseCountsAsLinked($expense, $forceUntaggedLinked)
                    );
                }

                $parsed = self::parseRegistrationNote($expense->note ?? '');
                $newItem = self::parseRegistrationNotePart($linePart);
                $newItem['raw'] = $linePart;
                $parsed['items'][] = $newItem;
                $remainingItems = array_values(array_map(function ($item, $index) {
                    $item['index'] = $index;

                    return $item;
                }, $parsed['items'], array_keys($parsed['items'])));

                $amounts = self::sumLineItemAmounts($remainingItems);
                $expense->update([
                    'note' => self::rebuildRegistrationNote($parsed['user_prefix'], $remainingItems, $parsed['link_suffix']),
                    'amount_dollar' => $amounts['dollar'],
                    'amount_dinar' => $amounts['dinar'],
                ]);
                $expense->refresh();

                if ($isLinked && $linkRate) {
                    $newContribution = self::calcExpenseLinkDollars($expense, $linkRate, true);
                    $delta = $newContribution - $oldContribution;
                    if ($delta !== 0) {
                        $desc = 'إضافة بند تسجيل السيارة ' . $car->car_type . ' ' . $car->vin;
                        $this->applyLinkedExpenseDelta($car->fresh(), $delta, $desc);
                    }
                }

                return response()->json(['ok' => true, 'expense_id' => $expense->id], 200);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'السيارة غير موجودة'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function updateRegistrationExchangeRate(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $car = Car::with('carexpenses')->findOrFail($request->car_id);
                $owner_id = Auth::user()->owner_id;

                if ((int) $car->owner_id !== (int) $owner_id) {
                    return response()->json(['error' => 'غير مصرح'], 403);
                }

                if (!self::isCarInRegistrationWorkflow($car)) {
                    return response()->json(['error' => 'السيارة ليست ضمن تسجيل المصاريف'], 422);
                }

                $newRate = (float) ($request->exchangeRate ?? 0);
                if (!self::isValidLinkExchangeRate($newRate)) {
                    return response()->json(['error' => 'يجب أن يكون سعر الصرف 6 أرقام'], 422);
                }

                $isLinked = self::isCarLinked($car);
                $linkedExpenses = self::resolveLinkedRegistrationExpenses($car);

                if ($linkedExpenses->isEmpty()) {
                    return response()->json(['error' => 'لا يوجد ربط لتعديل سعر الصرف'], 422);
                }

                $hasTaggedNotes = $car->carexpenses->contains(function ($expense) {
                    return str_contains($expense->note ?? '', '[مربوط]');
                });
                $forceUntaggedLinked = $isLinked && !$hasTaggedNotes;

                $oldRate = self::parseLinkExchangeRate($car->carexpenses);
                if (!$oldRate && $request->filled('previousExchangeRate')) {
                    $previousRate = (float) $request->previousExchangeRate;
                    if (self::isValidLinkExchangeRate($previousRate)) {
                        $oldRate = $previousRate;
                    }
                }

                $oldContribution = 0;
                $newContribution = 0;

                if ($isLinked && $oldRate) {
                    foreach ($linkedExpenses as $expense) {
                        $countsAsLinked = self::expenseCountsAsLinked($expense, $forceUntaggedLinked);
                        $oldContribution += self::calcExpenseLinkDollars($expense, $oldRate, $countsAsLinked);
                    }
                }

                $isFirst = true;
                foreach ($linkedExpenses as $expense) {
                    $parsed = self::parseRegistrationNote($expense->note ?? '');
                    $linkSuffix = $isFirst
                        ? ' [مربوط@' . (int) $newRate . ']'
                        : ' [مربوط]';
                    $isFirst = false;
                    $expense->update([
                        'note' => trim(self::rebuildRegistrationNote(
                            $parsed['user_prefix'],
                            $parsed['items'],
                            $linkSuffix
                        )),
                    ]);
                }

                if ($isLinked) {
                    foreach ($linkedExpenses as $expense) {
                        $expense->refresh();
                        $countsAsLinked = self::expenseCountsAsLinked($expense, true);
                        $newContribution += self::calcExpenseLinkDollars($expense, $newRate, $countsAsLinked);
                    }
                    $delta = $newContribution - $oldContribution;
                    if ($delta !== 0) {
                        $desc = 'تعديل سعر صرف ربط السيارة ' . $car->car_type . ' ' . $car->vin;
                        $this->applyLinkedExpenseDelta($car->fresh(), $delta, $desc);
                    }
                }

                return response()->json([
                    'ok' => true,
                    'link_exchange_rate' => $newRate,
                ], 200);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'السيارة غير موجودة'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
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

                $manualRate = $request->filled('exchangeRate')
                    ? (float) $request->exchangeRate
                    : null;
                $rateResolution = self::resolveUnlinkExchangeRate(
                    $car,
                    $linkedExpenses,
                    $manualRate
                );

                if ($rateResolution['error']) {
                    return response()->json([
                        'error' => $rateResolution['error'],
                        'needs_exchange_rate' => $rateResolution['needs_exchange_rate'],
                    ], 422);
                }

                $exchangeRate = $rateResolution['rate'];

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
                    $rate = (float) $rawRate;
                    if (self::isValidLinkExchangeRate($rate)) {
                        $rates[] = $rate;
                    }
                }
            }
        }

        if (empty($rates)) {
            return null;
        }

        // Prefer the first valid 6-digit tag (original link rate on first linked expense).
        return (float) $rates[0];
    }

    public static function resolveUnlinkExchangeRate(Car $car, $linkedExpenses, ?float $manualRate = null): array
    {
        $storedRate = self::parseLinkExchangeRate($linkedExpenses);
        if (!$storedRate) {
            $storedRate = self::parseLinkExchangeRate($car->carexpenses);
        }

        if ($storedRate && $storedRate > 0) {
            return [
                'rate' => $storedRate,
                'error' => null,
                'needs_exchange_rate' => false,
            ];
        }

        if ($manualRate !== null && $manualRate > 0) {
            if (!self::isValidLinkExchangeRate($manualRate)) {
                return [
                    'rate' => null,
                    'error' => 'يجب أن يكون سعر الصرف 6 أرقام',
                    'needs_exchange_rate' => true,
                ];
            }

            return [
                'rate' => $manualRate,
                'error' => null,
                'needs_exchange_rate' => false,
            ];
        }

        return [
            'rate' => null,
            'error' => 'سعر الصرف المستخدم في الربط غير متوفر',
            'needs_exchange_rate' => true,
        ];
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

    public static function parseAmountFromNote(string $value): float
    {
        $clean = str_replace([',', ' '], '', trim($value));

        return is_numeric($clean) ? (float) $clean : 0;
    }

    public static function parseRegistrationNote(string $note): array
    {
        $linkSuffix = '';
        if (preg_match('/\[مربوط@\d+\]/u', $note, $rateMatch)) {
            $linkSuffix = ' ' . $rateMatch[0];
        } elseif (str_contains($note, '[مربوط]')) {
            $linkSuffix = ' [مربوط]';
        }

        $baseNote = preg_replace('/\s*\[مربوط@\d+\]/u', '', $note);
        $baseNote = str_replace(' [مربوط]', '', $baseNote);
        $baseNote = trim($baseNote);

        $userPrefix = '';
        $itemsPart = $baseNote;
        if (str_contains($baseNote, ' — ')) {
            [$userPrefix, $itemsPart] = explode(' — ', $baseNote, 2);
            $userPrefix = trim($userPrefix);
            $itemsPart = trim($itemsPart);
        }

        $parts = $itemsPart !== '' ? preg_split('/\s*\|\s*/', $itemsPart) : [];
        $items = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $parsed = self::parseRegistrationNotePart($part);
            $parsed['index'] = count($items);
            $parsed['raw'] = $part;
            $items[] = $parsed;
        }

        return [
            'user_prefix' => $userPrefix,
            'items' => $items,
            'link_suffix' => trim($linkSuffix),
        ];
    }

    public static function parseRegistrationNotePart(string $part): array
    {
        if (preg_match('/^تسجيل\s+(.+)\$$/u', $part, $matches)) {
            return [
                'type' => 'registration',
                'currency' => 'dollar',
                'amount' => self::parseAmountFromNote($matches[1]),
            ];
        }

        if (preg_match('/^تسجيل\s+(.+)\s*د$/u', $part, $matches)) {
            $amountParts = preg_split('/\s*\+\s*/', $matches[1]);
            $amount = 0;
            foreach ($amountParts as $amountPart) {
                $amount += self::parseAmountFromNote($amountPart);
            }

            return [
                'type' => 'registration',
                'currency' => 'dinar',
                'amount' => $amount,
            ];
        }

        if (preg_match('/^تصليح\s+(.+)$/u', $part, $matches)) {
            $detail = trim($matches[1]);
            if (preg_match('/^([\d,\.\s]+)\$\s*(?:\((.+)\))?$/u', $detail, $repairMatch)) {
                return [
                    'type' => 'repair',
                    'currency' => 'dollar',
                    'amount' => self::parseAmountFromNote($repairMatch[1]),
                    'detail' => trim($repairMatch[2] ?? ''),
                ];
            }
            if (preg_match('/^([\d,\.\s]+)\s*د(?:\s*\((.+)\))?$/u', $detail, $repairMatch)) {
                return [
                    'type' => 'repair',
                    'currency' => 'dinar',
                    'amount' => self::parseAmountFromNote($repairMatch[1]),
                    'detail' => trim($repairMatch[2] ?? ''),
                ];
            }
        }

        return [
            'type' => 'other',
            'currency' => 'dinar',
            'amount' => 0,
        ];
    }

    public static function sumLineItemAmounts(array $items): array
    {
        $dollar = 0;
        $dinar = 0;

        foreach ($items as $item) {
            if (($item['currency'] ?? '') === 'dollar') {
                $dollar += (float) ($item['amount'] ?? 0);
            } else {
                $dinar += (float) ($item['amount'] ?? 0);
            }
        }

        return [
            'dollar' => $dollar,
            'dinar' => $dinar,
        ];
    }

    public static function formatRegistrationLineItem(
        string $type,
        string $currency,
        float $amount,
        string $detail = ''
    ): string {
        $formatted = number_format((int) round($amount), 0, '.', ',');

        if ($type === 'registration') {
            return $currency === 'dollar'
                ? "تسجيل {$formatted}$"
                : "تسجيل {$formatted} د";
        }

        $label = $currency === 'dollar' ? "{$formatted}$" : "{$formatted} د";
        if ($detail !== '') {
            $label .= " ({$detail})";
        }

        return "تصليح {$label}";
    }

    public static function rebuildRegistrationNote(string $userPrefix, array $items, string $linkSuffix = ''): string
    {
        $parts = array_map(function ($item) {
            return $item['raw'];
        }, $items);

        $itemsText = implode(' | ', $parts);
        $note = $userPrefix !== '' && $itemsText !== ''
            ? $userPrefix . ' — ' . $itemsText
            : ($userPrefix !== '' ? $userPrefix : $itemsText);

        if ($linkSuffix !== '') {
            $note = trim($note . ' ' . trim($linkSuffix));
        }

        return trim($note);
    }

    public static function calcExpenseLinkDollars($expense, float $exchangeRate, bool $treatAsLinked = false): int
    {
        if (!$treatAsLinked && !str_contains($expense->note ?? '', '[مربوط]')) {
            return 0;
        }

        $calc_rate = $exchangeRate;
        if ($calc_rate > 9999) {
            $calc_rate = $calc_rate / 100;
        }

        return (int) ((float) ($expense->amount_dollar ?? 0) + (float) ($expense->amount_dinar ?? 0) / $calc_rate);
    }

    public static function resolveLinkedRegistrationExpenses(Car $car)
    {
        $tagged = $car->carexpenses->filter(function ($expense) {
            return str_contains($expense->note ?? '', '[مربوط]');
        });

        if ($tagged->isNotEmpty()) {
            return $tagged;
        }

        if (self::isCarLinked($car) && $car->carexpenses->isNotEmpty()) {
            return $car->carexpenses;
        }

        return $car->carexpenses->filter(function ($expense) {
            return preg_match('/\[مربوط@/u', $expense->note ?? '');
        });
    }

    public static function expenseCountsAsLinked($expense, bool $forceUntaggedLinked = false): bool
    {
        if (str_contains($expense->note ?? '', '[مربوط]')) {
            return true;
        }

        return $forceUntaggedLinked;
    }

    private function applyLinkedExpenseDelta(Car $car, int $expenseDelta, string $desc): void
    {
        if ($expenseDelta === 0) {
            return;
        }

        $newExpenses = (int) (($car->expenses ?? 0) + $expenseDelta);
        $newExpensesS = (int) (($car->expenses_s ?? 0) + $expenseDelta);

        if ($newExpenses < 0 || $newExpensesS < 0) {
            throw new \Exception('تعذر التعديل: المصاريف المسجلة أقل من المربوطة');
        }

        $oldTotal = (int) ($car->total ?? 0);
        $oldTotalS = (int) ($car->total_s ?? 0);

        $dolar_price_input = $car->dolar_price ?? 1;
        $car_calc_rate = $dolar_price_input == 0 ? 1 : $dolar_price_input;
        if ($car_calc_rate > 9999) {
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
        $car_calc_rate_s = $dolar_price_s_input == 0 ? 1 : $dolar_price_s_input;
        if ($car_calc_rate_s > 9999) {
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

        if ($expenseDelta > 0) {
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
        } else {
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
        }

        $car->update([
            'expenses' => $newExpenses,
            'expenses_s' => $newExpensesS,
            'total' => $newTotal,
            'total_s' => $newTotalS,
            'profit' => $newTotalS - $newTotal,
        ]);
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

    public static function isCarInRegistrationWorkflow(Car $car): bool
    {
        return in_array((int) $car->car_have_expenses, [1, 2, self::CAR_HAVE_EXPENSES_LINKED], true);
    }

    public static function prepareCarForRegistrationDisplay(Car $car): Car
    {
        $inWorkflow = self::isCarInRegistrationWorkflow($car);
        $car->has_registration_workflow = $inWorkflow;
        if (!$inWorkflow) {
            $car->carexpenses_count = 0;
        }

        return $car;
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
            ->orderBy('id')
            ->get(['car_id', 'note'])
            ->reduce(function ($acc, $expense) {
                if (isset($acc[$expense->car_id])) {
                    return $acc;
                }
                $rate = self::parseLinkExchangeRate(collect([$expense]));
                if ($rate) {
                    $acc[$expense->car_id] = $rate;
                }

                return $acc;
            }, []);

        return $collection->map(function ($car) use ($rateByCarId) {
            $carId = is_array($car) ? ($car['id'] ?? null) : ($car->id ?? null);
            $rate = $carId ? ($rateByCarId[$carId] ?? null) : null;

            if (is_array($car)) {
                $car['link_exchange_rate'] = $rate;
            } else {
                $car->link_exchange_rate = $rate;
            }

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