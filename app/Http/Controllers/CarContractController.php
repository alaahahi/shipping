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
use App\Models\CarContract;



use Carbon\Carbon;

use Inertia\Inertia;

class CarContractController extends Controller
{
    public function __construct(AccountingController $accountingController)
    {
    $this->accountingController = $accountingController;
    $this->userClient =  UserType::where('name', 'client')->first()->id;
    }

    public function contract(Request $request)
    {
        $id=$request->id;
        $data = CarContract::find($id);
        $owner_id=Auth::user()->owner_id;
        $client1 = CarContract::where('owner_id', $owner_id)
        ->select('name_seller', DB::raw('MAX(phone_seller) as phone_seller'), DB::raw('MAX(address_seller) as address_seller'))
        ->groupBy('name_seller')
        ->get();
        $client2 = CarContract::where('owner_id', $owner_id)
        ->select('name_buyer', DB::raw('MAX(phone_buyer) as phone_buyer'), DB::raw('MAX(address_buyer) as address_buyer'))
        ->groupBy('name_buyer')
        ->get();
        

        return Inertia::render('CarContract/add', ['client1'=>$client1,'data'=>$data,'client2'=>$client2 ]);   
    }
    public function contract_print(Request $request)
    {
        $id=$request->id;
        $data = CarContract::find($id);
        $owner_id=Auth::user()->owner_id;
        $client = User::where('type_id', $this->userClient)->where('owner_id',$owner_id)->get();
        $config=SystemConfig::first();

        return view('receiptContract',compact('data','config'));
    }
    public function index(Request $request)
    {
        $owner_id=Auth::user()->owner_id;
        $client = User::where('type_id', $this->userClient)->where('owner_id',$owner_id)->get();
        return Inertia::render('CarContract/index', ['client'=>$client ]);   
    }
    public function contract_account(Request $request)
    {
        $owner_id=Auth::user()->owner_id;
        $client = User::where('type_id', $this->userClient)->where('owner_id',$owner_id)->get();
        return Inertia::render('CarContract/account', ['client'=>$client ]);   
    }
    public function addCarContract(Request $request)
    {
        $contract= $request->all();
        $owner_id=Auth::user()->owner_id;
        $user_id=Auth::user()->id;
        $year_date=Carbon::now()->format('Y');
        $created= Carbon::now()->format('Y-m-d');

        $tex_seller = $request->tex_seller;
        $tex_seller_dinar = $request->tex_seller_dinar;
        $tex_buyer = $request->tex_buyer;
        $tex_buyer_dinar = $request->tex_buyer_dinar;
        $tex_seller_paid = $request->tex_seller_paid;
        $tex_seller_dinar_paid = $request->tex_seller_dinar_paid;
        $tex_buyer_paid = $request->tex_buyer_paid;
        $tex_buyer_dinar_paid = $request->tex_buyer_dinar_paid;

        // Perform your logic here using the values
        // For example:
        $status = 2; // Default status assuming all fields are equal

        if ($tex_seller != $tex_seller_paid || $tex_seller_dinar != $tex_seller_dinar_paid || $tex_buyer != $tex_buyer_paid || $tex_buyer_dinar != $tex_buyer_dinar_paid) {
            $status = 1; // Set status to 1 if any field is not equal to its corresponding _paid field
        }

        // Check if all fields are 0
        if ($status == 2 && $tex_seller == 0 && $tex_seller_dinar == 0 && $tex_buyer == 0 && $tex_buyer_dinar == 0 && $tex_seller_paid == 0 && $tex_seller_dinar_paid == 0 && $tex_buyer_paid == 0 && $tex_buyer_dinar_paid == 0) {
            $status = 0; // Set status to 0 if all fields are 0
        }
        $contract['status']=$status;
        $contract['owner_id']=$owner_id;
        $contract['user_id']=$user_id;
        $contract['year_date']=$year_date;
        $contract['created']=$created;

        

        $car = CarContract::updateOrCreate(
            ['id' => $contract['id']??0], // Search criteria, usually the primary key
            $contract // Data to be inserted or updated
        );


        return Response::json('ok', 200);    
    }
    public function getIndexContractCar(Request $request){
        $owner_id=Auth::user()->owner_id;
        $car_have_expenses = $request->car_have_expenses ?? '';
        $user_id =$_GET['user_id'] ?? '';
        $q = $_GET['q']??'';
        $from =  $_GET['from'] ?? 0;
        $to =$_GET['to'] ?? 0;
        $limit =$_GET['limit'] ?? 0;
 
        $data = CarContract::with('user')->where('owner_id', $owner_id)->orderBy('id', 'desc');;

        if ($from && $to) {
            $data->whereBetween('date', [$from, $to]);
        }
        
        if($q){
            $data->where(function ($query) use ($q) {
                $query->where('name_seller', 'LIKE', '%' . $q . '%')
                    ->orWhere('vin', 'LIKE', '%' . $q . '%')
                    ->orWhere('car_name', 'LIKE', '%' . $q . '%')
                    ->orWhere('name_buyer', 'LIKE', '%' . $q . '%');
            });
        }

        $data =$data->paginate($limit)->toArray();
        return Response::json($data, 200);
    }
    
    public function DelCarContract(Request $request){
        try {
            $expenses = CarContract::findOrFail($request->id);
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
    public function totalInfoContract(Request $request){
    $owner_id=Auth::user()->owner_id;

    $allContract = CarContract::with('user')->where('owner_id', $owner_id);

    $sumAllContractSeller = $allContract->sum('tex_seller');
    $sumAllContractSellerPaid = $allContract->sum('tex_seller_paid');

    $sumAllContractBuyer = $allContract->sum('tex_buyer');
    $sumAllContractBuyerPaid = $allContract->sum('tex_buyer_paid');

    $sumAllContractSellerDinar = $allContract->sum('tex_seller_dinar');
    $sumAllContractSellerPaidDinar = $allContract->sum('tex_seller_dinar_paid');

    $sumAllContractBuyerDinar = $allContract->sum('tex_buyer_dinar');
    $sumAllContractBuyerPaidDinar = $allContract->sum('tex_buyer_dinar_paid');

    
     // Additional logic to retrieve client data
     $data = [
         'contract' => $allContract->count(),
         'sum_contract' => $sumAllContractSellerPaid+$sumAllContractBuyerPaid,
         'sum_contract_debit' => ($sumAllContractSeller-$sumAllContractSellerPaid)+($sumAllContractBuyer-$sumAllContractBuyerPaid),
         'sum_contract_dinar' => $sumAllContractSellerPaidDinar+$sumAllContractBuyerPaidDinar,
         'sum_contract_debit_dinar' => ($sumAllContractSellerDinar-$sumAllContractSellerPaidDinar)+($sumAllContractBuyerDinar-$sumAllContractBuyerPaidDinar),
     ];

        return Response::json($data, 200);

    }
}