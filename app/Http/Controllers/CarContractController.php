<?php

namespace App\Http\Controllers;
use App\Http\Controllers\AccountingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transfers;
use App\Models\User;
use App\Models\Car;
use App\Models\Driving;
use App\Models\Wallet;
use App\Models\UserType;
use App\Models\ExpensesType;
use Illuminate\Support\Facades\DB;
use App\Models\Transactions;
use App\Models\Expenses;
use App\Models\CarExpenses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\SystemConfig;
use App\Models\CarContract;
use App\Models\TransactionsContract;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CarContractController extends Controller
{
    protected bool $showBrokerage;

    public function __construct(AccountingController $accountingController)
    {
        $this->accountingController = $accountingController;
        $this->userClient = $this->resolveUserTypeId('client');
        $this->userAccount = $this->resolveUserTypeId('account');
        $this->userCarContract = $this->resolveUserTypeId('car_contract');
        $this->userCarContractUser = $this->resolveUserTypeId('car_contract_user');
        $this->mainBoxContract = User::with('wallet')
            ->where('type_id', $this->userAccount)
            ->where('email', 'mainBoxContract@account.com');
        $this->currentDate = Carbon::now()->format('Y-m-d');
        $this->showBrokerage = filter_var(config('car_contract.show_brokerage', false), FILTER_VALIDATE_BOOLEAN);
    }

    protected function resolveUserTypeId(string $typeName, ?int $default = null): ?int
    {
        $id = UserType::where('name', $typeName)->value('id');

        if (!$id && $default !== null) {
            return $default;
        }

        if (!$id) {
            Log::warning('UserType missing for contracts module', ['type' => $typeName]);
        }

        return $id;
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
        return Inertia::render('CarContract/add', [
            'client1'=>$client1,
            'data'=>$data,
            'client2'=>$client2,
            'showBrokerage' => $this->showBrokerage,
        ]);   
    }
    public function contract_print(Request $request)
    {
        $id=$request->id;
        $data = CarContract::find($id);
        if ($data && empty($data->verification_token)) {
            $data->verification_token = Str::uuid()->toString();
            $data->save();
        }
        $owner_id=Auth::user()->owner_id;
        $client = User::where('type_id', $this->userClient)->where('owner_id',$owner_id)->get();
        $config = SystemConfig::first();
        $verificationUrl = $data ? route('contract.verify', $data->verification_token) : null;
        $template = (int) ($request->query('template') ?? $config->contract_template ?? 1);
        $viewName = $template === 2 ? 'receiptContract2' : 'receiptContract';
        $contractOrganizer = Auth::user()->name ?? ($config->contract_organizer_name ?? '');
        return view($viewName, compact('data', 'config', 'verificationUrl', 'contractOrganizer'));
    }
    public function index(Request $request)
    {
        $owner_id=Auth::user()->owner_id;
        $client = User::where('type_id', $this->userClient)->where('owner_id',$owner_id)->get();
        $q= $_GET['q'] ?? '';
        // المستخدمون الذين أنشأوا عقوداً (للفلتر)
        $creatorIds = CarContract::where('owner_id', $owner_id)->whereNotNull('user_id')->distinct()->pluck('user_id');
        $contractCreators = User::whereIn('id', $creatorIds)->orderBy('name')->get(['id', 'name']);
        return Inertia::render('CarContract/index', [
            'client'=>$client,
            'user'=>$q,
            'contractCreators'=>$contractCreators,
            'showBrokerage' => $this->showBrokerage,
        ]);
    }
    public function contract_account(Request $request)
    {
        $owner_id=Auth::user()->owner_id;
        $client = User::where('type_id', $this->userClient)->where('owner_id',$owner_id)->get();
        return Inertia::render('CarContract/account', [
            'client'=>$client,
            'showBrokerage' => $this->showBrokerage,
        ]);   
    }
 
    public function addCarContract(Request $request)
    {
        $raw = $request->all();
        // Strip offline-only keys so they are not saved to DB
        $offlineOnlyKeys = ['_id', '_offline', '_createdAt', '_status', '_lastAttempt', '_retryCount', '_timestamp'];
        $contract = array_diff_key($raw, array_flip($offlineOnlyKeys));

        $owner_id = Auth::user()->owner_id;
        $user_id = Auth::user()->id;
        $year_date = Carbon::now()->format('Y');
        $created = Carbon::now()->format('Y-m-d');

        $tex_seller = $request->tex_seller;
        $tex_seller_dinar = $request->tex_seller_dinar;
        $tex_buyer = $request->tex_buyer;
        $tex_buyer_dinar = $request->tex_buyer_dinar;
        $tex_seller_paid = $request->tex_seller_paid;
        $tex_seller_dinar_paid = $request->tex_seller_dinar_paid;
        $tex_buyer_paid = $request->tex_buyer_paid;
        $tex_buyer_dinar_paid = $request->tex_buyer_dinar_paid;

        $status = 2;
        if ($tex_seller != $tex_seller_paid || $tex_seller_dinar != $tex_seller_dinar_paid || $tex_buyer != $tex_buyer_paid || $tex_buyer_dinar != $tex_buyer_dinar_paid) {
            $status = 1;
        }
        if ($tex_seller_paid == 0 && $tex_seller_dinar_paid == 0 && $tex_buyer_paid == 0 && $tex_buyer_dinar_paid == 0) {
            $status = 0;
        }
        $contract['status'] = $status;
        $contract['owner_id'] = $owner_id;
        $contract['user_id'] = $user_id;
        $contract['year_date'] = $year_date;
        $contract['created'] = $created;

        $hasUuidColumn = Schema::hasColumn('car_contract', 'uuid');
        $requestUuid = $request->input('uuid');
        if (is_string($requestUuid)) {
            $requestUuid = trim($requestUuid);
        }
        if (empty($requestUuid)) {
            $requestUuid = null;
        }

        if ($hasUuidColumn && $requestUuid) {
            $oldContract = CarContract::where('uuid', $requestUuid)->where('owner_id', $owner_id)->first();
        } else {
            $oldContract = CarContract::find($contract['id'] ?? 0);
        }

        if ($oldContract) {
            $contractId = (int) $oldContract->id;
            $contract['id'] = $contractId;
            $contract['created'] = (new Carbon($oldContract->created_at))->format('Y-m-d');
            if ($hasUuidColumn && $requestUuid) {
                $contract['uuid'] = $requestUuid;
            }
            $desc = 'تم تعديل عقد بيع للسيارة ' . ($contract['car_name']) . ' البائع ' . ($contract['name_seller'] ?? 0) . ' دفع مبلغ ' . ($contract['tex_seller_paid'] ?? 0) . ' و المشتري ' . ($contract['tex_buyer_paid'] ?? 0) . ' دفع مبلغ ' . ($contract['name_buyer'] ?? 0) . ' رقم' . ($contract['vin']);
            $this->handlePaymentChange($contract['tex_seller_paid'], $oldContract['tex_seller_paid'], '$', $desc, $contractId, 'seller');
            $this->handlePaymentChange($contract['tex_buyer_paid'], $oldContract['tex_buyer_paid'], '$', $desc, $contractId, 'buyer');
            $this->handlePaymentChange($contract['tex_seller_dinar_paid'], $oldContract['tex_seller_dinar_paid'], 'IQD', $desc, $contractId, 'seller');
            $this->handlePaymentChange($contract['tex_buyer_dinar_paid'], $oldContract['tex_buyer_dinar_paid'], 'IQD', $desc, $contractId, 'buyer');
        } else {
            if ($hasUuidColumn) {
                $contract['uuid'] = $requestUuid ?: Str::uuid()->toString();
            }
            if (isset($contract['id']) && (int) $contract['id'] === 0) {
                unset($contract['id']);
            }
        }

        $car = CarContract::updateOrCreate(
            ['id' => $contract['id'] ?? 0],
            $contract
        );

        if ($car && empty($car->verification_token)) {
            $car->verification_token = Str::uuid()->toString();
            $car->save();
        }

        if (!$oldContract) {
            $desc = ' عقد بيع للسيارة ' . ($contract['car_name']) . ' البائع ' . ($contract['name_seller'] ?? 0) . ' دفع مبلغ ' . ($contract['tex_seller_paid'] ?? 0) . ' و المشتري ' . ($contract['tex_buyer_paid'] ?? 0) . ' دفع مبلغ ' . ($contract['name_buyer'] ?? 0) . ' رقم' . ($contract['vin']);
            if (($contract['tex_seller_paid'] ?? 0) || ($contract['tex_buyer_paid'] ?? 0)) {
                $this->increaseWallet(($contract['tex_seller_paid'] ?? 0) + ($contract['tex_buyer_paid'] ?? 0), $desc, $this->mainBoxContract->where('owner_id', $owner_id)->first()->id, $car->id, 'App\Models\CarContract', 0, 0, '$', 0, 0, 'in', ($contract['tex_seller_paid'] ?? 0), ($contract['tex_buyer_paid'] ?? 0));
            }
            if (($contract['tex_seller_dinar_paid'] ?? 0) || ($contract['tex_buyer_dinar_paid'] ?? 0)) {
                $this->increaseWallet(($contract['tex_seller_dinar_paid'] ?? 0) + ($contract['tex_buyer_dinar_paid'] ?? 0), $desc, $this->mainBoxContract->where('owner_id', $owner_id)->first()->id, $car->id, 'App\Models\CarContract', 0, 0, 'IQD', 0, 0, 'in', ($contract['tex_seller_paid'] ?? 0), ($contract['tex_buyer_paid'] ?? 0));
            }
        }

        return Response::json([
            'success' => true,
            'id' => $car->id,
            'uuid' => $hasUuidColumn ? ($car->uuid ?? null) : null,
            'message' => 'تم حفظ العقد بنجاح'
        ], 200);
    }
    public function getIndexContractCar(Request $request)
    {
        if (!Auth::check()) {
            return Response::json(['message' => 'Unauthenticated.', 'data' => [], 'total' => 0], 401);
        }

        $owner_id = Auth::user()->owner_id;
        $current_user_id = Auth::user()->id;
        $current_user_type_id = Auth::user()->type_id;
        $q = $request->query('q', '');
        $from = $request->query('from');
        $to = $request->query('to');
        $user_id_filter = $request->query('user_id');
        $limit = (int) $request->query('limit', 100);
        if ($limit < 1) {
            $limit = 100;
        }

        $data = CarContract::with('user')->where('owner_id', $owner_id)->orderBy('id', 'desc');

        if ($this->userCarContractUser && $current_user_type_id == $this->userCarContractUser) {
            $data->where('user_id', $current_user_id);
        }

        if ($user_id_filter && (int) $user_id_filter > 0) {
            $data->where('user_id', (int) $user_id_filter);
        }

        if ($from && $to) {
            $data->whereBetween('created', [$from, $to]);
        }

        if ($q) {
            $data->where(function ($query) use ($q) {
                $query->where('name_seller', 'LIKE', '%' . $q . '%')
                    ->orWhere('vin', 'LIKE', '%' . $q . '%')
                    ->orWhere('car_name', 'LIKE', '%' . $q . '%')
                    ->orWhere('name_buyer', 'LIKE', '%' . $q . '%');
            });
        }

        $data = $data->paginate($limit)->toArray();
        return Response::json($data, 200);
    }
    
    public function DelCarContract(Request $request){
        try {
            $contract = CarContract::findOrFail($request->id);
            $owner_id=Auth::user()->owner_id;

            // Calculate the total amount paid for the contract
            $totalDollarPaid = ($contract->tex_seller_paid ?? 0) + ($contract->tex_buyer_paid ?? 0);
            $totalDinarPaid = ($contract->tex_seller_dinar_paid ?? 0) + ($contract->tex_buyer_dinar_paid ?? 0);
            
            $desc = ' مترجع حذف عقد بيع للسيارة ' . ($contract['car_name']) . ' البائع ' . ($contract['name_seller'] ?? 0) . ' دفع مبلغ ' . ($contract['tex_seller_paid'] ?? 0) . ' و المشتري ' . ($contract['tex_buyer_paid'] ?? 0) . ' دفع مبلغ ' . ($contract['name_buyer'] ?? 0);

            
            // Handle payments for dollars
            if ($totalDollarPaid > 0) {

                
                // Record a transaction for dollar payments
                $this->decreaseWallet($totalDollarPaid, $desc, $this->mainBoxContract->where('owner_id', $owner_id)->first()->id, $contract->id, 'App\Models\CarContract', 0, 0, '$', 0, 0, 'out', ($contract->tex_seller_paid ?? 0), ($contract->tex_buyer_paid ?? 0));
            }
            
            // Handle payments for dinars
            if ($totalDinarPaid > 0) {
               
                
                // Record a transaction for dinar payments
                $this->decreaseWallet($totalDinarPaid, $desc, $this->mainBoxContract->where('owner_id', $owner_id)->first()->id, $contract->id, 'App\Models\CarContract', 0, 0, 'IQD', 0, 0, 'out', ($contract->tex_seller_paid ?? 0), ($contract->tex_buyer_paid ?? 0));
            }
            
            // Delete the contract
          $contract->delete();
    
            return response()->json('ok', 200);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the record is not found
            return response()->json(['error' => 'Contract not found'], 404);
        } catch (\Exception $e) {
            // Handle other exceptions that might occur during deletion
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function totalInfoContract(Request $request){
        $owner_id=Auth::user()->owner_id;
        $current_user_id=Auth::user()->id;
        $current_user_type_id=Auth::user()->type_id;

        $allContract = CarContract::with('user')->where('owner_id', $owner_id);

        // إذا كان نوع المستخدم car_contract_user، يرى فقط العقود التي أنشأها
        if ($this->userCarContractUser && $current_user_type_id == $this->userCarContractUser) {
            $allContract->where('user_id', $current_user_id);
        }

        $from =  $_GET['from'] ?? 0;
        $to =$_GET['to'] ?? 0;
        $user_id_filter = $_GET['user_id'] ?? null;

        if ($from && $to) {
            $allContract->whereBetween('created', [$from, $to]);
        }

        if ($user_id_filter && (int) $user_id_filter > 0) {
            $allContract->where('user_id', (int) $user_id_filter);
        }

        
        $sumAllContractSeller = $allContract->sum('tex_seller');
        $sumAllContractSellerPaid = $allContract->sum('tex_seller_paid');

        $sumAllContractBuyer = $allContract->sum('tex_buyer');
        $sumAllContractBuyerPaid = $allContract->sum('tex_buyer_paid');

        $sumAllContractSellerDinar = $allContract->sum('tex_seller_dinar');
        $sumAllContractSellerPaidDinar = $allContract->sum('tex_seller_dinar_paid');

        $sumAllContractBuyerDinar = $allContract->sum('tex_buyer_dinar');
        $sumAllContractBuyerPaidDinar = $allContract->sum('tex_buyer_dinar_paid');

        $data = TransactionsContract::orderBy('id', 'desc');

        if ($from && $to) {
            $data->whereBetween('created', [$from, $to]); ;
        }

        $dataIn = clone $data;
        $dataOut = clone $data;
        $dataInDinar = clone $data;
        $dataOutDinar = clone $data;
        
        $sumTransactionsIn = $dataIn->where('currency', '$')->where('type', 'in')->sum('amount');
        $sumTransactionsDinarIn = $dataInDinar->where('currency', 'IQD')->where('type', 'in')->sum('amount');
        $sumTransactionsOut = $dataOut->where('currency', '$')->where('type', 'out')->sum('amount');
        $sumTransactionsDinarOut = $dataOutDinar->where('currency', 'IQD')->where('type', 'out')->sum('amount');


        $sumTransactions = ($sumTransactionsIn -$sumTransactionsOut);
        $sumTransactionsDinar =($sumTransactionsDinarIn - $sumTransactionsDinarOut );
        // Additional logic to retrieve client data
        $data = [
            'contract' => $allContract->count(),
            'sumTransactionsIn'=>$sumTransactionsIn,
            'sumTransactionsOut'=>$sumTransactionsOut,
            'sumTransactionsDinarIn'=>$sumTransactionsDinarIn,
            'sumTransactionsDinarOut'=>$sumTransactionsDinarOut,
            'sum_contract' => $sumAllContractSellerPaid+$sumAllContractBuyerPaid,
            'sum_contract_debit' => ($sumAllContractSeller-$sumAllContractSellerPaid)+($sumAllContractBuyer-$sumAllContractBuyerPaid),
            'sum_contract_dinar' => $sumAllContractSellerPaidDinar+$sumAllContractBuyerPaidDinar,
            'sum_contract_debit_dinar' => ($sumAllContractSellerDinar-$sumAllContractSellerPaidDinar)+($sumAllContractBuyerDinar-$sumAllContractBuyerPaidDinar),
            'sumTransactions'=> $sumTransactions,
            'sumTransactionsDinar'=> $sumTransactionsDinar
        ];

        return Response::json($data, 200);

    }

    public function getListTransactionsContract(Request $request){
        $owner_id=Auth::user()->owner_id;
        $car_have_expenses = $request->car_have_expenses ?? '';
        $user_id =$_GET['user_id'] ?? '';
        $q = $_GET['q']??'';
        $from =  $_GET['from'] ?? 0;
        $to =$_GET['to'] ?? 0;
        $limit =$_GET['limit'] ?? 0;
 
        $data = TransactionsContract::orderBy('id', 'desc');;

        if ($from && $to) {
            $data->whereBetween('created', [$from, $to]);
        }
        
        if($q){
            $data->where(function ($query) use ($q) {
                $query->where('description', 'LIKE', '%' . $q . '%')
                    ->orWhere('amount', 'LIKE', '%' . $q . '%')
                   ;
            });
        }

        $data =$data->paginate($limit)->toArray();
        return Response::json($data, 200);
    }
    public function addToBoxContract(Request $request)
    {
     $owner_id=Auth::user()->owner_id;
     $note= $request->amountNote??'';
     $amountDollar= $request->amountDollar??0;
     $amountDinar= $request->amountDinar??0;
     $desc="وصل قبض مباشر"." ".' '.$note;
     $date= $request->date??0;
     if($amountDollar){
      $transaction=$this->increaseWallet($amountDollar,$desc,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'$',$date);
     }
     if($amountDinar){

      $transaction=$this->increaseWallet($amountDinar,$desc,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'IQD',$date);
     }
   

     return Response::json($transaction, 200);
 
     }

     public function DropFromBoxContract(Request $request)
     {
      $owner_id=Auth::user()->owner_id;
      $user_id= $request->user['id']??0;
      $note= $request->note??'';
      $amountDollar= $request->amountDollar??0;
      $amountDinar= $request->amountDinar??0;

      $desc=" سحب دفعة  ".' '.$note;
      $date= $request->date??0;
      if($amountDollar){
        $transaction=$this->decreaseWallet($amountDollar,$desc,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'$',$date);
      }
      if($amountDinar)
      {
        $transaction=$this->decreaseWallet($amountDinar,$desc,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'IQD',$date);

      }

  
      return Response::json($request, 200);
  
      }

    public function increaseWallet(int $amount,$desc,$user_id,$morphed_id='',$morphed_type='',$is_pay=0,$discount=0,$currency='$',$created=0,$parent_id=0,$type='in',$s_amount=0,$b_amount=0) 
     {
         if($amount){
             if($created==0){
                 $created=$this->currentDate;
             }
             $user=  User::with('wallet')->find($user_id);
             if($id = $user->wallet->id){
             $transactionDetils = ['type' => $type,'wallet_id'=>$id,'description'=>$desc,'amount'=>$amount,'is_pay'=>$is_pay,'morphed_id'=>$morphed_id,'morphed_type'=>$morphed_type,'user_added'=>0,'created'=>$created,'discount'=>$discount,'currency'=>$currency,'parent_id'=>$parent_id,'s_amount'=>$s_amount,'b_amount'=>$b_amount];
             $transaction = TransactionsContract::create($transactionDetils);
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
 
    public function decreaseWallet(int $amount,$desc,$user_id,$morphed_id=0,$morphed_type='',$is_pay=0,$discount=0,$currency='$',$created=0,$parent_id=0,$type='out',$s_amount=0,$b_amount=0) 
     {
         if($amount){
         if($created==0){
             $created=$this->currentDate;
         }
 
         $user=  User::with('wallet')->find($user_id);
         if(!$user->wallet->id){
           Wallet::create(['user_id' => $user_id,'balance'=>0]);
         }
   
         if($id = $user->wallet->id){
         $wallet = Wallet::find($id);
         $transactionDetils = ['type' => $type,'wallet_id'=>$id,'description'=>$desc,'amount'=>$amount,'is_pay'=>$is_pay,'morphed_id'=>$morphed_id,'morphed_type'=>$morphed_type,'user_added'=>0,'created'=>$created,'discount'=>$discount,'currency'=>$currency,'parent_id'=>$parent_id,'s_amount'=>$s_amount,'b_amount'=>$b_amount];
         $transaction =TransactionsContract::create($transactionDetils);
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
     public function delTransactionsContract(Request $request)
     {
         $owner_id = Auth::user()->owner_id;
         $transaction_id = $request->id ?? 0;
         $originalTransaction = TransactionsContract::with('morphed')->find($transaction_id);
         
         if (!$originalTransaction) {
             return response()->json(['message' => 'Transaction not found'], 404);
         }
         
         $wallet_id = $originalTransaction->wallet_id;
         $wallet = Wallet::find($wallet_id);
         
         // Handle only if the morphed type is CarContract
         if ($originalTransaction->morphed_type === 'App\Models\CarContract') {
             // Get the associated CarContract
             $carContract = $originalTransaction->morphed;
             if($carContract){
                $contract = CarContract::find($carContract->id);

            
             
             // Determine if the transaction amount relates to the seller or buyer
             $s_amount = $originalTransaction->s_amount;
             $b_amount = $originalTransaction->b_amount;
             
             // Adjust contract amounts based on the seller or buyer and the currency
             if ($s_amount) {
                 if ($originalTransaction->currency === '$') {
                     $contract->decrement('tex_seller_paid', $originalTransaction->amount);
                 } elseif ($originalTransaction->currency === 'IQD') {
                     $contract->decrement('tex_seller_dinar_paid', $originalTransaction->amount);
                 }
             }
             
             if ($b_amount) {
                 if ($originalTransaction->currency === '$') {
                     $contract->decrement('tex_buyer_paid', $originalTransaction->amount);
                 } elseif ($originalTransaction->currency === 'IQD') {
                     $contract->decrement('tex_buyer_dinar_paid', $originalTransaction->amount);
                 }
             }
            }
         }
         
         // Decrement wallet balance based on transaction currency
         if ($originalTransaction->currency === '$') {
             $wallet->decrement('balance', $originalTransaction->amount);
         } elseif ($originalTransaction->currency === 'IQD') {
             $wallet->decrement('balance_dinar', $originalTransaction->amount);
         }
         
         // Delete all transactions with the same parent_id
         $allTransactions = TransactionsContract::where('parent_id', $transaction_id)->get();
         foreach ($allTransactions as $transaction) {
             if ($transaction->currency === '$') {
                 $wallet->decrement('balance', $transaction->amount);
             } elseif ($transaction->currency === 'IQD') {
                 $wallet->decrement('balance_dinar', $transaction->amount);
             }
             $transaction->delete();
         }
         
         // Delete the original transaction
         $originalTransaction->delete();
         
         return response()->json(['message' => 'Transaction and associated records deleted successfully'], 200);
     }   
     public function convertDollarDinarContract(Request $request){
        $owner_id=Auth::user()->owner_id;
        $amountDollar =$request->amountDollar;
        $amountResultDinar =$request->amountResultDinar;
        $exchangeRate =$request->exchangeRate;
        $date=$request->date??0;
        $desc=' تحويل من الصندوق مبلغ بالدولار'.' '.($amountDollar).'  بسعر صرف '.' '.$exchangeRate.' المبلغ المضاف للصندوف بالدينار '.$amountResultDinar;
        if($amountDollar){
            $transactionDollar=$this->decreaseWallet($amountDollar,$desc,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'$',$date);
          }
          if($amountResultDinar)
          {
            $transactionDinar=$this->increaseWallet($amountResultDinar,$desc,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'IQD',$date);
          }
          
          $transactionDollar->update(['parent_id'=>$transactionDinar->id]);
          $transactionDinar->update(['parent_id'=>$transactionDollar->id]);
          return Response::json($transactionDinar, 200);    

    }
    public function convertDinarDollarContract(Request $request){
        $owner_id=Auth::user()->owner_id;
        $amountDinar =$request->amountDinar;
        $amountResultDollar =$request->amountResultDollar;
        $exchangeRate =$request->exchangeRate;
        $date=$request->date??0;
        $desc=' تحويل من الصندوق مبلغ بالدينار'.' '.($amountDinar).'  بسعر صرف '.' '.$exchangeRate.' المبلغ المضاف للصندوف بالدولار '.$amountResultDollar;
        if($amountResultDollar){
            $transactionDollar= $this->increaseWallet($amountResultDollar,$desc,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'$',$date);
          }
          if($amountDinar)
          {
            $transactionDinar= $transaction=$this->decreaseWallet($amountDinar,$desc,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,$this->mainBoxContract->where('owner_id',$owner_id)->first()->id,'App\Models\User',0,0,'IQD',$date);
          }
          $transactionDollar->update(['parent_id'=>$transactionDinar->id]);
          $transactionDinar->update(['parent_id'=>$transactionDollar->id]);
          return Response::json($transactionDinar, 200);    

    }

    public function handlePaymentChange($newPayment, $oldPayment, $currency, $desc, $contractId,$type) {
        $owner_id=Auth::user()->owner_id;
        if ($newPayment > $oldPayment) {
            $difference = $newPayment - $oldPayment;
            if($type=='seller'){
                $this->increaseWallet($difference, $desc, $this->mainBoxContract->where('owner_id', $owner_id)->first()->id, $contractId, 'App\Models\CarContract', 0, 0, $currency,0,0,'in',$newPayment);
            }
            if($type=='buyer'){
                $this->increaseWallet($difference, $desc, $this->mainBoxContract->where('owner_id', $owner_id)->first()->id, $contractId, 'App\Models\CarContract', 0, 0, $currency,0,0,'in',0,$newPayment);
            }
            } elseif ($newPayment < $oldPayment) {

            $difference = $oldPayment - $newPayment;

            if($type=='seller'){
            $this->decreaseWallet($difference, $desc, $this->mainBoxContract->where('owner_id', $owner_id)->first()->id,$contractId, 'App\Models\CarContract', 0, 0, $currency,0,0,'out',$newPayment);
            }
            if($type=='buyer'){
                $this->decreaseWallet($difference, $desc, $this->mainBoxContract->where('owner_id', $owner_id)->first()->id,$contractId, 'App\Models\CarContract', 0, 0, $currency,0,0,'out',0,$newPayment);
            }
        }
    }

    public function getIndexClientsContract()
    {
        $q = $_GET['q'] ?? '';
        $from = $_GET['from'] ?? 0;
        $to = $_GET['to'] ?? 0;
        $searchType = $_GET['searchType'] ?? '';
        $owner_id=Auth::user()->owner_id;
        $current_user_id=Auth::user()->id;
        $current_user_type_id=Auth::user()->type_id;
        $userClient=$this->userClient ?? 0;

        $dataQuery1 = CarContract::where('owner_id', $owner_id);
        $dataQuery2 = CarContract::where('owner_id', $owner_id);

        // إذا كان نوع المستخدم car_contract_user، يرى فقط العقود التي أنشأها
        if ($this->userCarContractUser && $current_user_type_id == $this->userCarContractUser) {
            $dataQuery1->where('user_id', $current_user_id);
            $dataQuery2->where('user_id', $current_user_id);
        }

        $dataQuery1 = $dataQuery1->select('name_seller', 
                 DB::raw('MAX(phone_seller) as phone_seller'), 
                 DB::raw('SUM(tex_seller_dinar) as tex_seller_dinar'), 
                 DB::raw('SUM(tex_seller) as tex_seller'),
                 DB::raw('SUM(tex_seller_dinar_paid) as tex_seller_dinar_paid'), 
                 DB::raw('SUM(tex_seller_paid) as tex_seller_paid'),
                 )
        ->groupBy('name_seller');

        $dataQuery2 = $dataQuery2->select('name_buyer', 
                 DB::raw('MAX(phone_buyer) as phone_seller'), 
                 DB::raw('SUM(tex_buyer_dinar) as tex_buyer_dinar'), 
                 DB::raw('SUM(tex_buyer) as tex_buyer'),
                 DB::raw('SUM(tex_buyer_dinar_paid) as tex_buyer_dinar_paid'), 
                 DB::raw('SUM(tex_buyer_paid) as tex_buyer_paid')
                 )
        ->groupBy('name_buyer');
    
        if ($q) {
            if ($q !== 'debit') {
            $dataQuery1->where('name_seller', 'like', '%' . $q . '%');
            $dataQuery2->where('name_buyer', 'like', '%' . $q . '%');
            }
        }
        if ($from && $to) {
            $dataQuery1->whereBetween('created', [$from, $to]);
            $dataQuery2->whereBetween('created', [$from, $to]);

        } 


        if ($q=='debit') {
            $dataQuery1->havingRaw('(SUM(tex_seller) - SUM(tex_seller_paid)) > 0 OR (SUM(tex_seller_dinar) - SUM(tex_seller_dinar_paid)) > 0');
            $dataQuery2->havingRaw('(SUM(tex_buyer) - SUM(tex_buyer_paid)) > 0 OR (SUM(tex_buyer_dinar) - SUM(tex_buyer_dinar_paid)) > 0');

        } 
        $data1 = $dataQuery1->get();
        $data2 = $dataQuery2->get();

        // إحصائيات العقود المنجزة هذا الشهر + آخر العقود (عند q=debit)
        $statsQuery = CarContract::where('owner_id', $owner_id);
        if ($this->userCarContractUser && $current_user_type_id == $this->userCarContractUser) {
            $statsQuery->where('user_id', $current_user_id);
        }
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
        $completedThisMonth = (clone $statsQuery)->whereBetween('created', [$startOfMonth, $endOfMonth])->count();
        $recentContracts = (clone $statsQuery)->orderBy('id', 'desc')->limit(8)->get(['id', 'no', 'car_name', 'name_seller', 'name_buyer', 'car_price', 'created']);

        return Response::json([
            'data1' => $data1,
            'data2' => $data2,
            'completedContractsThisMonth' => $completedThisMonth,
            'recentContracts' => $recentContracts,
        ], 200);
    }
    public function contract_account_report()
    {
        $q = $_GET['q'] ?? '';
        $from = $_GET['from'] ?? 0;
        $to = $_GET['to'] ?? 0;
        $type = $_GET['type'] ?? 0;
        $print = $_GET['print'] ?? 0;
        $searchType = $_GET['searchType'] ?? '';
        $owner_id=Auth::user()->owner_id;
        $current_user_id=Auth::user()->id;
        $current_user_type_id=Auth::user()->type_id;
        $userClient=$this->userClient ?? 0;

        $dataQuery1 = CarContract::where('owner_id', $owner_id);
        $dataQuery2 = CarContract::where('owner_id', $owner_id);

        // إذا كان نوع المستخدم car_contract_user، يرى فقط العقود التي أنشأها
        if ($this->userCarContractUser && $current_user_type_id == $this->userCarContractUser) {
            $dataQuery1->where('user_id', $current_user_id);
            $dataQuery2->where('user_id', $current_user_id);
        }

        $dataQuery1 = $dataQuery1->select('name_seller', 
                 DB::raw('MAX(phone_seller) as phone_seller'), 
                 DB::raw('SUM(tex_seller_dinar) as tex_seller_dinar'), 
                 DB::raw('SUM(tex_seller) as tex_seller'),
                 DB::raw('SUM(tex_seller_dinar_paid) as tex_seller_dinar_paid'), 
                 DB::raw('SUM(tex_seller_paid) as tex_seller_paid'),
                 )
        ->groupBy('name_seller');

        $dataQuery2 = $dataQuery2->select('name_buyer', 
                 DB::raw('MAX(phone_buyer) as phone_seller'), 
                 DB::raw('SUM(tex_buyer_dinar) as tex_buyer_dinar'), 
                 DB::raw('SUM(tex_buyer) as tex_buyer'),
                 DB::raw('SUM(tex_buyer_dinar_paid) as tex_buyer_dinar_paid'), 
                 DB::raw('SUM(tex_buyer_paid) as tex_buyer_paid')
                 )
        ->groupBy('name_buyer');
    
        if ($q) {
            if ($q !== 'debit') {
            $dataQuery1->where('name_seller', 'like', '%' . $q . '%');
            $dataQuery2->where('name_buyer', 'like', '%' . $q . '%');
            }
        }
        if ($from && $to) {
            $dataQuery1->whereBetween('created', [$from, $to]);
            $dataQuery2->whereBetween('created', [$from, $to]);

        } 


        if ($q=='debit') {
            $dataQuery1->havingRaw('(SUM(tex_seller) - SUM(tex_seller_paid)) > 0 OR (SUM(tex_seller_dinar) - SUM(tex_seller_dinar_paid)) > 0');
            $dataQuery2->havingRaw('(SUM(tex_buyer) - SUM(tex_buyer_paid)) > 0 OR (SUM(tex_buyer_dinar) - SUM(tex_buyer_dinar_paid)) > 0');

        } 
        $data1 = $dataQuery1->get();
        $data2 = $dataQuery2->get();
        if($print==1){
            $data = TransactionsContract::whereBetween('created', [$from, $to])->where('description', 'like', "%$type%")->get();
            $totalDollar = TransactionsContract::whereBetween('created', [$from, $to])->where('currency', '$')->where('description', 'like', "%$type%")->sum('amount');
            $totalDinar = TransactionsContract::whereBetween('created', [$from, $to])->where('currency', 'IQD')->where('description', 'like', "%$type%")->sum('amount');

            $config=SystemConfig::first();
            return view('Contract.receiptExpensesContractTotal',compact('data','config','totalDollar','totalDinar'));
        }
        if($print==2){
            if($q){
                $data = CarContract::where('owner_id', $owner_id)
                ->whereBetween('created', [$from, $to])
                ->where('name_seller', 'like', '%' . $q . '%')
                ->get();
            }else{
                $data =  CarContract::where('owner_id', $owner_id)->whereBetween('created', [$from, $to])->get();
            }
            
            $config=SystemConfig::first();
            return view('Contract.reportContractTotal',compact('data','config'));
        }

        return Response::json(['data1'=>$data1,'data2'=>$data2], 200);
    }
    public function makeDrivingDocument(Request $request)
    {

        $owner_id = Auth::user()->owner_id;
        $year_date=Carbon::now()->format('Y');

        // Retrieve values from the request or provide defaults
        $car_colorDriving = $request->get('car_colorDriving', '');
        $car_numberDriving = $request->get('car_numberDriving', '');
        $car_typeDriving = $request->get('car_typeDriving', '');
        $createdDriving = $request->get('createdDriving', '');
        $nameDriving = $request->get('nameDriving', '');
        $noteDriving = $request->get('noteDriving', '');
        $vinDriving = $request->get('vinDriving', '');
        $yearDriving = $request->get('yearDriving', '');
        $clientIdDriving = $request->get('clientIdDriving', 0);
        
        // Insert into the database
        $doc = Driving::create([
            'client_id' => $clientIdDriving,
            'user_id' => Auth::user()->id,
            'owner_id' => $owner_id,
            'color' => $car_colorDriving, // Assuming your table has this column
            'car_number' => $car_numberDriving,
            'car_type' => $car_typeDriving,
            'created' => $createdDriving,
            'name' => $nameDriving,
            'note' => $noteDriving,
            'vin' => $vinDriving, // Add column if needed
            'year' => $yearDriving,
            'year_date' => $year_date
        ]);


        return Response::json($doc, 200);    

    }

    public function makeDrivingDocumentPdf(Request $request)
    {


        $id = $request->get('doc_id', '');

        // Insert into the database
        $doc = Driving::find($id);

        $config=SystemConfig::first();
        
        return view('documents.driving',compact('doc','config'));
    }

    public function verify($token)
    {
        $contract = CarContract::where('verification_token', $token)->firstOrFail();

        if (empty($contract->verification_token)) {
            $contract->verification_token = Str::uuid()->toString();
            $contract->save();
        }

        $config = SystemConfig::first();
        $verificationUrl = route('contract.verify', $contract->verification_token);

        return view('contractVerify', [
            'contract' => $contract,
            'config' => $config,
            'verificationUrl' => $verificationUrl,
        ]);
    }
}