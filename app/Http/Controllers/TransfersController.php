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



use Carbon\Carbon;

use Inertia\Inertia;

class TransfersController extends Controller
{
    public function __construct(AccountingController $accountingController)
    {
    $this->accountingController = $accountingController;
    $this->url = env('FRONTEND_URL');
    $this->userAdmin =  UserType::where('name', 'admin')->first()->id;
        $this->userErbil =  UserType::where('name', 'erbil')->first()->id;

    $this->userClient =  UserType::where('name', 'client')->first()->id;
    $this->userAccount =  UserType::where('name', 'account')->first()->id;
    $this->mainAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','main@account.com')->first();
    $this->inAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','in@account.com')->first();
    $this->outAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','out@account.com')->first();
    $this->debtAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','debt@account.com')->first();
    $this->transfersAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','transfers@account.com')->first();
    $this->outSupplier= User::with('wallet')->where('type_id', $this->userAccount)->where('email','supplier-out')->first();
    $this->debtSupplier= User::with('wallet')->where('type_id', $this->userAccount)->where('email','supplier-debt')->first();
    $this->onlineContracts= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts')->first();
    $this->debtOnlineContracts= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-debt')->first();
    $this->howler= User::with('wallet')->where('type_id', $this->userAccount)->where('email','howler')->first();
    $this->shippingCoc= User::with('wallet')->where('type_id', $this->userAccount)->where('email','shipping-coc')->first();
    $this->border= User::with('wallet')->where('type_id', $this->userAccount)->where('email','border')->first();
    $this->iran= User::with('wallet')->where('type_id', $this->userAccount)->where('email','iran')->first();
    $this->dubai= User::with('wallet')->where('type_id', $this->userAccount)->where('email','dubai')->first();

    }
    public function __invoke(Request $request)
    {
        $results = null;
       // $client = new Client( $this->url, 'masterKey');
       // $results = $client->stats();
        //dd($results);
        return Inertia::render('dashboard', ['url'=>$this->url]);   

    }
    public function index(Request $request)
    {
        $users = User::where('id', $this->mainAccount->id)->orWhere('id', $this->howler->id)->orWhere('id', $this->shippingCoc->id)->orWhere('id', $this->border->id)->orWhere('id', $this->iran->id)->orWhere('id', $this->dubai->id)->orWhere('id', $this->onlineContracts->id)->orWhere('type_id',$this->userClient)->get();
        return Inertia::render('FormRegistrationCourt', ['url'=>$this->url,'users'=>$users]);

    }

    public function getcountComp(Request $request)
    {
        $profile=  Car::all();
        $start = $request->get('start');
        $end = $request->get('end');
        if($start && $end ){
            $countComp =Car::whereBetween('created_at', [$start, $end])->where('user_accepted','!=',null)->count();
        }
        else{
            $countComp =Car::where('user_accepted','!=',null)->count();  
        }
        return response()->json($countComp); 
    }
    public function addTransfers()
    {
        $maxNo = Transfers::max('no');
        $no = $maxNo + 1;
        $tran=Transfers::create([
            'no'=>$no,
            'user_id' =>$_GET['user_id'],
            'amount'=> $_GET['amount'],
            'note'=>$_GET['note'],
             ]);
        if($tran->id){
            $desc=trans('text.addTransfer').' '.$tran->amount.' '.($tran->currency ?? '$').' || '.$_GET['note']??'';
            $this->accountingController->increaseWallet($tran->amount, $desc,$this->transfersAccount->id,$tran->id,'App\Models\Transactions');
        }
        return Response::json('ok', 200);    
    }
    public function editCar(Request $request)
    {
        $maxNo = Car::max('no');
        $no = $maxNo + 1;
        $car=Car::updateOrCreate(['id' => $_GET['id']],[
            'company_id' =>$_GET['company_id'],
            'name_id'=> $_GET['name_id'],
            'model_id'=> $_GET['model_id'],
            'color_id'=> $_GET['color_id'],
            'pin'=> $_GET['pin'],
            'purchase_data'=> $_GET['purchase_data'],
            'purchase_price'=> $_GET['purchase_price'],
            'paid_amount'=> $_GET['paid_amount'],
            'note'=> $_GET['note']??'',
            'user_id'=> auth()->user()->id,
            'no'=>$no
             ]);
        
            return Response::json('ok', 200);    
    }

    public function payCar(Request $request)
    {   
        if( $_GET['client_name']){
            $client = new User;
            $client->name = $_GET['client_name'];
            $client->phone = $_GET['client_phone'];
            $client->save();
            Wallet::create(['user_id' => $user->id]);
        }

        $car=Car::updateOrCreate(['id' => $_GET['id']],[
            'note_pay' =>$_GET['note_pay'],
            'client_id'=> ($client??'') ? $client->id : $_GET['client_id'],
            'pay_data'=> Carbon::now()->format('Y-m-d'),
            'pay_price'=> $_GET['pay_price'],
            'paid_amount_pay' =>  $_GET['paid_amount_pay'],
            'results'=>1
             ]);
        if($car->id){
                $desc=trans('text.buyCar').' '.$car->pay_price.trans('text.payDone').$car->paid_amount_pay;
                $this->accountingController->decreaseWallet($car->paid_amount_pay, $desc,$this->mainAccount->id);
                $this->accountingController->increaseWallet($car->paid_amount_pay, $desc,$this->inAccount->id);
            }
            return Response::json('ok', 200);    
    }
    public function getIndexCar()
    {
        $data =  Car::with('carmodel')->with('name')->with('color')->with('company')->with('client');
        $type =$_GET['type'] ?? '';
        if($type){
        $data =    $data->where('results', $type);
        }
        if($type==0){
            $data =    $data->where('results', $type);
        }
        $data =$data->paginate(1000);
        return Response::json($data, 200);
    }
    
}
