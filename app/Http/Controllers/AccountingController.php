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
use App\Models\Transactions;
use App\Models\Results;
use App\Models\DoctorResults;
use App\Models\SystemConfig;
use App\Models\Wallet;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Transfers;
use App\Models\Car;
use App\Models\Company;
use App\Models\Name;
use App\Models\CarModel;
use App\Models\Color;
use App\Models\ExpensesType;
use App\Models\Expenses;


class AccountingController extends Controller
{
    public function __construct(){
        $this->url = env('FRONTEND_URL');
        $this->userAdmin =  UserType::where('name', 'admin')->first()->id;
        $this->userClient =  UserType::where('name', 'client')->first()->id;
        $this->userAccount =  UserType::where('name', 'account')->first()->id;
    
        $this->mainAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','main@account.com');
        $this->onlineContracts= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts');
        $this->onlineContractsDinar= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-dinar');
        $this->debtOnlineContracts= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-debt');
        $this->debtOnlineContractsDinar= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-debit-dinar');
        $this->howler= User::with('wallet')->where('type_id', $this->userAccount)->where('email','howler')->first();
        $this->shippingCoc= User::with('wallet')->where('type_id', $this->userAccount)->where('email','shipping-coc')->first();
        $this->border= User::with('wallet')->where('type_id', $this->userAccount)->where('email','border')->first();
        $this->iran= User::with('wallet')->where('type_id', $this->userAccount)->where('email','iran')->first();
        $this->dubai= User::with('wallet')->where('type_id', $this->userAccount)->where('email','dubai')->first();
    
        $this->mainBox= User::with('wallet')->where('type_id', $this->userAccount)->where('email','mainBox@account.com');

        $this->currentDate = Carbon::now()->format('Y-m-d');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function index()
    {  
        $owner_id=Auth::user()->owner_id;
        $boxes = User::with('wallet')->where('owner_id',$owner_id)->where('email', 'mainBox@account.com')->get();
        return Inertia::render('Accounting/Index', ['boxes'=>$boxes,'accounts'=>$this->mainAccount->where('owner_id',$owner_id)->first()]);
    }
    public function getIndexAccounting(Request $request)
    {
    $owner_id=Auth::user()->owner_id;
     $user_id = $_GET['user_id'] ?? 0;
     $from =  $_GET['from'] ?? 0;
     $to =$_GET['to'] ?? 0;
     $print =$_GET['print'] ?? 0;
     $q= $_GET['q'] ?? 0;
     $transactions_id = $_GET['transactions_id'] ?? 0;
     $user = User::with('wallet')->where('id',$user_id)->first();
     if($from && $to ){
         $transactions = Transactions ::with('morphed')->where('wallet_id', $user->wallet->id)->orderBy('id','desc')->whereBetween('created', [$from, $to]);
 
     }else{
         $transactions = Transactions ::with('morphed')->where('wallet_id', $user->wallet->id)->orderBy('id','desc');
     }
     if($q){
        $transactions = Transactions ::where('id', $q)->orWhere('description', 'LIKE','%'.$q.'%');
     }
     $allTransactions = $transactions->paginate(100);
 
     $sumAllTransactions = $allTransactions->where('currency','$')->sum('amount');
     $sumDebitTransactions = $allTransactions->where('currency','$')->where('type', 'debt')->sum('amount');
     $sumInTransactions = $allTransactions->where('currency','$')->where('type', 'in')->sum('amount');

     $sumAllTransactionsDinar = $allTransactions->where('currency','IQD')->sum('amount');
     $sumDebitTransactionsDinar = $allTransactions->where('currency','IQD')->where('type', 'debt')->sum('amount');
     $sumInTransactionsDinar = $allTransactions->where('currency','IQD')->where('type', 'in')->sum('amount');

     
     // Additional logic to retrieve client data
     $data = [
         'user' => $user,
         'transactions' => $allTransactions,
         'sum_transactions' => $sumAllTransactions,
         'sum_transactions_debit' => $sumDebitTransactions,
         'sum_transactions_in' => $sumInTransactions,
         'sum_transactions_dinar' => $sumAllTransactionsDinar,
         'sum_transactions_debit_dinar' => $sumDebitTransactionsDinar,
         'sum_transactions_in_dinar' => $sumInTransactionsDinar,
     ];
 
     if($print==1){
         $config=SystemConfig::first();
         return view('receiptPaymentTotal',compact('data','config'));
      }
      if($print==2){
         $config=SystemConfig::first();
 
         return view('receipt',compact('clientData','config','transactions_id'));
      }
      if($print==3){
         $config=SystemConfig::first();
 
         return view('receiptPayment',compact('clientData','config','transactions_id'));
      }
      if($print==4){
         $config=SystemConfig::first();
 
         return view('receiptPaymentTotal',compact('clientData','config','transactions_id'));
      }
 
      if($print==5){
        $config=SystemConfig::first();

        return view('receiptExpensesTotal.blade',compact('clientData','config','transactions_id'));
     }
     return response()->json($data); 
     }
     public function salesDebt(Request $request)
     {
      $owner_id=Auth::user()->owner_id;
      $user_id= $request->user['id']??0;
      $note= $request->note??'';
      $amountDollar= $request->amountDollar??0;
      $amountDinar= $request->amountDinar??0;

      $desc=" سحب دفعة  ".' '.$note;
      $date= $request->date??0;
      if($amountDollar){
        $transaction=$this->debtWallet($amountDollar,$desc,$this->mainBox->where('owner_id',$owner_id)->first()->id,$this->mainBox->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'$',$date);
      }
      if($amountDinar)
      {
        $transaction=$this->debtWallet($amountDinar,$desc,$this->mainBox->where('owner_id',$owner_id)->first()->id,$this->mainBox->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'IQD',$date);
      }

  
      return Response::json($request, 200);
  
      }
      public function receiptArrived(Request $request)
      {
       $owner_id=Auth::user()->owner_id;
       $note= $request->amountNote??'';
       $amountDollar= $request->amountDollar??0;
       $amountDinar= $request->amountDinar??0;
       $desc="وصل قبض مباشر"." ".' '.$note;
       $date= $request->date??0;
       if($amountDollar){
        $transaction=$this->increaseWallet($amountDollar,$desc,$this->mainBox->where('owner_id',$owner_id)->first()->id,$this->mainBox->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'$',$date);
       }
       if($amountDinar){

        $transaction=$this->increaseWallet($amountDinar,$desc,$this->mainBox->where('owner_id',$owner_id)->first()->id,$this->mainBox->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'IQD',$date);
       }

       return Response::json($transaction, 200);
   
       }

    public function getIndexAccountsSelas()
    { 
        $owner_id=Auth::user()->owner_id;
        $user_id = $_GET['user_id'] ?? 0;
        $from =  $_GET['from'] ?? 0;
        $to =$_GET['to'] ?? 0;
        $print =$_GET['print'] ?? 0;
        $showComplatedCars=$_GET['showComplatedCars'] ?? 0;
        $transactions_id = $_GET['transactions_id'] ?? 0;
        $client = User::with('wallet')->where('id', $user_id)->first();
        if($from && $to ){
            $contract=Contract::where('user_id',$user_id)->whereBetween('created', [$from, $to]);
            $transactions = Transactions ::where('wallet_id', $client?->wallet?->id)->whereBetween('created', [$from, $to]);
            $cars = Car::with('contract')->with('exitcar')->where('client_id',$client->id)->whereBetween('date', [$from, $to]);
            $car_total = $cars->count();
            $car_total_unpaid =     Car::where('client_id',$client->id)->where('results',0)->whereBetween('date', [$from, $to])->count();
            $car_total_uncomplete = Car::where('client_id',$client->id)->where('results',1)->whereBetween('date', [$from, $to])->count();
            $car_total_complete =   Car::where('client_id',$client->id)->where('results',2)->whereBetween('date', [$from, $to])->count();
            $cars_discount=   Car::where('client_id',$client->id)->whereBetween('date', [$from, $to])->sum('discount');
            $cars_paid=   Car::where('client_id',$client->id)->whereBetween('date', [$from, $to])->sum('paid');
            $cars_sum=   Car::where('client_id',$client->id)->whereBetween('date', [$from, $to])->sum('total_s');
            $contract_total=   Car::where('client_id',$client->id)->whereBetween('date', [$from, $to])->where('contract_id','!=',0)->count();
            $exit_car_total=   Car::where('client_id',$client->id)->whereBetween('date', [$from, $to])->where('is_exit','!=',0)->count();
            $contract_total_debit_Dollar=($contract->sum('price')-$contract->sum('paid'))??0;
            $contract_total_debit_Dinar=($contract->sum('price_dinar')-$contract->sum('paid_dinar'))??0;
            $cars_need_paid=$cars_sum-($cars_paid+$cars_discount);
        }else{
            $contract=Contract::where('user_id',$user_id);
            $transactions = Transactions ::where('wallet_id', $client?->wallet?->id);
            $cars =  Car::with('contract')->with('exitcar')->where('client_id',$client->id);
            $car_total = $cars->count();
            $car_total_unpaid =     Car::where('client_id',$client->id)->where('results',0)->count();
            $car_total_uncomplete = Car::where('client_id',$client->id)->where('results',1)->count();
            $car_total_complete =   Car::where('client_id',$client->id)->where('results',2)->count();
            $cars_discount=Car::where('client_id',$client->id)->sum('discount');
            $cars_paid=   Car::where('client_id',$client->id)->sum('paid');
            $cars_sum=   Car::where('client_id',$client->id)->sum('total_s');
            $contract_total=   Car::where('client_id',$client->id)->where('contract_id','!=',0)->count();
            $exit_car_total=   Car::where('client_id',$client->id)->where('is_exit','!=',0)->count();
            $contract_total_debit_Dollar=($contract->sum('price')-$contract->sum('paid'))??0;
            $contract_total_debit_Dinar=($contract->sum('price_dinar')-$contract->sum('paid_dinar'))??0;
            $cars_need_paid=$cars_sum-($cars_paid+$cars_discount);
        }
        //$data = $transactions->paginate(10);
 

        if($print==1){
            if($showComplatedCars==1){
                $clientData = [
                    'totalAmount' =>   $transactions->sum('amount'),
                    'data' => $cars->where('results','!=','2')->get(),
                    'client'=>$client,
                    'car_total'=>$cars->where('results','!=','2')->count(),
                    'car_total_unpaid'=>$car_total_unpaid,
                    'car_total_complete'=>$car_total_complete,
                    'car_total_uncomplete'=>$car_total_uncomplete,
                    'contract_total'=>$contract_total,
                    'exit_car_total'=>$exit_car_total,
                    'contract_total_debit_Dollar'=>$contract_total_debit_Dollar,
                    'contract_total_debit_Dinar'=>$contract_total_debit_Dinar,
                    'cars_sum'=>$cars_sum,
                    'cars_paid'=>$cars_paid,
                    'cars_discount'=>$cars_discount,
                    'cars_need_paid'=>$cars_need_paid,
                    'transactions'=>$transactions->get(),
                    'date'=> Carbon::now()->format('Y-m-d')
                ];
            }else{
                $clientData = [
                    'totalAmount' =>   $transactions->sum('amount'),
                    'data' => $cars->get(),
                    'client'=>$client,
                    'car_total'=>$car_total,
                    'car_total_unpaid'=>$car_total_unpaid,
                    'car_total_complete'=>$car_total_complete,
                    'car_total_uncomplete'=>$car_total_uncomplete,
                    'contract_total'=>$contract_total,
                    'exit_car_total'=>$exit_car_total,
                    'contract_total_debit_Dollar'=>$contract_total_debit_Dollar,
                    'contract_total_debit_Dinar'=>$contract_total_debit_Dinar,
                    'cars_sum'=>$cars_sum,
                    'cars_paid'=>$cars_paid,
                    'cars_discount'=>$cars_discount,
                    'cars_need_paid'=>$cars_need_paid,
                    'transactions'=>$transactions->get(),
                    'date'=> Carbon::now()->format('Y-m-d')
                ];
            }

            $config=SystemConfig::first();

            return view('show',compact('clientData','config'));
         }
                 // Additional logic to retrieve client data
        $clientData = [
            'totalAmount' =>   $transactions->sum('amount'),
            'data' => $cars->get(),
            'client'=>$client,
            'car_total'=>$car_total,
            'car_total_unpaid'=>$car_total_unpaid,
            'car_total_complete'=>$car_total_complete,
            'car_total_uncomplete'=>$car_total_uncomplete,
            'contract_total'=>$contract_total,
            'exit_car_total'=>$exit_car_total,
            'contract_total_debit_Dollar'=>$contract_total_debit_Dollar,
            'contract_total_debit_Dinar'=>$contract_total_debit_Dinar,
            'cars_sum'=>$cars_sum,
            'cars_paid'=>$cars_paid,
            'cars_discount'=>$cars_discount,
            'cars_need_paid'=>$cars_need_paid,
            'transactions'=>$transactions->get(),
            'date'=> Carbon::now()->format('Y-m-d')
        ];

         if($print==2){
            $config=SystemConfig::first();

            return view('receipt',compact('clientData','config','transactions_id'));
         }
         if($print==3){
            $config=SystemConfig::first();

            return view('receiptPayment',compact('clientData','config','transactions_id'));
         }
         if($print==4){
            $config=SystemConfig::first();
            return view('receiptPaymentTotal',compact('clientData','config','transactions_id'));
         }
         if($print==5){
            $config=SystemConfig::first();
    
            return view('receiptExpensesTotal',compact('clientData','config','transactions_id'));
         }
        return Response::json($clientData, 200);
    }
    public function paySelse(Request $request,$id)
    {
        try {
            DB::beginTransaction();
            // Perform your database operations with Eloquent
            $user=  User::with('wallet')->find($id);
            $transactions =Transactions ::where('wallet_id', $user?->wallet?->id)->where('is_pay',0);
            $amount=$transactions->sum('amount');
            $transactions->update(['is_pay' => 1]);
            $profile_count = Profile::where('user_id', $user?->id)->where('results',1)->update(['results' => 2]);
            $this->decreaseWallet($amount*-1,' تسليم مبلغ '.$amount.' دينار عراقي ',$user->id);
            // If everything is successful, commit the transaction
            DB::commit();
            // Return a response or perform other actions
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();
            // Handle the exception or return an error response
        }
        return Response::json('ok', 200);

    }
    public function addPaymentCar()
    {
        $owner_id=Auth::user()->owner_id;
        $user_id = $_GET['user_id']??0;
        $car_id = $_GET['car_id']??0;
        $amount=$_GET['amount']??0;
        $discount = $_GET['discount']??0;
        $note = $_GET['note'] ?? '';
        $car = Car::find($car_id);
        $car->increment('paid',$amount);
        $car->increment('discount',$discount);
        $wallet = Wallet::where('user_id',$car->client_id)->first();
        $desc=trans('text.addPayment').' '.$amount.' '.$car->car_type.' رقم الشانص'.$car->vin.' '.$note;
        $tran=$this->increaseWallet($amount,$desc,$this->mainBox->where('owner_id',$owner_id)->first()->id,$this->mainBox->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'$');
        $this->increaseWallet($amount, $desc,$this->mainAccount->where('owner_id',$owner_id)->first()->id,$car_id,'App\Models\User',1,$discount,'$',$this->currentDate,$tran->id);
        $transaction=$this->decreaseWallet($amount+$discount, $desc,$car->client_id,$car_id,'App\Models\User',1,$discount,'$',$this->currentDate,$tran->id);
        if((($car->paid)+($car->discount))-$car->total_s >= 0){
            $car->update(['results'=>2]); 
        }
        elseif($amount){
            $car->update(['results'=>1]); 
        }
        return Response::json($transaction, 200);    
    }
    public function addPaymentCarTotal()
    {
        $owner_id=Auth::user()->owner_id;
        $client_id  = $_GET['client_id']  ??0;
        $amount_o  = $_GET['amount']  ??0;
        $note = $_GET['note'] ?? '';
        $discount= $_GET['discount']  ??0;
        $amount  = $_GET['amount']   ??0;
        $paided =false;
        $client= User::with('wallet')->find($client_id);

        $cars = Car::where('client_id',$client_id)->where('total_s','!=',0)->whereIn('results',[0, 1]);
        $carLast = Car::where('client_id',$client_id)->where('total_s','!=',0)->whereIn('results',[0, 1])->latest()->first();
        $needToPay=0;
        $user_id=$_GET['user_id']??0;
        $carsName = '';
        if(($client->wallet->balance -((int)$amount_o +(int)$discount))==0){
        $amount= (int)$cars->sum('total_s') - (int)$cars->sum('discount');
        foreach ($cars->get() as $car) {
            $paided = true;
            $needToPay = $car->total_s - ($car->paid + $car->discount);
            $carsName = $car->car_type.' '.$carsName;
            if ($needToPay <= $amount) {
                // Deduct the amount and update 'paid' for this car
                $amount -= $needToPay;
                $car->update(['paid' => $car->total_s-$car->discount,'results' =>2]);
  
            } else {
                if($needToPay <= $amount+$discount){
                    $car->update(['paid' => $car->paid + $amount,'results' =>2]);
                    $amount = 0;
                    break; // Stop processing if the amount is exhausted
                }else{
                    $car->update(['paid' => $car->paid + $amount,'results' =>1]);
                    $amount = 0;
                    break; // Stop processing if the amount is exhausted 
                }


            }

           
        }
        if($discount){
            $carLast->decrement('paid',$discount);
            $carLast->increment('discount',$discount);
            }
        }else{
            $carLast->increment('discount',$discount);
        }
        if($amount_o){
            $desc=trans('text.addPayment').' '.$amount_o.' '.$note;

            $tran=$this->increaseWallet($amount_o,$desc,$this->mainBox->where('owner_id',$owner_id)->first()->id,$this->mainBox->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'$');
    
            $this->increaseWallet($amount_o, $desc,$this->mainAccount->where('owner_id',$owner_id)->first()->id,$this->mainAccount->where('owner_id',$owner_id)->first()->id,'App\Models\User',1,$discount,'$',$this->currentDate,$tran->id);
    
            $transaction = $this->decreaseWallet((int)$amount_o+(int)$discount, $desc,$client_id,$client_id,'App\Models\User',1,$discount,'$',$this->currentDate,$tran->id);
            return Response::json($transaction, 200);    
        }
        return Response::json('ok', 200);    
      

       
    }
    public function getGenExpenses (Request $request){
        $expenses = Expenses::where('expenses_type_id',$request->expenses_type_id)->get();

        return Response::json($expenses, 200);    

    }
    public function GenExpenses (Request $request){
        $owner_id=Auth::user()->owner_id;

        $factor=$request->factor ?? 1;
        $amount=(($request->amount)/ $factor);
        $expenses_type_id = $request->expenses_type_id;
        $reason=$request->note ?? '';
        if($expenses_type_id==1){
            $user_id=$this->howler->id;
            $desc='مصاريف أربيل مبلغ '.' '.($request->amount).'بسعر صرف'.' '.$factor.' '.$reason;
        }
        if($expenses_type_id==2){
            $user_id=$this->dubai->id;
            $desc='مصاريف دبي مبلغ '.' '.($request->amount).'بسعر صرف'.' '.$factor.' '.$reason;
        }
        if($expenses_type_id==3){
            $desc='مصاريف ايران مبلغ '.' '.($request->amount).'بسعر صرف'.' '.$factor.' '.$reason;
            $user_id=$this->iran->id;
        }
        if($expenses_type_id==4){
            $desc='مصاريف الحدود مبلغ '.' '.($request->amount).'بسعر صرف'.' '.$factor.' '.$reason;
            $user_id=$this->border->id;
        }
        if($expenses_type_id==5){
            $desc='مصاريف شهادة coc مبلغ '.' '.($request->amount).'بسعر صرف'.' '.$factor.' '.$reason;
            $user_id=$this->shippingCoc->id;
        }
        $tran=$this->decreaseWallet($amount,$desc,$this->mainBox->where('owner_id',$owner_id)->first()->id,$this->mainBox->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'$');
        $transaction=$this->increaseWallet($amount, $desc,$user_id,$user_id,'App\Models\User',1,0,'$',$this->currentDate,$tran->id);
        $expenses = Expenses::create([
            'factor' => $factor,
            'amount' => ($request->amount)/ $factor ?? 0,
            'reason' => $reason,
            'expenses_type_id'=>$expenses_type_id,
            'transaction_id' =>  $transaction->id,
            'user_id' => $user_id
        ]);

        return Response::json($transaction, 200);    

    }
    public function convertDollarDinar(Request $request){
        $owner_id=Auth::user()->owner_id;
        $amountDollar =$request->amountDollar;
        $amountResultDinar =$request->amountResultDinar;
        $exchangeRate =$request->exchangeRate;
        $date=$request->date??0;
        $desc=' تحويل من الصندوق مبلغ بالدولار'.' '.($amountDollar).'  بسعر صرف '.' '.$exchangeRate.' المبلغ المضاف للصندوف بالدينار '.$amountResultDinar;
        if($amountDollar){
            $transactionDollar=$this->decreaseWallet($amountDollar,$desc,$this->mainBox->where('owner_id',$owner_id)->first()->id,$this->mainBox->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'$',$date);
          }
          if($amountResultDinar)
          {
            $transactionDinar=$this->increaseWallet($amountResultDinar,$desc,$this->mainBox->where('owner_id',$owner_id)->first()->id,$this->mainBox->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'IQD',$date);
          }
          
          $transactionDollar->update(['parent_id'=>$transactionDinar->id]);
          $transactionDinar->update(['parent_id'=>$transactionDollar->id]);
          return Response::json($transactionDinar, 200);    

    }
    public function convertDinarDollar(Request $request){
        $owner_id=Auth::user()->owner_id;
        $amountDinar =$request->amountDinar;
        $amountResultDollar =$request->amountResultDollar;
        $exchangeRate =$request->exchangeRate;
        $date=$request->date??0;
        $desc=' تحويل من الصندوق مبلغ بالدينار'.' '.($amountDinar).'  بسعر صرف '.' '.$exchangeRate.' المبلغ المضاف للصندوف بالدولار '.$amountResultDollar;
        if($amountResultDollar){
            $transactionDollar= $this->increaseWallet($amountResultDollar,$desc,$this->mainBox->where('owner_id',$owner_id)->first()->id,$this->mainBox->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'$',$date);
          }
          if($amountDinar)
          {
            $transactionDinar= $transaction=$this->decreaseWallet($amountDinar,$desc,$this->mainBox->where('owner_id',$owner_id)->first()->id,$this->mainBox->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'IQD',$date);
          }
          $transactionDollar->update(['parent_id'=>$transactionDinar->id]);
          $transactionDinar->update(['parent_id'=>$transactionDollar->id]);
          return Response::json($transactionDinar, 200);    

    }
    public function receiveCard(Request $request)
    {
        $authUser = auth()->user();

        $profile_id = $_GET['id'] ?? 0;

        $profile = Profile::find($profile_id);

        $wallet = Wallet::where('user_id', $profile->user_id)->first();

        $card = Card::find($profile->card_id);

        $user = User::find($profile->user_id);

        $old_card = $wallet->card; 

        $old_balance = $wallet->balance;

        $card_price = $card->price;

        $percentage = $user->percentage;

        $new_balance =  $old_balance + $percentage;

        try {
            DB::beginTransaction();

            $profile->update(['results'=>1,'user_accepted'=>$authUser->id]);
            $this->increaseWallet($percentage,' نسبة على البطاقة رقم '.$profile?->card_number,$user->id);
            $wallet->update(['card' => $old_card-1,'balance'=>$new_balance]);

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();

        }

        return Response::json($new_balance, 200);

    }
    public function increaseWallet(int $amount,$desc,$user_id,$morphed_id='',$morphed_type='',$is_pay=0,$discount=0,$currency='$',$created=0,$parent_id=0) 
    {
        if($amount){
            if($created==0){
                $created=$this->currentDate;
            }
            $user=  User::with('wallet')->find($user_id);
            if($id = $user->wallet->id){
            $transactionDetils = ['type' => 'in','wallet_id'=>$id,'description'=>$desc,'amount'=>$amount,'is_pay'=>$is_pay,'morphed_id'=>$morphed_id,'morphed_type'=>$morphed_type,'user_added'=>0,'created'=>$created,'discount'=>$discount,'currency'=>$currency,'parent_id'=>$parent_id];
            $transaction = Transactions::create($transactionDetils);
            $wallet = Wallet::find($id);
            if($currency=='IQD'){
                $wallet->increment('balance_dinar', $amount);
            }else{
                $wallet->increment('balance', $amount);
            }
            }
            if (is_null($wallet)) {
                return null;
            }
            // Finally return the updated wallet.
            return $transaction;
        }else{
            return 0 ;
        }

    }

    public function decreaseWallet(int $amount,$desc,$user_id,$morphed_id=0,$morphed_type='',$is_pay=0,$discount=0,$currency='$',$created=0,$parent_id=0) 
    {
        if($amount){
        if($created==0){
            $created=$this->currentDate;
        }
        $user=  User::with('wallet')->find($user_id);
        if($id = $user->wallet->id){
        $wallet = Wallet::find($id);
        $transactionDetils = ['type' => 'out','wallet_id'=>$id,'description'=>$desc,'amount'=>$amount*-1,'is_pay'=>$is_pay,'morphed_id'=>$morphed_id,'morphed_type'=>$morphed_type,'user_added'=>0,'created'=>$created,'discount'=>$discount,'currency'=>$currency,'parent_id'=>$parent_id];
        $transaction =Transactions::create($transactionDetils);
        if($currency=='IQD'){
            $wallet->decrement('balance_dinar', $amount);
        }else{
            $wallet->decrement('balance', $amount);
        }

        }
        if (is_null($wallet)) {
            return null;
        }
        // Finally return the updated wallet.
        return $transaction;
        }else{
            return 0 ;
        }
    }
    public function debtWallet(int $amount,$desc,$user_id,$morphed_id=0,$morphed_type='',$is_pay=0,$discount=0,$currency='$',$created=0,$parent_id=0)  
    {

        if($created==0){
            $created=$this->currentDate ;
        }
        $user=  User::with('wallet')->find($user_id);
        if($id = $user->wallet->id){
        $wallet = Wallet::find($id);
        if($currency=='IQD'){
            $wallet->decrement('balance_dinar', $amount);
        }else{
            $wallet->decrement('balance', $amount);
        }
            $transactionDetils = ['type' => 'debt','wallet_id'=>$id,'description'=>$desc,'amount'=>$amount*-1,'is_pay'=>$is_pay,'morphed_id'=>$morphed_id,'morphed_type'=>$morphed_type,'user_added'=>0,'created'=>$created,'discount'=>$discount,'currency'=>$currency,'parent_id'=>$parent_id];

            $Transactions =Transactions::create($transactionDetils);
         
        
        }
        if (is_null($wallet)) {
            return null;
        }
        // Finally return the updated wallet.
        return $Transactions;
    }
    
    // public function deleteTransactions(Request $request){
    //     $transactions = Transactions::find($request->id);
    //     $amount=$transactions->amount;
    //     if($amount>0){
    //         $amount=$amount;
    //     }else{
    //         $amount=$amount*1;
    //     }
    //     $desc="مرتجع دفعة";
    //     $wallet = Wallet::find($transactions->wallet_id);
    //     if($amount>0){
    //         $this->increaseWallet($amount, $desc,$wallet->user_id,$wallet->user_id,'App\Models\User',0);
    //     }else{
    //         $this->decreaseWallet($amount, $desc,$wallet->user_id,$wallet->user_id,'App\Models\User',0);
    //     }
    //     $transactions->delete();
    //     return Response::json('ok', 200);    
    // }
    public function delTransactions(Request $request)
    {
        $owner_id=Auth::user()->owner_id;
        $transaction_id = $request->id ?? 0;
        $originalTransaction = Transactions::find($transaction_id);
        $wallet_id=$originalTransaction->wallet_id;
        $refundTransaction = 'مرتجع حذف حركة';

        $wallet=Wallet::find($wallet_id);
        if (!$originalTransaction) {
          return response()->json(['message' => 'Transaction not found'], 404);
          }
        if($originalTransaction->currency=='$'){
          $wallet->decrement('balance', $originalTransaction->amount);
          $all=  Transactions::where('parent_id',$transaction_id)->get();

          $firstTransaction=Transactions::where('parent_id',$transaction_id)->first();
          if ($all->isNotEmpty()) { // Check if there are records in the collection
            foreach ($all as $transaction) {
                if($transaction->currency=='$'){
                    $wallet_id = $transaction->wallet_id;
                    $wallet = Wallet::find($wallet_id);
                    $wallet->decrement('balance', $transaction->amount);
                    $transaction->delete();
                }
                if($transaction->currency=='IQD'){
                    $wallet_id = $transaction->wallet_id;
                    $wallet = Wallet::find($wallet_id);
                    $wallet->decrement('balance_dinar', $transaction->amount);
                    $transaction->delete();
                }
            }
        }
        }
        if($originalTransaction->currency=='IQD'){
          $wallet->decrement('balance_dinar', $originalTransaction->amount);
          $all=  Transactions::where('parent_id',$transaction_id)->get();
          $firstTransaction=Transactions::where('parent_id',$transaction_id)->first();

          if ($all->isNotEmpty()) { // Check if there are records in the collection
            foreach ($all as $transaction) {
                if($transaction->currency=='$'){
                    $wallet_id = $transaction->wallet_id;
                    $wallet = Wallet::find($wallet_id);
                    $wallet->decrement('balance', $transaction->amount);
                    $transaction->delete();
                }
                if($transaction->currency=='IQD'){
                    $wallet_id = $transaction->wallet_id;
                    $wallet = Wallet::find($wallet_id);
                    $wallet->decrement('balance_dinar', $transaction->amount);
                    $transaction->delete();
                }
            }
        }

        }
        $walletExpensesIds = [
            $this->howler->wallet->id,
            $this->shippingCoc->wallet->id,
            $this->border->wallet->id,
            $this->iran->wallet->id,
            $this->dubai->wallet->id,
        ];
        if (in_array($wallet_id, $walletExpensesIds)) {
            $expenses = Expenses::where('transaction_id',$firstTransaction->id);
            $expenses->delete();
        }
        $walletContractsIds = [
            $this->onlineContracts->where('owner_id',$owner_id)->first()->wallet->id,
            $this->onlineContractsDinar->where('owner_id',$owner_id)->first()->wallet->id,
            $this->debtOnlineContracts->where('owner_id',$owner_id)->first()->wallet->id,
            $this->debtOnlineContractsDinar->where('owner_id',$owner_id)->first()->wallet->id
        ];
        if (in_array($wallet_id, $walletContractsIds)) {
            $refundTransaction = 'مرتجع حذف حركة';
            $contract = Contract::where('car_id',$firstTransaction->morphed_id)->first();
            if($firstTransaction->currency=='$'){
                $this->increaseWallet($firstTransaction->amount, $refundTransaction,$this->debtOnlineContracts->where('owner_id',$owner_id)->first()->id,$firstTransaction->id,'App\Models\Car',0,0,'$',0);
                $contract->decrement('paid', $firstTransaction->amount);
            }
            if($firstTransaction->currency=='IQD'){
                $this->increaseWallet($firstTransaction->amount, $refundTransaction,$this->debtOnlineContractsDinar->where('owner_id',$owner_id)->first()->id,$firstTransaction->id,'App\Models\Car',0,0,'IQD',0);
                $contract->decrement('paid_dinar', $firstTransaction->amount);
            }
        }
        // // Create a new transaction for the refund
        // $refundTransaction = new Transactions();
        // $refundTransaction->wallet_id = $originalTransaction->wallet_id;
        // $refundTransaction->morphed_id = $originalTransaction->morphed_id;
        // $refundTransaction->morphed_type = $originalTransaction->morphed_type;
        // $refundTransaction->created =$this->currentDatef;
        // $refundTransaction->type = 'refund'; // Assuming you have a 'refund' transaction type
        // $refundTransaction->amount = -$originalTransaction->amount; // Make the refund negative
        // $refundTransaction->save();
    
        // Delete the original transaction
        $originalTransaction->delete();
    
        return response()->json(['message' => $all], 200);
    }
    }