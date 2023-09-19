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
    $this->userSeles =  UserType::where('name', 'seles')->first()->id;
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
        $amountTotal=$request->amountTotal;
        $amountPaid=$request->amountPaid;
        $note=$request->note;
        $car_id=$request->car_id;
        $car = Car::find($car_id);
        $data = ['user_id'=>$car->client_id,'car_id'=>$car->id,"price"=>$amountTotal,'paid'=>$amountPaid,'note'=>$note,'created'=>Carbon::now()->format('Y-m-d')];
        $contract=Contract::create($data);
        if($contract){
            $desc="انشاء عقد بقيمة ".$amountTotal." وتم دفع مبلغ".$amountPaid.' '.$note;
            $descDebit="دين من عقد السيارة ".$car->car_type.' '.$note;;
            $this->accountingController->increaseWallet($amountPaid, $desc,$this->onlineContracts->id,$car->id,'App\Models\Car');
            if($amountTotal-$amountPaid > 0){
                $this->accountingController->increaseWallet($amountTotal-$amountPaid, $descDebit,$this->debtOnlineContracts->id,$car->id,'App\Models\Car');
            }
        }
        return Response::json('ok', 200);    

    }
    public function editCarContracts(Request $request){
        $amount=$request->amountPayment;
        $note=$request->notePayment;
        $car_id=$request->car_id;
        $car = Car::find($car_id);
    
        $contract=Contract::find($car->contract->id);
        $contract->increment('paid',$amount);
        $descDebit="دين من عقد السيارة ".$car->car_type.' '.$note;;
        $this->accountingController->increaseWallet($amount, $descDebit,$this->onlineContracts->id,$car->id,'App\Models\Car');
        $this->accountingController->decreaseWallet($amount, $descDebit,$this->debtOnlineContracts->id,$car->id,'App\Models\Car');

        return Response::json('ok', 200);    

    }
    
    }