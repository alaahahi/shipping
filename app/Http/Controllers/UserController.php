<?php
   
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Wallet;
use App\Models\User;
 use App\Models\Car;
use App\Models\SystemConfig;

use App\Models\UserType;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Massage;
use Carbon\Carbon;
use App\Models\Transactions;
use App\Models\Contract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\AccountingCacheService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $accounting;
    protected $url;
     public function __construct(AccountingCacheService $accounting){
         $this->url = env('FRONTEND_URL');
         $this->accounting = $accounting;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    
    public function index()
    {         
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        return Inertia::render('Users/Index');
    }

    public function clients()
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        return Inertia::render('Clients/Index', ['url'=>$this->url]);
    }
    public function showClients($id)
    {
        $owner_id=Auth::user()->owner_id;
        $q = request()->query('q');
        $clients = User::with('wallet')->where('owner_id',$owner_id)->where('type_id', $this->accounting->userClient())->get();
        $client= user::find($id);
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        return Inertia::render('Clients/Show', ['url'=>$this->url,'client'=>$client,'clients'=>$clients,'client_id'=>$id,'q'=>$q]);
    }
    public function show ()
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        return Inertia::render('Users/Index', ['url'=>$this->url]);
    }
    public function getIndex()
    {
        $data = User::with('userType:id,name','wallet')->whereIn('type_id', [$this->accounting->userSelesKirkuk(),$this->accounting->userCarExpenses()])->paginate(10);
        return Response::json($data, 200);
    }
    public function getIndexClients()
    {
        $q = request()->input('q', '');
        $from = request()->input('from', 0);
        $to = request()->input('to', 0);
        $owner_id = Auth::user()->owner_id;
        $userClient = $this->accounting->userClient() ?? 0;
        $page = request()->input('page', 1);
        $print = request()->input('print', 0);
        
        // تحسين مفتاح الكاش
        $cacheKey = 'clients_optimized_' . md5($q . $owner_id . $userClient . $from . $to . $page);
        $cacheDuration = 300; // تقليل مدة الكاش إلى 5 دقائق لضمان البيانات الحديثة

        // استخدام الكاش مع تحسين الاستعلام
        $query = Cache::remember($cacheKey, $cacheDuration, function () use ($owner_id, $userClient, $q, $from, $to) {
            // بناء الاستعلام الأساسي المحسن
            $baseQuery = DB::table('users')
                ->select([
                    'users.id', 
                    'users.name', 
                    'users.phone', 
                    'users.created_at'
                ])
                ->where('users.owner_id', $owner_id)
                ->where('users.type_id', $userClient);
            
            // إضافة الإحصائيات باستخدام JOIN بدلاً من subqueries لتحسين الأداء
            $baseQuery->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
                     ->leftJoin('car', 'users.id', '=', 'car.client_id')
                     ->leftJoin('contract', 'users.id', '=', 'contract.user_id')
                     ->selectRaw('COALESCE(wallets.balance, 0) as balance')
                     ->selectRaw('COUNT(DISTINCT car.id) as car_count')
                     ->selectRaw('COUNT(DISTINCT CASE WHEN car.results = 2 THEN car.id END) as car_count_completed')
                     ->selectRaw('COUNT(DISTINCT CASE WHEN car.total_s = 0 THEN car.id END) as car_total_un_pay')
                     ->selectRaw('COUNT(DISTINCT contract.id) as contract_count')
                     ->groupBy('users.id', 'users.name', 'users.phone', 'users.created_at', 'wallets.balance');
            
            // تطبيق البحث المحسن
            if ($q && $q !== 'debit') {
                $baseQuery->where(function ($query) use ($q) {
                    $query->where('users.name', 'LIKE', '%' . $q . '%')
                          ->orWhere('car.vin', 'LIKE', '%' . $q . '%')
                          ->orWhere('car.car_number', 'LIKE', '%' . $q . '%');
                });
            }
            
            // تطبيق فلترة التاريخ
            if ($from && $to) {
                $baseQuery->whereBetween('users.created_at', [$from, $to]);
            }
            
            // ترتيب النتائج
            $baseQuery->orderBy('balance', 'desc');
            
            return $baseQuery->get();
        });
        
        // معالجة النتائج للطباعة
        if ($print == 1) {
            $config = SystemConfig::first();
            
            $data = $q === 'debit' 
                ? collect($query)->filter(function ($item) {
                    return $item->balance > 0;
                })
                : $query;
            
            return view('reportClients', compact('data', 'config', 'owner_id'));
        }
        
        // معالجة النتائج للعرض العادي
        if ($q === 'debit') {
            $data = collect($query)->filter(function ($item) {
                return $item->balance > 0;
            });
            
            return response()->json(['data' => $data], 200);
        }
        
        // تطبيق التصفح مع تحسين الأداء
        $paginationLimit = 25;
        $currentPage = max(1, (int)$page);
        $totalItems = count($query);
        $totalPages = ceil($totalItems / $paginationLimit);
        
        $paginatedData = collect($query)
            ->forPage($currentPage, $paginationLimit)
            ->values()
            ->toArray();
        
        return response()->json([
            'data' => $paginatedData,
            'pagination' => [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'per_page' => $paginationLimit
            ]
        ], 200);
    }
    
    
    
    
    public function create()
    {
        $usersType = UserType::all();
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        return Inertia::render('Users/Create',['usersType'=>$usersType]);
    }
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users',
           ])->validate();
        $user = User::create([
                    'name' => $request->name,
                    'type_id' => $request->userType,
                    'email' => $request->email,
                    'created' =>Carbon::now()->format('Y-m-d'),
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone
                ]);
  
                Wallet::create(['user_id' => $user->id]);
                $this->accounting->loadAccounts(Auth::user()->owner_id);

        return Inertia::render('Users/Index', ['url'=>$this->url]);
    }
    public function clientsStore(Request $request)
    {
        $year_date=Carbon::now()->format('Y');

        $owner_id=Auth::user()->owner_id;
        Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users',
           ])->validate();
           //$userChief_id =User::where('type_id',  $this->userChief)->first()->id ?? 0 ;
                $user = User::create([
                    'name' => $request->name,
                    'type_id' => $this->accounting->userClient(),
                    'phone' => $request->phone,
                    'year_date'=>$year_date,
                    'owner_id'=>$owner_id,
                    'created' =>Carbon::now()->format('Y-m-d'),
                ]);
  
                Wallet::create(['user_id' => $user->id]);
     
                return Response::json($user, 200);
    }
    public function clientsEdit(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
           ])->validate();
           //$userChief_id =User::where('type_id',  $this->userChief)->first()->id ?? 0 ;
                $user = User::find($request->id)->update([
                    'name' => $request->name,
                    'phone' => $request->phone,
                ]);
       
        return Response::json($user, 200);
    }
    public function delClient(Request $request)
    {
    // Find the client
    $client = User::with('wallet')->where('id', $request->id)->first();

    if ($client) {
        // Get related transactions
        $transactions = Transactions::where('wallet_id', $client->wallet->id)->get();

        // Get related cars
        $cars = Car::where('client_id', $client->id)->get();

        // Delete transactions
        $transactions->each(function ($transaction) {
            $transaction->delete();
        });

        // Delete cars
        $cars->each(function ($car) {
            $car->delete();
        });

        // Delete the client
        $client->delete();

        return response()->json(['message' => 'Client and related records deleted'], 200);
    }

    return response()->json(['message' => 'Client not found'], 404);
    }
    public function getCoordinator(Request $request)
    {
        $user =User::where('type_id', $request->id);
        return Response::json(['status' => 200,'massage' => 'users found','data' => $user->get()],200);
    }
    
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function edit(User $User)
    {
        $usersType = UserType::all();
        $user = User::find($User->id);
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        return Inertia::render('Users/Edit', ['usersType'=>$usersType,'user'=>$user]);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $username = User::where('id', $id)->first()->email;
        switch ($username) {
            case $request->email:
                if ($request->password) {
                    $request->validate([
                        'name' => 'required|string|max:255',
                        'password' => [Rules\Password::defaults()],
                    ]);
                    $user = User::find($id)->update([
                        'name' => $request->name,
                        'password' => Hash::make($request->password),
                        'percentage' => $request->percentage
                    ]);
                } else {
                    $request->validate([
                        'name' => 'required|string|max:255',
                    ]);
                    $user = User::find($id)->update([
                        'name' => $request->name,
                        'percentage' => $request->percentage
                    ]);
                }
                break;
                
            default:
                if ($request->password) {
                    $request->validate([
                        'name' => 'required|string|max:255',
                        'email' => 'required|string|max:255|unique:users',
                    ]);
                    $user = User::find($id)->update([
                        'name' => $request->name,
                        'email' => $request->email,
                    ]);
                } else {
                    $request->validate([
                        'name' => 'required|string|max:255',
                        'email' => 'required|string|max:255|unique:users',
                        'password' => [Rules\Password::defaults()],
                    ]);
                    $user = User::find($id)->update([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                    ]);
                }
                break;
        }
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        return Inertia::render('Users/Index', ['url'=>$this->url]);

    }
    
    
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function destroy($id)
    {   
     
       // User::where('parent_id',$id)->update(['parent_id' =>null]);
        User::find($id)->delete();
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        return Inertia::render('Users/Index', ['url'=>$this->url]); 
    }
    public function ban($id)
    {
        User::find($id)->update(['is_band' => 1]);
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        return Inertia::render('Users/Index', ['url'=>$this->url]); 
    }
    public function unban($id)
    {
        User::find($id)->update(['is_band' => 0]);
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        return Inertia::render('Users/Index', ['url'=>$this->url]); 
    }
    public function login(LoginRequest $request)
    {
        try {
             $request->authenticate();
             $user =User::where('email', $request->email)->first();
             $publickey_receiver =  User::find($user->parent_id)->public_key ?? 0;
             if( $user->device){
                $request->device = $user->device.' | '.$request->device;
             }
             $user->append(['token']);
             if(!$user->is_band){
                if( $user->type_id == $this->userChief){
                    if($request->public_key){
                        $user->update(['public_key' => $request->public_key,'device' =>  $request->device,'publickey_receiver'=> $publickey_receiver]);
                    }
                    return Response::json(['status' => 200,'massage' => 'user found','data' => $user,'token'=> Crypt::encryptString($user->first()->id)],200); 
                }else{
                    if($publickey_receiver){
                    if($request->public_key){
                        $user->update(['public_key' => $request->public_key,'device' => $request->device,'publickey_receiver'=> $publickey_receiver]);
                    }
                       return Response::json(['status' => 200,'massage' => 'user found','data' => $user,'token'=> Crypt::encryptString($user->first()->id)],200); 
                    }else
                    return Response::json(['status' => 407,'massage' => 'user found but publickey for parent notfound'],407); 

                }
             }
             else  return Response::json(['status' => 403,'massage' => 'user is band'],403);
            
             //else  return Response::json(['status' => 407,'massage' => 'user parent dont have public key'],407);
        } catch (\Throwable $th) {
              return   Response::json(['status' => 400,'massage' => 'user not found','error' =>  $th ],400);
        }
        
    }


  
 
    
    public function Authorization($request){
        $token = substr($request->header('Authorization') ,7);;
        try {
            $id = Crypt::decryptString($token) ;
        $authUser = User::where('id', $id) ? User::where('id', $id)->first() :0;
        if($authUser && !$authUser->is_band){
           return $authUser;
        }
        else
        return  Response::json(['status' => 401,'massage' => 'user not Authorize'],401);
        } catch (\Throwable $th) {
            return  Response::json(['status' => 401,'massage' => 'user not Authorize'],401);
        }
        }
    }