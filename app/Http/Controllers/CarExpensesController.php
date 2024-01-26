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


use Carbon\Carbon;

use Inertia\Inertia;

class CarExpensesController extends Controller
{
    public function __construct(AccountingController $accountingController)
    {
    $this->accountingController = $accountingController;
    $this->url = env('FRONTEND_URL');
    $this->userAdmin =  UserType::where('name', 'admin')->first()->id;
    $this->userClient =  UserType::where('name', 'client')->first()->id;
    $this->userAccount =  UserType::where('name', 'account')->first()->id;
    $this->mainAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','main@account.com');
    $this->inAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','in@account.com');
    $this->outAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','out@account.com');
    $this->debtAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','debt@account.com');
    $this->transfersAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','transfers@account.com');
    $this->outSupplier= User::with('wallet')->where('type_id', $this->userAccount)->where('email','supplier-out');
    $this->debtSupplier= User::with('wallet')->where('type_id', $this->userAccount)->where('email','supplier-debt');
    $this->onlineContracts= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts');
    $this->onlineContractsDinar= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-dinar');
    $this->debtOnlineContracts= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-debt');
    $this->debtOnlineContractsDinar= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-debit-dinar');
    $this->howler= User::with('wallet')->where('type_id', $this->userAccount)->where('email','howler');
    $this->shippingCoc= User::with('wallet')->where('type_id', $this->userAccount)->where('email','shipping-coc');
    $this->border= User::with('wallet')->where('type_id', $this->userAccount)->where('email','border');
    $this->iran= User::with('wallet')->where('type_id', $this->userAccount)->where('email','iran');
    $this->dubai= User::with('wallet')->where('type_id', $this->userAccount)->where('email','dubai');
    $this->mainBox= User::with('wallet')->where('type_id', $this->userAccount)->where('email','mainBox@account.com');
    }

    public function index(Request $request)
    {
        $owner_id=Auth::user()->owner_id;
        $client = User::where('type_id', $this->userClient)->where('owner_id',$owner_id)->get();
        return Inertia::render('CarExpenses/index', ['client'=>$client ]);   
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
}