<?php

namespace App\Http\Controllers;
use App\Http\Controllers\AccountingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use App\Models\Info;
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
use App\Helpers\UploadHelper;

use Carbon\Carbon;

use Inertia\Inertia;

class DashboardController extends Controller
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
        $car = Car::all();
    
        $allCars = $car->count();
        $client = User::where('type_id', $this->userClient)->get();
        return Inertia::render('Dashboard', ['client'=>$client,
        'mainAccount'=>$this->mainAccount->wallet->balance,'allCars'=>$allCars ]);   

    }
    public function purchases(Request $request)
    {
        $car = Car::all();
    
        $allCars = $car->count();
        $client = User::where('type_id', $this->userClient)->get();

        return Inertia::render('purchases', ['client'=>$client,
        'mainAccount'=>$this->mainAccount->wallet->balance,'allCars'=>$allCars ]);   

    }
    public function sales(Request $request)
    {
        $car = Car::all();
    
        $allCars = $car->count();
        $client = User::where('type_id', $this->userClient)->get();

        return Inertia::render('Sales', ['client'=>$client,
        'mainAccount'=>$this->mainAccount->wallet->balance,'allCars'=>$allCars ]);   

    }
    public function totalInfo(Request $request)
    {
        $car = Car::all();

        $data = [
        'mainAccount'=>$this->mainAccount->wallet->balance??0,
        'allCars'=>$car->count()??0,
        'carsInStock'=>$car->where('client_id',null)->count()??0
        ];
        return response()->json(['data'=>$data]); 

    }
    public function client(Request $request)
    {
        $client = User::where('type_id', $this->userClient)->with('wallet')->paginate(10);
        return response()->json($client); 
    }
    public function getcount(Request $request)
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
    public function addCar(Request $request)
    {
        $car_id=$request->id??0;
        $maxNo = Car::max('no');
        if($car_id){
            $no = $request->no;
        }else{
            $no = $maxNo + 1;
        }
        $images=[];
        $paid_amount = $request->paid_amount??0;
        $purchase_price =$request->purchase_price;
        $debt_price =$purchase_price - $paid_amount;
        if($request->image ){
            foreach ($request->image as $image) {
                $imageName = $image->getClientOriginalName().$no;
                $filename = pathinfo($imageName, PATHINFO_FILENAME);
                $imagePath = UploadHelper::upload('image', $image, $filename, 'storage/car');
                $images[] = $imagePath;
            }    
        }

        if(!$car_id){
        $car=Car::create([
            'company_id' =>$request->company_id,
            'name_id'=> $request->name_id,
            'model_id'=> $request->model_id,
            'color_id'=> $request->color_id,
            'pin'=> $request->pin,
            'purchase_data'=> $request->purchase_data,
            'purchase_price'=> $purchase_price,
            'paid_amount'=> $paid_amount,
            'note'=> $request->note??'',
            'image'=>$images ? json_encode($images):"",
            'user_id'=> $request->user_id??0,
            'no'=>$no
             ]);
             if($paid_amount){
                $desc=trans('text.payCar').' '.$purchase_price.trans('text.payDone').$paid_amount;
             
                $this->accountingController->decreaseWallet($paid_amount, $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
                $this->accountingController->increaseWallet($paid_amount, $desc,$this->outAccount->id,$car->id,'App\Models\Car' );
                $this->accountingController->increaseWallet($paid_amount, $desc,$this->outSupplier->id,$car->id,'App\Models\Car');
                if($debt_price){
                   $this->accountingController->increaseWallet($debt_price, $desc,$this->debtSupplier->id,$car->id,'App\Models\Car');
                }
             }
 
        }else{
            $car=Car::find($car_id);
            $purchase_price_old=$car->purchase_price;
            if($purchase_price > $purchase_price_old){
                $purchase_price_new = $purchase_price - $purchase_price_old;
                $desc=trans('text.editCar').' '.trans('text.from').$purchase_price_old.trans('text.to').$purchase_price;
                $this->accountingController->decreaseWallet($purchase_price_new, $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
                $this->accountingController->increaseWallet($purchase_price_new, $desc,$this->outAccount->id,$car->id,'App\Models\Car' );
                $this->accountingController->decreaseWallet($purchase_price_new, $desc,$this->inAccount->id,$car->id,'App\Models\Car');
                $this->accountingController->increaseWallet($purchase_price_new, $desc,$this->outSupplier->id,$car->id,'App\Models\Car');
                $this->accountingController->increaseWallet($purchase_price_new, $desc,$this->debtSupplier->id,$car->id,'App\Models\Car');
            }
            if($purchase_price < $purchase_price_old){
                $purchase_price_new =$purchase_price_old - $purchase_price;
                $desc=trans('text.editCar').' '.trans('text.from').$purchase_price_old.trans('text.to').$purchase_price;
                $this->accountingController->increaseWallet($purchase_price_new, $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
                $this->accountingController->decreaseWallet($purchase_price_new, $desc,$this->outAccount->id,$car->id,'App\Models\Car' );
                $this->accountingController->increaseWallet($purchase_price_new, $desc,$this->inAccount->id,$car->id,'App\Models\Car');
                $this->accountingController->decreaseWallet($purchase_price_new, $desc,$this->outSupplier->id,$car->id,'App\Models\Car');
                $this->accountingController->decreaseWallet($purchase_price_new, $desc,$this->debtSupplier->id,$car->id,'App\Models\Car');

            }
            $car->update([
                'company_id' =>$request->company_id,
                'name_id'=> $request->name_id,
                'model_id'=> $request->model_id,
                'color_id'=> $request->color_id,
                'pin'=> $request->pin,
                'purchase_data'=> $request->purchase_data,
                'purchase_price'=> $purchase_price,
                'paid_amount'=> $paid_amount,
                'note'=> $request->note??'',
                'image'=>$images ? json_encode($images):"",
                'no'=>$no
                 ]);
        }

        return Response::json('ok', 200);    
    }
    public function addCars(Request $request)
    {
        $client_id =$request->client_id;
    


        $car_id=$request->id??0;

        $maxNo = Car::max('no');

        if($car_id){
            $no = $request->no;
        }else{
            $no = $maxNo + 1;
        }
        $images=[];
        $checkout=$request->checkout ?? 0;
        $shipping_dolar=$request->shipping_dolar ?? 0;
        $coc_dolar=$request->coc_dolar ?? 0;
        $dinar=$request->dinar ?? 0;
        $dolar_price=$request->dolar_price ?? 1;
        $expenses=$request->expenses ?? 0;
        $paid=$paid ?? 0;
        if($dolar_price==0){
            $dolar_price=1;
        }
        $total_amount = ($checkout+$shipping_dolar+$expenses+ $coc_dolar +($dinar / $dolar_price));
        $dolar_custom=$dinar/$dolar_price;
        if( $client_id==0){
            $client = new User;
            $client->name = $request->client_name;
            $client->phone = $request->client_phone;
            $client->type_id = $this->userClient;
            $client->save();
            Wallet::create(['user_id' => $client->id,'balance'=>$total_amount-$paid]);
            $client_id=$client->id;
        }else{
            $wallet = Wallet::where('user_id',$client_id)->first();
            $wallet->increment('balance',$total_amount-$paid); 
        }

        $car=Car::create([
            'note'=> $request->note??'',
            'no'=>$no,
            'car_owner'=> $request->car_owner,
            'car_type'=> $request->car_type,
            'vin'=> $request->vin,
            'car_number'=> $request->car_number,
            'dinar'=> $request->dinar,
            'dolar_price'=> $request->dolar_price,
            'dolar_custom'=> $dolar_custom,
            'shipping_dolar'=> $request->shipping_dolar,
            'coc_dolar'=> $request->coc_dolar,
            'checkout'=> $request->checkout,
            'total'=> $total_amount,
            'paid'=> $paid,
            'year'=> $request->year,
            'car_color'=> $request->car_color,
            'date'=> $request->date,
            'expenses'=> $request->expenses,
            'profit'=> $paid-$total_amount,
            'client_id'=>$client_id
             ]);
             if($paid){
                if($paid-$total_amount >0){
                    $desc=trans('text.payCar').' '.$total_amount.trans('text.payDone').($paid);
                    $this->accountingController->increaseWallet(($paid-$total_amount), $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
                }else
                $desc=trans('text.payCar').' '.$total_amount.trans('text.payDone');
                $this->accountingController->decreaseWallet(($paid-$total_amount), $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
             }else{
                $desc=trans('text.payCar').' '.$total_amount.trans('text.payDone');
                $this->accountingController->decreaseWallet(($total_amount), $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
             }
            
 

        return Response::json('ok', 200);    
    }
    public function updateCars(Request $request)
    {
        $car_id=$request->id??0;

        $maxNo = Car::max('no');

        if($car_id){
            $no = $request->no;
        }else{
            $no = $maxNo + 1;
        }
        $images=[];
        $checkout=$request->checkout ?? 0;
        $shipping_dolar=$request->shipping_dolar ?? 0;
        $coc_dolar=$request->coc_dolar ?? 0;
        $dinar=$request->dinar ?? 0;
        $dolar_price=$request->dolar_price ?? 1;
        $paid=$request->paid ??0;
        if($dolar_price==0){
            $dolar_price=1;
        }
        $total_amount = ($checkout+$shipping_dolar+ $coc_dolar +($dinar / $dolar_price)) ??0;
        // if($request->image ){
        //     foreach ($request->image as $image) {
        //         $imageName = $image->getClientOriginalName().$no;
        //         $filename = pathinfo($imageName, PATHINFO_FILENAME);
        //         $imagePath = UploadHelper::upload('image', $image, $filename, 'storage/car');
        //         $images[] = $imagePath;
        //     }    
        // }

            $car=Car::find($car_id);
            if(!isset($no)){
                $no=$car->no;
               
            }
            $purchase_paid_old=$car->paid;
            if($paid > $purchase_paid_old){
                $paid_new = $paid - $purchase_paid_old;
                $desc=trans('text.editCar').' '.trans('text.from').$purchase_paid_old.trans('text.to').$paid;
                $this->accountingController->increaseWallet($paid_new, $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
            }
            if($paid < $purchase_paid_old){
                $paid_new =$purchase_paid_old - $paid;
                $desc=trans('text.editCar').' '.trans('text.from').$purchase_paid_old.' '.trans('text.to').$paid;
                $this->accountingController->decreaseWallet($paid_new, $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
            }
            // Define an array of field names that you want to update
            $fillableFields = [
                'company_id', 'name_id', 'year', 'car_color', 'pin',
                'note', 'image', 'user_id', 'no',
                'car_owner', 'car_type', 'vin', 'car_number', 'dinar',
                'dolar_price', 'dolar_custom', 'shipping_dolar',
                'coc_dolar', 'checkout', 'paid','dinar_s',
                'dolar_price_s',
                'dolar_custom_s',
                'checkout_s',
                'shipping_dolar_s','expenses',
                'coc_dolar_s',
            ];

            // Extract the relevant fields from the $request object
            $dataToUpdate = $request->only($fillableFields);

            // If 'purchase_price' and 'paid_amount' are calculated separately, add them to $dataToUpdate

            // Update the car model
            $car->update($dataToUpdate);
      

        return Response::json('ok', 200);    
    }
    public function getIndexExpenses () {

        $expenses = Expenses::with('user')->paginate(10);
        return Response::json($expenses, 200);    

    }
    public function addGenExpenses (Request $request){
        $expenses = Expenses::create([
            'user_id' => $request->user_id,
            'reason' => $request->reason ?? '',
            'amount' => $request->amount ?? 0,
            'note' => $request->note ?? '',
        ]);
        if($expenses->id){
            $desc=trans('text.genExpenses');
            $this->accountingController->increaseWallet($expenses->amount, $desc,$this->outAccount->id,($request->user_id??0),'App\Models\User', ($request->user_id??0));
            $this->accountingController->decreaseWallet($expenses->amount, $desc,$this->mainAccount->id,($request->user_id??0),'App\Models\User', ($request->user_id??0));
        }
        return Response::json('ok', 200);    

    }
    public function GenExpenses (){
        $expenses = Expenses::create([
            'user_id' => $_GET['user_id'],
            'reason' => $_GET['reason'],
            'amount' => $_GET['amount'],
            'note' => $_GET['note'],
        ]);
        if($expenses->id){
            $desc=trans('text.genExpenses');
            $this->accountingController->increaseWallet($expenses->amount, $desc,$this->outAccount->id,($_GET['user_id']??0),'App\Models\User', ($_GET['user_id']??0));
            $this->accountingController->decreaseWallet($expenses->amount, $desc,$this->mainAccount->id,($_GET['user_id']??0),'App\Models\User', ($_GET['user_id']??0));
        }
        return Response::json('ok', 200);    

    }
    public function addExpenses (){

        $user_id=$_GET['user_id']??0;
        $car_id=$_GET['car_id']??0;        
        $expenses_id=$_GET['expenses_id']??0;
        $expens_amount=$_GET['expens_amount']??0;
        $note=$_GET['note']??'';
        
        $expensesType =ExpensesType::find($expenses_id);
        $car = Car::with('company')->with('name')->find($car_id);
        switch ($expensesType->id) {
            case 1:
                $car->increment('dubai_exp',$expens_amount);
                $desc=trans('text.expensesExpDubai').$expens_amount.'$ '.$car->company?->name.' '.$car->name?->name.' '.$note;
                $this->accountingController->decreaseWallet($expens_amount, $desc,$this->mainAccount->id,$car_id,'App\Models\Car',$user_id);
                $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                break;
            
            case 2:
                $car->increment('erbil_exp',$expens_amount);
                $desc=trans('text.expensesExpErbil').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name.' '.$note;
                $this->accountingController->decreaseWallet($expens_amount, $desc,$this->mainAccount->id,$car_id,'App\Models\Car',$user_id);
                $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                break;

            case 3:
                $car->increment('erbil_shipping',$expens_amount);
                $desc=trans('text.expensesShippingErbil').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name.' '.$note;
                $this->accountingController->decreaseWallet($expens_amount, $desc,$this->mainAccount->id,$car_id,'App\Models\Car',$user_id);
                $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                break;
            
            case 4:
                $car->increment('dubai_shipping',$expens_amount);
                $desc=trans('text.expensesShippingDubai').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name.' '.$note;
                $this->accountingController->decreaseWallet($expens_amount, $desc,$this->mainAccount->id,$car_id,'App\Models\Car',$user_id);
                $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                break;

            case 5:
                if(($car->purchase_price - $car->paid_amount) ==0){
                    return Response::json('error', 500);       
                }
                else{
            
                    $car->increment('paid_amount',$expens_amount);
                    $desc=trans('text.expensesExpPay').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name;
                    $this->accountingController->decreaseWallet($expens_amount, $desc,$this->mainAccount->id,$car_id,'App\Models\Car',$user_id);
                    $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                    $this->accountingController->decreaseWallet($expens_amount, $desc,$this->debtSupplier->id,$car_id,'App\Models\Car',$user_id);
                    $this->accountingController->increaseWallet($expens_amount, $desc,$this->outSupplier->id,$car_id,'App\Models\Car',$user_id);

                }

                break;

            default:
                
                break;
        }


     return Response::json('ok', 200);    
        
    }
    public function payCar(Request $request)
    {
        $authUser = auth()->user();   
        $client_id =$request->client_id;
        $pay_price =(int)$request->pay_price??0;
        $paid_amount_pay =(int)$request->paid_amount_pay??0;

        if( $client_id==0){
            $client = new User;
            $client->name = $request->client_name;
            $client->phone = $request->client_phone;
            $client->type_id = $this->userClient;
            $client->save();
            Wallet::create(['user_id' => $client->id,'balance'=>$pay_price-$paid_amount_pay]);
            $client_id=$client->id;
        }else{
            $wallet = Wallet::where('user_id',$client_id)->first();
            $wallet->increment('balance',$pay_price-$paid_amount_pay); 
        }

        $car=Car::find($request->id);
        if($car->id){
                $car->update([
                'note_pay' =>$request->note_pay,
                'client_id'=> $client_id ,
                'pay_data'=> Carbon::now()->format('Y-m-d'),
                'pay_price'=>$pay_price,
                'paid_amount_pay' =>  $paid_amount_pay,
                'results'=>1
                 ]);
                $desc=trans('text.buyCar').' '.$car->pay_price.trans('text.payDone').$car->paid_amount_pay;
                $this->accountingController->increaseWallet($car->paid_amount_pay, $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
                $this->accountingController->increaseWallet($car->paid_amount_pay, $desc,$this->inAccount->id,$car->id,'App\Models\Car');
                if($pay_price-$paid_amount_pay >= 0){
                    $this->accountingController->increaseWallet($pay_price-$paid_amount_pay, $desc,$this->debtAccount->id,$car->id,'App\Models\Car');
                }
                if($pay_price==$paid_amount_pay){
                    $car->increment('results'); 
                }
            }
            return Response::json('ok', 200);    
    }
    public function getIndexCar()
    {
        $user_id =$_GET['user_id'] ?? '';
        $data =  Car::with('carmodel')->with('name')->with('color')->with('company')->with('client')->with('transactions');
        $type =$_GET['type'] ?? '';
        if($type){
            $data =    $data->where('results', $type);
        }
        if($type==0){
            $data =    $data->where('results', $type);
        }
        if($user_id){
            $data =    $data->where('client_id',  $user_id);
        }
        $data =$data->orderBy('no', 'DESC')->paginate(10)->toArray();
        return Response::json($data, 200);
    }
    public function getIndexCarSearch()
    {
        $term = $_GET['q']??'';
        $data =  Car::with('carmodel')->with('name')->with('color')->with('company')->with('client')->orwhere('vin', 'LIKE','%'.$term.'%')->orwhere('car_owner', 'LIKE','%'.$term.'%')->orwhere('car_type', 'LIKE','%'.$term.'%');
        $data =$data->orderBy('no', 'DESC')->paginate(10);
        return Response::json($data, 200);
    }
    public function addToBox()
    {
        $user_id = $_GET['user_id']??0;
        $desc=trans('text.addToBox').' '.($_GET['amount']??0).'$'.' || '.$_GET['note']??'';
        $this->accountingController->increaseWallet(($_GET['amount']??0), $desc,$this->mainAccount->id,$user_id,'App\Models\User',$user_id);
        return Response::json('ok', 200);    
    }
    public function withDrawFromBox()
    {
        $user_id = $_GET['user_id']??0;
        $desc=trans('text.withDrawFromBox').' '.($_GET['amount']??'').'$'.' || '.$_GET['note']??'';
        $this->accountingController->decreaseWallet(($_GET['amount']??0), $desc,$this->mainAccount->id,$user_id,'App\Models\User',$user_id);
        
        return Response::json('ok', 200);    
    }
    public function addPaymentCar()
    {
        $user_id = $_GET['user_id']??0;
        $car_id = $_GET['car_id']??0;
        $amount=$_GET['amount']??0;
        $car = Car::find($car_id);
        $car->increment('paid_amount_pay',$amount);
        $wallet = Wallet::where('user_id',$car->client_id)->first();
        $desc=trans('text.addPayment').' '.$amount.'$'.' || '.$_GET['note']??'';
        $this->accountingController->increaseWallet($amount, $desc,$this->mainAccount->id,$car_id,'App\Models\Car',$user_id);
        $this->accountingController->increaseWallet($amount, $desc,$this->inAccount->id,$car_id,'App\Models\Car',$user_id);
        $this->accountingController->decreaseWallet($amount, $desc,$this->debtSupplier->id,$car_id,'App\Models\Car',$user_id);
        if($car->pay_price-$car->paid_amount_pay >= 0){
            $this->accountingController->decreaseWallet($amount, $desc,$this->debtAccount->id,$car->id,'App\Models\Car',$user_id);
            $wallet->decrement('balance',$amount); 
        }
        if($car->pay_price-$car->paid_amount_pay==0){
            $car->increment('results'); 
        }
        return Response::json('ok', 200);    
    }
    public function DelCar(Request $request){



        $car=Car::find($request->id);

        if(($car->client_id??0)&&($car->paid_amount??0)){
            $desc=' مرتج حذف سيارة'.$car->paid_amount;
            $wallet = Wallet::where('user_id',$car->client_id)->first();
            $wallet->decrement('balance',$car->pay_price-$car->paid_amount_pay);
            $this->accountingController->decreaseWallet($car->pay_price-$car->paid_amount_pay, $desc,$this->debtAccount->id,$car->id,'App\Models\Car');
        }
        if(($car->paid_amount??0)>0){
            $desc=' مرتج حذف سيارة'.$car->paid_amount;
            $this->accountingController->increaseWallet($car->paid_amount, $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
            $this->accountingController->increaseWallet($car->paid_amount, $desc,$this->inAccount->id,$car->id,'App\Models\Car');
            $this->accountingController->decreaseWallet($car->paid_amount, $desc,$this->outAccount->id,$car->id,'App\Models\Car' );

        }
        if(($car->purchase_price??0)-($car->paid_amount_pay??0)>0){
            $desc=' مرتج حذف سيارة'.$car->paid_amount_pay;
            $this->accountingController->decreaseWallet($car->purchase_price-$car->paid_amount_pay, $desc,$this->outSupplier->id,$car->id,'App\Models\Car');
            $this->accountingController->increaseWallet($car->purchase_price-$car->paid_amount_pay, $desc,$this->debtSupplier->id,$car->id,'App\Models\Car');
        }

        $car->delete();
        DB::statement('SET @row_number = 0');

        DB::table('car')
            ->whereNull('deleted_at') // Apply soft delete constraint
            ->orderBy('id') // Assuming 'id' is the primary key column
            ->update(['no' => DB::raw('(@row_number:=@row_number + 1)')]);
            
       

        return Response::json('delete is done', 200);    

        
    }
    
}
