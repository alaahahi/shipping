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
use App\Models\Company;
use App\Models\Name;
use App\Models\CarModel;
use App\Models\Color;
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


use Carbon\Carbon;

use Inertia\Inertia;

class CarExpensesController extends Controller
{
    public function __construct(AccountingController $accountingController)
    {
    $this->accountingController = $accountingController;
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
        $expenses->amount_dinar = $request->amountDinar;
        $expenses->amount_dollar = $request->amountDollar;
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
}