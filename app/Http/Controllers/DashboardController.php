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
use App\Models\ExitCar;
use App\Models\Contract;
use App\Models\CarModel;
use App\Models\Color;
use App\Models\Wallet;
use App\Models\UserType;
use App\Models\ExpensesType;
use Illuminate\Support\Facades\DB;
use App\Models\Transactions;
use App\Models\Expenses;
use App\Helpers\UploadHelper;
use App\Exports\Exportcar;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SystemConfig;
use App\Services\AccountingCacheService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $accountingController;    
    protected $url;
    protected $accounting;
    protected $currentDate;
    
    public function __construct(AccountingController $accountingController,AccountingCacheService $accounting)
    {
    $this->accountingController = $accountingController;
    $this->url = env('FRONTEND_URL');
    $this->accounting = $accounting;
     $this->currentDate = Carbon::now()->format('Y-m-d');

    }
     
    public function __invoke(Request $request)
    {
        $results = null;
        $config=SystemConfig::first();
        $apiKey =$config->api_key;
        return Inertia::render('dashboard', ['apiKey'=>$apiKey]);   
    }
    public function refreshCache()
    {
         $this->accounting->refreshIfNeeded();

        return response()->json(['message' => 'تم تحديث الكاش بنجاح']);
    }
 
    public function index(Request $request)
    { 
         $this->accounting->loadAccounts(Auth::user()->owner_id);
        $config=SystemConfig::first();
        $apiKey =$config->api_key;

        return Inertia::render('Dashboard', ['apiKey'=>$apiKey]);   
    }
    public function purchases(Request $request)
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        $owner_id=Auth::user()->owner_id;
        $car = Car::all()->where('owner_id',$owner_id);
        $allCars = $car->count();
        $client = User::where('type_id', $this->accounting->userClient())
        ->where('owner_id', $owner_id)
        ->get()
        ->map(function ($user) {
            $user->name = "{$user->name}  {$user->phone}";
            return $user;
        });
    
        $config=SystemConfig::first()->default_price_p;
        return Inertia::render('purchases', ['client'=>$client,'config'=>$config]);   
    }
    public function sales(Request $request)
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        $owner_id=Auth::user()->owner_id;
        $car = Car::all()->where('owner_id',$owner_id);
        $allCars = $car->count();
        $client = User::where('type_id', $this->accounting->userClient())
        ->where('owner_id', $owner_id)
        ->get()
        ->map(function ($user) {
            $user->name = "{$user->name}  {$user->phone}";
            return $user;
        });
        $config=SystemConfig::first()->default_price_s;

        return Inertia::render('Sales', ['client'=>$client ,'config'=>$config]);   
    }
    public function totalInfo(Request $request)
    {
        $owner_id=Auth::user()->owner_id;
        $this->accounting->loadAccounts($owner_id);
        $from =  $_GET['from'] ?? Carbon::now()->format('Y-m-d');
        $to =$_GET['to'] ?? Carbon::now()->format('Y-m-d');
        $mainBoxId=$this->accounting->mainBox()->wallet->id;
       
        $transactionIn = (int) Transactions::where('wallet_id', $mainBoxId)
        ->where('currency', '$')
        ->whereIn('type', ['in', 'inUserBox'])
        ->sum('amount');

        $transactionOut =(int) Transactions::where('wallet_id', $mainBoxId)
        ->where('currency', '$')
        ->whereIn('type', ['out', 'debt'])
        ->sum('amount');

        $car = Car::all()->where('owner_id',$owner_id);

        $sumTotal = $car->sum('total');
        $sumTotalS = $car->sum('total_s');
        $client = User::where('type_id', $this->accounting->userClient())->where('owner_id',$owner_id)->pluck('id');
        $sumDebit =Wallet::whereIn('user_id', $client)->sum('balance');
        $sumPaid = $car->sum('paid')+ $car->sum('discount');
        $sumProfit = $car->where('results',2)->sum('profit');

         $transactionsTodayDollar = Transactions::where('wallet_id', $mainBoxId)
        ->where('currency', '$')
        ->when($from && $to, function ($q) use ($from, $to) {
            $q->whereBetween('created', [$from, $to]);
        });

          $transactionsTodayDinar = Transactions::where('wallet_id', $mainBoxId)
        ->where('currency', 'IQD')
        ->when($from && $to, function ($q) use ($from, $to) {
            $q->whereBetween('created', [$from, $to]);
        });


        $transactionInTodayDollar = (int) $transactionsTodayDollar->clone()
            ->whereIn('type', ['in', 'inUserBox'])
            ->sum('amount');

        $transactionOutTodayDollar = (int) $transactionsTodayDollar->clone()
            ->whereIn('type', ['out', 'debt', 'outUserBox'])
            ->sum('amount');

        $transactionInTodayDinar = (int) $transactionsTodayDinar->clone()
            ->whereIn('type', ['in', 'inUserBox'])
            ->sum('amount');

        $transactionOutTodayDinar = (int) $transactionsTodayDinar->clone()
            ->whereIn('type', ['out', 'debt', 'outUserBox'])
            ->sum('amount');
        
        $data = [
        'mainAccount'=>$sumTotal -$sumPaid ,
        
        'howler'=>$this->accounting->howler()->wallet->balance??0,
        'shippingCoc'=>$this->accounting->shippingCoc()->wallet->balance??0,
        'border'=>$this->accounting->border()->wallet->balance??0,
        'iran'=>$this->accounting->iran()->wallet->balance??0,
        'dubai'=>$this->accounting->dubai()->wallet->balance??0,
        'sumTotal'=>$sumTotal,
        'sumPaid'=>$sumPaid,
        'sumDebit'=>$sumDebit,
        'sumProfit'=>$sumProfit,
        'allCars'=>$car->count()??0,
        'purchasesCost'=>$sumTotalS??0,
        'clientPaid'=>$sumPaid??0,
        'clientDebit'=>$sumDebit ?? 0,
        'mainBoxDollar'=>$this->accounting->mainBox()->wallet->balance??0,
        'mainBoxDinar' =>$this->accounting->mainBox()->wallet->balance_dinar??0,
        'mainBoxDollarNew'=>$transactionIn+$transactionOut,
        'transactionInTodayDollar' => $transactionInTodayDollar,
        'transactionOutTodayDollar' => $transactionOutTodayDollar,
        'transactionInTodayDinar' => $transactionInTodayDinar,
        'transactionOutTodayDinar' => $transactionOutTodayDinar,

        
        ];
        return response()->json(['data'=>$data]); 

    }
    public function client(Request $request)
    {
        $owner_id=Auth::user()->owner_id;
         $client = User::where('type_id', $this->accounting->userClient())->where('owner_id',$owner_id)->with('wallet')->paginate(10);
        return response()->json($client); 
    }
    public function getcount(Request $request)
    {
        $owner_id=Auth::user()->owner_id;
        $profile=  Car::all()->where('owner_id',$owner_id);
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
        $owner_id=Auth::user()->owner_id;
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
            'no'=>$no,
            'owner_id'=>$owner_id
             ]);
             if($paid_amount){
                $desc=trans('text.payCar').' '.$purchase_price.trans('text.payDone').$paid_amount;
             
                $this->accountingController->decreaseWallet($paid_amount, $desc,$this->accounting->mainAccount()->id,$car->id,'App\Models\Car');
                $this->accountingController->increaseWallet($paid_amount, $desc,$this->accounting->outAccount()->id,$car->id,'App\Models\Car' );
                $this->accountingController->increaseWallet($paid_amount, $desc,$this->accounting->outSupplier()->id,$car->id,'App\Models\Car');
                if($debt_price){
                   $this->accountingController->increaseWallet($debt_price, $desc,$this->accounting->debtSupplier->id,$car->id,'App\Models\Car');
                }
             }
 
        }else{
            $car=Car::find($car_id);
            $purchase_price_old=$car->purchase_price;
            if($purchase_price > $purchase_price_old){
                $purchase_price_new = $purchase_price - $purchase_price_old;
                $desc=trans('text.editCar').' '.trans('text.from').$purchase_price_old.trans('text.to').$purchase_price;
                $this->accountingController->decreaseWallet($purchase_price_new, $desc,$this->accounting->mainAccount()->id,$car->id,'App\Models\Car');
                $this->accountingController->increaseWallet($purchase_price_new, $desc,$this->accounting->outAccount()->id,$car->id,'App\Models\Car' );
                $this->accountingController->decreaseWallet($purchase_price_new, $desc,$this->accounting->inAccount()->id,$car->id,'App\Models\Car');
                $this->accountingController->increaseWallet($purchase_price_new, $desc,$this->accounting->outSupplier()->id,$car->id,'App\Models\Car');
                $this->accountingController->increaseWallet($purchase_price_new, $desc,$this->accounting->debtSupplier()->id,$car->id,'App\Models\Car');
            }
            if($purchase_price < $purchase_price_old){
                $purchase_price_new =$purchase_price_old - $purchase_price;
                $desc=trans('text.editCar').' '.trans('text.from').$purchase_price_old.trans('text.to').$purchase_price;
                $this->accountingController->increaseWallet($purchase_price_new, $desc,$this->accounting->mainAccount()->id,$car->id,'App\Models\Car');
                $this->accountingController->decreaseWallet($purchase_price_new, $desc,$this->accounting->outAccount()->id,$car->id,'App\Models\Car' );
                $this->accountingController->increaseWallet($purchase_price_new, $desc,$this->accounting->inAccount()->id,$car->id,'App\Models\Car');
                $this->accountingController->decreaseWallet($purchase_price_new, $desc,$this->accounting->outSupplier()->id,$car->id,'App\Models\Car');
                $this->accountingController->decreaseWallet($purchase_price_new, $desc,$this->accounting->debtSupplier()->id,$car->id,'App\Models\Car');

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
        $owner_id = Auth::user()->owner_id;
        $year_date = Carbon::now()->format('Y');
        $client_id = $request->client_id;
        $maxNo = Car::max('no') ?? 0;
        $results = 0;
        $checkout = $request->checkout ?? 0;
        $shipping_dolar = $request->shipping_dolar ?? 0;
        $coc_dolar = $request->coc_dolar ?? 0;
        $dinar = $request->dinar ?? 0;
        // keep the original exchange rate as entered (e.g., 140000)
        $dolar_price_input = $request->dolar_price ?? 1;
        $expenses = $request->expenses ?? 0;
        $land_shipping = $request->land_shipping ?? 0;
        $land_shipping_dinar = $request->land_shipping_dinar ?? 0;

        // use a calculated rate for math (divide by 100 only for calculations)
        $calc_rate = $dolar_price_input;
        if ($calc_rate == 0) {
            $calc_rate = 1;
        } elseif ($calc_rate > 9999) {
            $calc_rate = $calc_rate / 100;
        }

        $carsPayload = collect($request->get('cars', []))
            ->map(function ($car) {
                return [
                    'vin' => isset($car['vin']) ? trim($car['vin']) : null,
                    'car_number' => isset($car['car_number']) && $car['car_number'] !== null
                        ? trim($car['car_number'])
                        : null,
                ];
            })
            ->filter(function ($car) {
                return !empty($car['vin']);
            });

        if ($carsPayload->isEmpty() && $request->vin) {
            $carsPayload = collect([[
                'vin' => trim($request->vin),
                'car_number' => $request->car_number ? trim($request->car_number) : null,
            ]]);
        }

        if ($carsPayload->isEmpty()) {
            return Response::json(['message' => 'VIN is required'], 422);
        }

        $dolar_custom = (int) ($dinar / ($calc_rate)) ?? 0;
        $land_shipping_dinar_custom = (int) ($land_shipping_dinar / ($calc_rate)) ?? 0;
        $total_amount = $checkout + $shipping_dolar + $expenses + $coc_dolar + $dolar_custom + $land_shipping + $land_shipping_dinar_custom;

        if (empty($client_id) || $client_id == 0) {
            $client = new User;
            $client->name = $request->client_name;
            $client->phone = $request->client_phone;
            $client->created = Carbon::now()->format('Y-m-d');
            $client->type_id = $this->accounting->userClient();
            $client->owner_id = $owner_id;
            $client->year_date = $year_date;
            $client->save();
            Wallet::create(['user_id' => $client->id, 'balance' => 0]);
            $client_id = $client->id;
        }

        $createdCars = collect();

        DB::transaction(function () use (
            $request,
            $carsPayload,
            $owner_id,
            $year_date,
            $client_id,
            $results,
            $checkout,
            $shipping_dolar,
            $coc_dolar,
            $dinar,
            $dolar_price_input,
            $expenses,
            $land_shipping,
            $land_shipping_dinar,
            $total_amount,
            &$maxNo,
            &$createdCars
        ) {
            foreach ($carsPayload as $carData) {
                $maxNo += 1;

                $car = Car::create([
                    'note' => $request->note ?? '',
                    'no' => $maxNo,
                    'car_owner' => $request->car_owner,
                    'car_type' => $request->car_type,
                    'vin' => $carData['vin'],
                    'car_number' => $carData['car_number'],
                    'dinar' => $dinar,
                    // store the entered exchange rate as-is
                    'dolar_price' => $dolar_price_input,
                    'shipping_dolar' => $shipping_dolar,
                    'coc_dolar' => $coc_dolar,
                    'checkout' => $checkout,
                    'total' => $total_amount,
                    'year' => $request->year,
                    'year_date' => $year_date,
                    'car_color' => $request->car_color,
                    'date' => $request->date,
                    'expenses' => $expenses,
                    'client_id' => $client_id,
                    'results' => $results,
                    'owner_id' => $owner_id,
                    'land_shipping' => $land_shipping,
                    'land_shipping_dinar' => $land_shipping_dinar,
                    'profit' => ($total_amount * -1),
                ]);

                if ($total_amount) {
                    $desc = 'اضافة سيارة من المشتريات رقم شانصى ' . $carData['vin'];
                    $this->accountingController->decreaseWallet(
                        ($total_amount),
                        $desc,
                        $this->accounting->mainAccount()->id,
                        $car->id,
                        'App\Models\Car'
                    );
                }

                $createdCars->push($car);
            }
        });

        return Response::json('ok', 200);
    }
    public function updateCarsP(Request $request)
    {
        $owner_id=Auth::user()->owner_id;

        $car_id=$request->id??0;
        $maxNo = Car::max('no');

        if($car_id){
            $no = $request->no;
        }else{
            $no = $maxNo + 1;
        }
        $car=Car::with('client')->find($request->id);


        if(!isset($no)){
            $no=$car->no;
           
        }
        $images=[];
        $checkout=$request->checkout ;
        $shipping_dolar=$request->shipping_dolar ;
        $coc_dolar=$request->coc_dolar;
        $land_shipping=$request->land_shipping;
        $land_shipping_dinar=$request->land_shipping_dinar;

        $dinar=$request->dinar;
        $expenses=($request->expenses??0);
        // keep original exchange rate as entered
        $dolar_price_input = $request->dolar_price;
        // calculate effective rate for math
        $calc_rate = $dolar_price_input;
        if($calc_rate==0){
            $calc_rate=1;
        }elseif($calc_rate > 9999){
            $calc_rate=$calc_rate/100;
        }else{
            $calc_rate=$calc_rate;
        }

        $total = (int)(($checkout+$shipping_dolar+ $coc_dolar +(int)($dinar / ($calc_rate))+(int)($land_shipping_dinar / ($calc_rate))+$expenses+$land_shipping) ??0);
        $profit=$car->total_s-$total;

        if($car->client_id == $request->client_id)
        {

        }else{
            $desc="نقل السيارة";
            if($car->results==0){
                if($car->total_s){
                    $this->accountingController->decreaseWallet($car->total_s, $desc,$car->client_id,$car->id,'App\Models\User');
                    $this->accountingController->increaseWallet($car->total_s, $desc,$request->client_id,$car->id,'App\Models\User');
                }
            }
            if($car->results==1){
                if($car->total_s){
                    $this->accountingController->decreaseWallet($car->total_s-$car->paid, $desc,$car->client_id,$car->id,'App\Models\User');
                    $this->accountingController->increaseWallet($car->total_s-$car->paid, $desc,$request->client_id,$car->id,'App\Models\User');

                
                }
            }
        }
            $dataToUpdate = $request->all();

            // If 'purchase_price' and 'paid_amount' are calculated separately, add them to $dataToUpdate
            $dataToUpdate['total']=$total;
            $dataToUpdate['profit']=$profit;
            
            if($total >$car->total){
                $descClient = trans('text.addExpenses').' '.($total-$car->total).' '.trans('text.for_car').$car->car_type.' '.$car->vin;
                $this->accountingController->decreaseWallet(($total-$car->total), $descClient,$this->accounting->mainAccount()->id,$car->id,'App\Models\Car');
            }else{
                $descClient = 'مرتجع للصندوق مصاريف';
                $this->accountingController->increaseWallet(($car->total-$total), $descClient,$this->accounting->mainAccount()->id,$car->id,'App\Models\Car');

            }
            if($car->paid){
                if($total > $car->paid +$car->discount){
                    $dataToUpdate['results'] = 1  ;
                }elseif($total == $car->paid+$car->discount){
                    $dataToUpdate['results'] = 2  ;
                }else{
                    $dataToUpdate['results'] = 0  ;
                }
            }

            //$this->accountingController->increaseWallet(($total-$car->total), $descClient,$car->client_id,$car->id,'App\Models\User');
            $car->update($dataToUpdate);


        return Response::json('ok', 200);    
    }
    public function updateCarsS(Request $request)
    {
        $owner_id=Auth::user()->owner_id;

        $car_id=$request->id??0;

        $maxNo = Car::max('no');

        if($car_id){
            $no = $request->no;
        }else{
            $no = $maxNo + 1;
        }
        $car=Car::find($car_id);
        if(!isset($no)){
            $no=$car->no;
           
        }
        $images=[];
        $checkout_s=$request->checkout_s ;
        $shipping_dolar_s=$request->shipping_dolar_s ;
        $coc_dolar_s=$request->coc_dolar_s ;
        $dinar_s=$request->dinar_s ;
        $land_shipping_s=$request->land_shipping_s;
        $land_shipping_dinar_s=$request->land_shipping_dinar_s;
        $expenses_s=($request->expenses_s??0);
        $dolar_price_s=$request->dolar_price_s ;
        if($dolar_price_s==0){
            $dolar_price_s=1;
        }elseif($dolar_price_s > 9999){
            $dolar_price_s=$dolar_price_s/100;
        }else{
            $dolar_price_s=$dolar_price_s;
        }
        $total_s = (($checkout_s+$shipping_dolar_s+ $coc_dolar_s +(int)($dinar_s / ($dolar_price_s))+(int)($land_shipping_dinar_s / ($dolar_price_s))+$expenses_s+$land_shipping_s) ??0);
        $profit=$total_s-$car->total;
        $descClient = trans('text.editExpenses').' '.$total_s-$car->total_s.' '.trans('text.for_car').$car->car_type.' '.$car->vin;
        $this->accountingController->increaseWallet($total_s-$car->total_s, $descClient,$car->client_id,$car->id,'App\Models\User');
            // Extract the relevant fields from the $request object
            $dataToUpdate = $request->all();
            // If 'purchase_price' and 'paid_amount' are calculated separately, add them to $dataToUpdate
            $dataToUpdate['total_s']=$total_s;
            $dataToUpdate['profit']=$profit;

            if($car->paid){
                if($total_s >($car->paid+$car->discount)){
                    $dataToUpdate['results'] = 1  ;
                }elseif($total_s==$car->paid+$car->discount){
                    $dataToUpdate['results'] = 2  ;
                }else{
                    $dataToUpdate['results'] = 0  ;
                }
            }


            // Update the car model
            $car->update($dataToUpdate);
            

        return Response::json('ok', 200);    
    }
    public function getIndexExpenses () {

        $expenses = Expenses::with('user')->paginate(10);
        return Response::json($expenses, 200);    

    }
    public function bulkUpdateCarsP(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $carIds = $request->get('car_ids', []);
        if (!is_array($carIds) || empty($carIds)) {
            return Response::json(['message' => 'car_ids is required'], 422);
        }

        $cars = Car::whereIn('id', $carIds)->where('owner_id', $owner_id)->get();
        if ($cars->isEmpty()) {
            return Response::json('ok', 200);
        }

        $requestData = $request->all();
        $allowedFields = [
            'dolar_price',
            'shipping_dolar',
            'coc_dolar',
            'checkout',
            'expenses',
            'land_shipping',
            'land_shipping_dinar',
            'note',
            'date',
        ];

        foreach ($cars as $car) {
            $checkout = array_key_exists('checkout', $requestData) ? (float)$requestData['checkout'] : ($car->checkout ?? 0);
            $shipping_dolar = array_key_exists('shipping_dolar', $requestData) ? (float)$requestData['shipping_dolar'] : ($car->shipping_dolar ?? 0);
            $coc_dolar = array_key_exists('coc_dolar', $requestData) ? (float)$requestData['coc_dolar'] : ($car->coc_dolar ?? 0);
            $expenses = array_key_exists('expenses', $requestData) ? (float)$requestData['expenses'] : ($car->expenses ?? 0);
            $land_shipping = array_key_exists('land_shipping', $requestData) ? (float)$requestData['land_shipping'] : ($car->land_shipping ?? 0);
            $land_shipping_dinar = array_key_exists('land_shipping_dinar', $requestData) ? (float)$requestData['land_shipping_dinar'] : ($car->land_shipping_dinar ?? 0);
            $dolar_price_input = array_key_exists('dolar_price', $requestData)
                ? (float)$requestData['dolar_price']
                : ($car->dolar_price ?? 1);

            $calc_rate = $dolar_price_input;
            if ($calc_rate == 0) {
                $calc_rate = 1;
            } elseif ($calc_rate > 9999) {
                $calc_rate = $calc_rate / 100;
            }

            $dinar = $car->dinar ?? 0;
            $dolar_custom = (int)($dinar / $calc_rate);
            $land_shipping_dinar_custom = (int)($land_shipping_dinar / $calc_rate);
            $total = (int)(
                ($checkout + $shipping_dolar + $coc_dolar
                + $dolar_custom
                + $land_shipping_dinar_custom
                + $expenses + $land_shipping) ?? 0
            );
            $profit = $car->total_s - $total;

            // حساب الفروقات للصندوق
            if ($total > $car->total) {
                $descClient = trans('text.addExpenses') . ' ' . ($total - $car->total) . ' ' . trans('text.for_car') . $car->car_type . ' ' . $car->vin;
                $this->accountingController->decreaseWallet(($total - $car->total), $descClient, $this->accounting->mainAccount()->id, $car->id, 'App\Models\Car');
            } else {
                $descClient = 'مرتجع للصندوق مصاريف';
                $this->accountingController->increaseWallet(($car->total - $total), $descClient, $this->accounting->mainAccount()->id, $car->id, 'App\Models\Car');
            }

            $dataToUpdate = [];
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $requestData)) {
                    $dataToUpdate[$field] = $requestData[$field];
                }
            }

            $dataToUpdate['total'] = $total;
            $dataToUpdate['profit'] = $profit;
            $dataToUpdate['dolar_price'] = $dolar_price_input;

            if ($car->paid) {
                if ($total > $car->paid + $car->discount) {
                    $dataToUpdate['results'] = 1;
                } elseif ($total == $car->paid + $car->discount) {
                    $dataToUpdate['results'] = 2;
                } else {
                    $dataToUpdate['results'] = 0;
                }
            }

            $car->update($dataToUpdate);
        }

        return Response::json('ok', 200);
    }
    public function bulkUpdateCarsS(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $carIds = $request->get('car_ids', []);
        if (!is_array($carIds) || empty($carIds)) {
            return Response::json(['message' => 'car_ids is required'], 422);
        }

        $cars = Car::whereIn('id', $carIds)->where('owner_id', $owner_id)->get();
        if ($cars->isEmpty()) {
            return Response::json('ok', 200);
        }

        $requestData = $request->all();
        $allowedFields = [
            'dolar_price_s',
            'shipping_dolar_s',
            'coc_dolar_s',
            'checkout_s',
            'expenses_s',
            'land_shipping_s',
            'land_shipping_dinar_s',
            'note',
            'date',
        ];

        foreach ($cars as $car) {
            $checkout_s = array_key_exists('checkout_s', $requestData) ? (float)$requestData['checkout_s'] : ($car->checkout_s ?? 0);
            $shipping_dolar_s = array_key_exists('shipping_dolar_s', $requestData) ? (float)$requestData['shipping_dolar_s'] : ($car->shipping_dolar_s ?? 0);
            $coc_dolar_s = array_key_exists('coc_dolar_s', $requestData) ? (float)$requestData['coc_dolar_s'] : ($car->coc_dolar_s ?? 0);
            $expenses_s = array_key_exists('expenses_s', $requestData) ? (float)$requestData['expenses_s'] : ($car->expenses_s ?? 0);
            $land_shipping_s = array_key_exists('land_shipping_s', $requestData) ? (float)$requestData['land_shipping_s'] : ($car->land_shipping_s ?? 0);
            $land_shipping_dinar_s = array_key_exists('land_shipping_dinar_s', $requestData) ? (float)$requestData['land_shipping_dinar_s'] : ($car->land_shipping_dinar_s ?? 0);
            $dolar_price_s_input = array_key_exists('dolar_price_s', $requestData)
                ? (float)$requestData['dolar_price_s']
                : ($car->dolar_price_s ?? 1);

            $calc_rate_s = $dolar_price_s_input;
            if ($calc_rate_s == 0) {
                $calc_rate_s = 1;
            } elseif ($calc_rate_s > 9999) {
                $calc_rate_s = $calc_rate_s / 100;
            }

            $dinar_s = $car->dinar_s ?? 0;
            $dolar_custom_s = (int)($dinar_s / $calc_rate_s);
            $land_shipping_dinar_custom_s = (int)($land_shipping_dinar_s / $calc_rate_s);
            $total_s = (($checkout_s + $shipping_dolar_s + $coc_dolar_s
                + $dolar_custom_s
                + $land_shipping_dinar_custom_s
                + $expenses_s + $land_shipping_s) ?? 0);
            $profit = $total_s - $car->total;
            $descClient = trans('text.editExpenses') . ' ' . ($total_s - $car->total_s) . ' ' . trans('text.for_car') . $car->car_type . ' ' . $car->vin;
            $this->accountingController->increaseWallet($total_s - $car->total_s, $descClient, $car->client_id, $car->id, 'App\Models\User');

            $dataToUpdate = [];
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $requestData)) {
                    $dataToUpdate[$field] = $requestData[$field];
                }
            }
            $dataToUpdate['total_s'] = $total_s;
            $dataToUpdate['profit'] = $profit;
            $dataToUpdate['dolar_price_s'] = $dolar_price_s_input;

            if ($car->paid) {
                if ($total_s > ($car->paid + $car->discount)) {
                    $dataToUpdate['results'] = 1;
                } elseif ($total_s == $car->paid + $car->discount) {
                    $dataToUpdate['results'] = 2;
                } else {
                    $dataToUpdate['results'] = 0;
                }
            }

            $car->update($dataToUpdate);
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
            $client->type_id = $this->accounting->userClient();
            $client->created =Carbon::now()->format('Y-m-d');
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
                $this->accountingController->increaseWallet($car->paid_amount_pay, $desc,$this->accounting->mainAccount()->id,$car->id,'App\Models\Car');
                $this->accountingController->increaseWallet($car->paid_amount_pay, $desc,$this->accounting->inAccount()->id,$car->id,'App\Models\Car');
                if($pay_price-$paid_amount_pay >= 0){
                    $this->accountingController->increaseWallet($pay_price-$paid_amount_pay, $desc,$this->accounting->debtAccount()->id,$car->id,'App\Models\Car');
                }
                if($pay_price==$paid_amount_pay){
                    $car->increment('results'); 
                }
            }
            return Response::json('ok', 200);    
    }
    public function getIndexCar(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $car_have_expenses = $request->car_have_expenses ?? '';
        $user_id = $request->get('user_id') ?? '';
        $q = $request->get('q') ?? '';
        $from = $request->get('from') ?? 0;
        $to = $request->get('to') ?? 0;
        $get_image = $request->get('get_image') ?? 0;
        $limit = $request->get('limit') ?? 15;
        $printExcel = $request->get('printExcel') ?? 0;
        $online_contract = $request->get('online_contract') ?? 0;
        // بناء الاستعلام الأساسي مع تحسين الأداء
        $baseQuery = Car::query()->where('owner_id', $owner_id);
        
        // إضافة فلاتر إضافية
        if ($car_have_expenses || $car_have_expenses == 1) {
            $baseQuery->where('car_have_expenses', $car_have_expenses);
        }
        
        if ($from && $to) {
            $baseQuery->whereBetween('date', [$from, $to]);
        }
        
        if ($user_id) {
            $baseQuery->where('client_id', $user_id);
        }
        
        // تطبيق البحث النصي المحسن
        if ($q) {
            $baseQuery->where(function ($query) use ($q) {
                $query->where('car_number', 'LIKE', '%' . $q . '%')
                      ->orWhere('vin', 'LIKE', '%' . $q . '%')
                      ->orWhere('car_type', 'LIKE', '%' . $q . '%')
                      ->orWhereHas('client', function ($subquery) use ($q) {
                          $subquery->where('name', 'LIKE', '%' . $q . '%');
                      });
            });
        }
        
        // حساب الإحصائيات بعد تطبيق الفلاتر (تحسين مهم)
        $resultsDinar = $baseQuery->sum('dinar');
        $resultsDollar = $baseQuery->sum('total');
        $resultsTotalS = $baseQuery->sum('total_s');
        $resultsProfit = $baseQuery->sum('profit');
        $resultsPaid = $baseQuery->sum('paid');
        $totalCars = $baseQuery->count();
        // جلب البيانات مع العلاقات المطلوبة فقط
        if($get_image){
            // جلب البيانات مع الصور (أبطأ لكن يحتوي على الصور)
            $data = $baseQuery->with(['client:id,name', 'CarImages'])
                             ->orderBy('no', 'DESC')
                             ->paginate($limit)
                             ->toArray();
        } else if($online_contract){
            $data = $baseQuery->with(['client:id,name', 'contract','exitcar'])
                             ->orderBy('no', 'DESC')
                             ->paginate($limit)
                             ->toArray();
        }
        else {
            // جلب البيانات بدون الصور (أسرع للأداء)
            $data = $baseQuery->with(['client:id,name'])
                             ->orderBy('no', 'DESC')
                             ->paginate($limit)
                             ->toArray();
        }
     
        
        // إضافة الإحصائيات
        $data['resultsDinar'] = $resultsDinar;
        $data['resultsDollar'] = $resultsDollar;
        $data['totalCars'] = $totalCars;
        $data['resultsProfit'] = $resultsProfit;
        $data['resultsPaid'] = $resultsPaid;
        $data['resultsTotalS'] = $resultsTotalS;

        $config = SystemConfig::first();

        if ($printExcel) {
            return Excel::download(new Exportcar($from, $to, $user_id, $owner_id), $from . ' ' . $to . '.xlsx');
        }

        return Response::json($data, 200);
    }
    public function getIndexCarSearch()
    {
        $owner_id = Auth::user()->owner_id;
        $term = $_GET['q'] ?? '';
        
        // إذا كان البحث فارغ، إرجاع مصفوفة فارغة
        if (empty($term)) {
            return Response::json(['data' => [], 'total' => 0], 200);
        }
        
        // بناء الاستعلام المحسن
        $query = Car::query()
            ->select(['id', 'vin', 'car_number', 'car_type', 'client_id', 'no', 'created_at', 'total', 'total_s', 'results'])
            ->where('owner_id', $owner_id);
        
        // استخدام البحث المحسن مع فهارس
        $query->where(function ($q) use ($term) {
            $q->where('vin', 'LIKE', $term . '%')  // البحث من البداية أفضل للأداء
              ->orWhere('car_number', 'LIKE', $term . '%')
              ->orWhere('car_type', 'LIKE', '%' . $term . '%')
              ->orWhereHas('client', function ($subquery) use ($term) {
                  $subquery->where('name', 'LIKE', '%' . $term . '%');
              });
        });
        
        // تحميل العلاقة المطلوبة فقط وتحسين الأداء
        $data = $query->with(['client:id,name'])
                     ->orderBy('no', 'DESC')
                     ->limit(50) // تحديد حد أقصى للنتائج لتحسين الأداء
                     ->get();
        
        return Response::json($data, 200);
    }
    public function addToBox()
    {
        $user_id = $_GET['user_id']??0;
        $desc=trans('text.addToBox').' '.($_GET['amount']??0).'$'.' || '.$_GET['note']??'';
        $this->accountingController->increaseWallet(($_GET['amount']??0), $desc,$this->accounting->mainAccount()->id,$user_id,'App\Models\User',$user_id);
        return Response::json('ok', 200);    
    }
    public function withDrawFromBox()
    {
        $user_id = $_GET['user_id']??0;
        $desc=trans('text.withDrawFromBox').' '.($_GET['amount']??'').'$'.' || '.$_GET['note']??'';
        $this->accountingController->decreaseWallet(($_GET['amount']??0), $desc,$this->accounting->mainAccount()->id,$user_id,'App\Models\User',$user_id);
        
        return Response::json('ok', 200);    
    }

    public function DelCar(Request $request){
        $owner_id=Auth::user()->owner_id;

        $car=Car::with('client')->find($request->id);
        $desc=' مرتج حذف سيارة'.$car->total;
        $wallet = Wallet::where('user_id',$car->client_id)->first();
        $this->accountingController->increaseWallet($car->total, $desc,$this->accounting->mainAccount()->id,$car->id,'App\Models\Car');
        if($car->results == 0 && $car->total_s!=0){
            $trans = $this->accountingController->decreaseWallet($car->total_s , $desc,$car->client->id,$car->id,'App\Models\Car');
        }
        if($car->results == 1){
            $trans = $this->accountingController->decreaseWallet($car->total_s-$car->paid , $desc,$car->client->id,$car->id,'App\Models\Car');
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
