<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Card;
use App\Models\User;
use App\Models\Profile;
use App\Models\UserType;
use App\Models\Wallet;
use App\Models\Results;
use App\Models\Company;
use App\Models\Transactions;
use App\Models\SystemConfig;
use App\Models\Name;
use App\Models\Car;
use App\Models\ExitCar;
use App\Models\CarModel;
use App\Models\Contract;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Massage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;






class OnlineContractsController extends Controller
{
    public function __construct(AccountingController $accountingController)
    {
    $this->accountingController = $accountingController;
    $this->url = env('FRONTEND_URL');
    $this->userAdmin =  UserType::where('name', 'admin')->first()->id;
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
    $this->onlineContractsDinar= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-dinar')->first();
    $this->debtOnlineContracts= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-debt')->first();
    $this->debtOnlineContractsDinar= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-debit-dinar')->first();
    $this->howler= User::with('wallet')->where('type_id', $this->userAccount)->where('email','howler')->first();
    $this->shippingCoc= User::with('wallet')->where('type_id', $this->userAccount)->where('email','shipping-coc')->first();
    $this->border= User::with('wallet')->where('type_id', $this->userAccount)->where('email','border')->first();
    $this->iran= User::with('wallet')->where('type_id', $this->userAccount)->where('email','iran')->first();
    $this->dubai= User::with('wallet')->where('type_id', $this->userAccount)->where('email','dubai')->first();
    $this->mainBox= User::with('wallet')->where('type_id', $this->userAccount)->where('email','mainBox@account.com')->first();
    $this->currentDate = Carbon::now()->format('Y-m-d');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function index()
    {   
        try {
            $authUser = auth()?->user();
            if($authUser){
                $wallet = Wallet::where('user_id', $authUser->id)->first();
                $card = $wallet->card ??'';
                return Inertia::render('FormRegistration/Index', ['url'=>$this->url,'card'=>$card]);
            }
            else {
                return Inertia::render('Auth/Login');
            }
        } catch (\Throwable $th) {
            return Inertia::render('Auth/Login');
        }
    }
    public function online_contracts()
    {   
        $car = Car::all();
    
        $allCars = $car->count();
        $client = User::where('type_id', $this->userClient)->get();

        return Inertia::render('OnlineContracts', ['client'=>$client,
        'mainAccount'=>$this->mainAccount->wallet->balance,'allCars'=>$allCars ]);   

    }
    public function addCarContracts(Request $request){
        $price=$request->price;
        $price_dinar=$request->price_dinar;
        $paid=$request->paid;
        $paid_dinar=$request->paid_dinar;
        $phone=$request->phone;
        $note=$request->note;
        $car_id=$request->car_id;
        $car = Car::find($car_id);
        $data = ['user_id'=>$car->client_id,'car_id'=>$car->id,"price"=>$price,"price_dinar"=>$price_dinar,'paid'=>$paid,'paid_dinar'=>$paid_dinar,'note'=>$note,'created'=>Carbon::now()->format('Y-m-d')];
        $contract=Contract::create($data);
        $car->update(['contract_id'=>$contract->id]);
        if($contract){
            $desc="انشاء عقد بقيمة ".$price." وتم دفع مبلغ".$paid.' '.$car->car_type.' رقم الشانص'.$car->vin.' '.$note;
            $descD="انشاء عقد بقيمة ".$price_dinar." وتم دفع مبلغ".$paid_dinar.' '.$car->car_type.' رقم الشانص'.$car->vin.' '.$note;
            $descDebit="دين من عقد السيارة ".$car->car_type.' '.' '.$car->car_type.' '.$car->car_type.' رقم الشانص'.$car->vin.' '.$note;
            if($paid){
                $tranDollar=$this->accountingController->increaseWallet($paid,$desc,$this->accountingController->mainBox->id,$this->mainBox->id,'App\Models\Car',0,0,'$');
                $this->accountingController->increaseWallet($paid, $desc,$this->onlineContracts->id,$car->id,'App\Models\Car',0,0,'$',0,$tranDollar->id);
            }

            if($paid_dinar){
                $tranDinar=$this->accountingController->increaseWallet($paid_dinar,$descD,$this->mainBox->id,$this->mainBox->id,'App\Models\Car',0,0,'IQD');
                $this->accountingController->increaseWallet($paid_dinar, $descD,$this->onlineContractsDinar->id,$car->id,'App\Models\Car',0,0,'IQD',0,$tranDinar->id);
            }
        

            if($price-$paid > 0){
                $this->accountingController->increaseWallet($price-$paid, $descDebit,$this->debtOnlineContracts->id,$car->id,'App\Models\Car',0,0,'$',0);
            }
            if($price_dinar-$paid_dinar > 0){
            $this->accountingController->increaseWallet($price_dinar-$paid_dinar, $descDebit,$this->debtOnlineContractsDinar->id,$car->id,'App\Models\Car',0,0,'IQD',0);
            }
            if(($price-$paid == 0)&&($price_dinar-$paid_dinar == 0)){
                $dataExitCar=['car_id'=>$car->id,'user_id'=>$car->client_id, 'created'=>$this->currentDate, 'phone' =>$phone, 'note' =>$note];
                $exitCar = ExitCar::create($dataExitCar);
                $car->update(['is_exit'=>$exitCar->id]);

            }
        }
        return Response::json('ok', 200);    

    }
    public function makeCarExit(Request $request){
        $car_id=$request->car_id;
        $created=$request->created ?? $this->currentDate;
        $phone=$request->phone ?? 0;
        $note=$request->note ?? '';
        $car = Car::find($car_id);

        $data=['car_id'=>$car_id,'user_id'=>$car->client_id, 'created'=>$created, 'phone' =>$phone, 'note' =>$note];
        $exitCar = ExitCar::create($data);
        $car->update(['is_exit'=>$exitCar->id]);
        return $exitCar;
    }

    public function editCarContracts(Request $request){
        $paid=$request->paid;
        $paid_dinar=$request->paid_dinar;
        $note=$request->notePayment;
        $car_id=$request->car_id;
        $car = Car::find($car_id);
        $phone=$request->phone ?? 0;

        $contract=Contract::find($car->contract->id);
;
        $descDebit="دين من عقد السيارة ".$car->car_type.' '.$car->car_type.' رقم الشانص'.$car->vin.' '.$note;
        if($paid){
            $contract->increment('paid',$paid);
            $tran=$this->accountingController->increaseWallet($paid,$descDebit,$this->accountingController->mainBox->id,$this->mainBox->id,'App\Models\Car',0,0,'$');

            $this->accountingController->increaseWallet($paid, $descDebit,$this->onlineContracts->id,$car->id,'App\Models\Car',0,0,'$',0,$tran->id);
            $this->accountingController->decreaseWallet($paid, $descDebit,$this->debtOnlineContracts->id,$car->id,'App\Models\Car',0,0,'$',0,$tran->id);
        }
        if($paid_dinar)
        {
            $contract->increment('paid_dinar',$paid_dinar);
            $tran=$this->accountingController->increaseWallet($paid_dinar,$descDebit,$this->accountingController->mainBox->id,$this->mainBox->id,'App\Models\Car',0,0,'IQD');
            $this->accountingController->increaseWallet($paid_dinar, $descDebit,$this->onlineContractsDinar->id,$car->id,'App\Models\Car',0,0,'IQD',0,$tran->id);
            $this->accountingController->decreaseWallet($paid_dinar, $descDebit,$this->debtOnlineContractsDinar->id,$car->id,'App\Models\Car',0,0,'IQD',0,$tran->id);
        }
       

        if(( $contract->price-$paid == 0)&&($contract->price_dinar-$paid_dinar == 0)){
            $dataExitCar=['car_id'=>$car->id,'user_id'=>$car->client_id, 'created'=>$this->currentDate, 'phone' =>$phone, 'note' =>$note];
            $exitCar = ExitCar::create($dataExitCar);
            $car->update(['is_exit'=>$exitCar->id]);

        }
        return Response::json('ok', 200);    

    }
    
    }