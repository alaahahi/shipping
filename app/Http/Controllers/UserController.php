<?php
   
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Card;
use App\Models\Car;

use App\Models\UserType;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Massage;
use Carbon\Carbon;
use App\Models\Transactions;

class UserController extends Controller
{
    public function __construct(){
         $this->url = env('FRONTEND_URL');
         $this->userAdmin =  UserType::where('name', 'admin')->first()->id;
         $this->userSeles =  UserType::where('name', 'seles')->first()->id;
         $this->userClient =  UserType::where('name', 'client')->first()->id;
         $this->userAccount =  UserType::where('name', 'account')->first()->id;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function index()
    {
        $cards= Card::all();
        return Inertia::render('Users/Index', ['url'=>$this->url,'cards'=>$cards]);
    }
    public function getSaler()
    {
        $data = User::with('userType:id,name','wallet')->where('type_id', $this->userSeles)->paginate(10);
        return Response::json($data, 200);    

    }

    public function clients()
    {
        $cards= Card::all();
        return Inertia::render('Clients/Index', ['url'=>$this->url,'cards'=>$cards]);
    }
    public function showClients($id)
    {
        $clients = User::where('type_id', $this->userClient)->get();

        $client= user::find($id);
        return Inertia::render('Clients/Show', ['url'=>$this->url,'client'=>$client,'clients'=>$clients,'client_id'=>$id]);
    }
    public function show ()
    {
        return Inertia::render('Users/Index', ['url'=>$this->url]);
    }
    public function getIndex()
    {
        $data = User::with('userType:id,name','wallet')->where('type_id', $this->userSeles)->paginate(10);
        return Response::json($data, 200);
    }
    public function getIndexClients()
    {
        $q = $_GET['q'] ?? '';
        $from =  $_GET['from'] ?? 0;
        $to =$_GET['to'] ?? 0 ;
        
        $dataQuery = User::with('userType:id,name', 'wallet')
        ->where('type_id', $this->userClient);
    
        if ($q) {
            if ($q !== 'debit') {
                $dataQuery =  $dataQuery->where('name', 'like', '%' . $q . '%')->orWhere('phone', 'like', '%' . $q . '%');
            }
            
            $paginationLimit = $q !== 'debit' ? 1000 : 1000;
        } else {
            $paginationLimit = 1000;
        }
        if ($from && $to) {
            $dataQuery->whereBetween('created', [$from, $to]);
            $paginationLimit = 1000;
        } 
        $data = $dataQuery->paginate($paginationLimit);

        $data->getCollection()->transform(function ($user) {
            $user->car_total_uncomplete = Car::where('client_id', $user->id)->whereIn('results', [0, 1])->count();
            $user->car_total_complete = Car::where('client_id', $user->id)->where('results', 2)->count();
            
            if ($user->car_total_uncomplete > 0) {
                return $user;
            }
            
            return $user;
        });
        
        // Filter the collection to keep only users with car_total_uncomplete > 0
        if ($q === 'debit') {
            $data->setCollection($data->getCollection()->filter(function ($user) {
                return $user->car_total_uncomplete > 0;
            }));
        }
        return Response::json($data, 200);
    }

    public function create()
    {
        $usersType = UserType::all();
        $userSeles=$this->userSeles;
        $userDoctor =  $this->userClient;
        $userHospital = '';
        return Inertia::render('Users/Create',['usersType'=>$usersType,'userSeles'=>$userSeles,'userDoctor'=>$userDoctor,'userHospital'=>$userHospital]);
    }
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
           ])->validate();
           //$userChief_id =User::where('type_id',  $this->userChief)->first()->id ?? 0 ;
                $user = User::create([
                    'name' => $request->name,
                    'type_id' => $this->userSeles,
                    'email' => $request->email,
                    'created' =>Carbon::now()->format('Y-m-d'),
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone
                ]);
  
                Wallet::create(['user_id' => $user->id]);
     
        return Inertia::render('Users/Index', ['url'=>$this->url]);
    }
    public function clientsStore(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
           ])->validate();
           //$userChief_id =User::where('type_id',  $this->userChief)->first()->id ?? 0 ;
                $user = User::create([
                    'name' => $request->name,
                    'type_id' => $this->userClient,
                    'phone' => $request->phone,
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
        $userSeles=$this->userSeles;
        $userDoctor =  $this->userClient;
        $userHospital = '';
       //$coordinators =User::where('type_id', $this->userCoordinator )->get();
       // $chief =User::where('type_id', $this->userChief )->get();
        return Inertia::render('Users/Edit', [
            'user' => $User,'usersType'=>$usersType,'userSeles'=>$userSeles,'userDoctor'=>$userDoctor,'userHospital'=>$userHospital
        ]);
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
                        'type_id' => $request->userType,
                        'percentage' => $request->percentage
                    ]);
                } else {
                    $request->validate([
                        'name' => 'required|string|max:255',
                    ]);
                    $user = User::find($id)->update([
                        'name' => $request->name,
                        'type_id' => $request->userType,
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
                        'percentage' => $request->percentage
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
                        'percentage' => $request->percentage
                    ]);
                }
                break;
        }
        
        return Inertia::render('clients', ['url'=>$this->url]);

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
     
        return Inertia::render('Users/Index', ['url'=>$this->url]); 
    }
    public function ban($id)
    {
        User::find($id)->update(['is_band' => 1]);
        return Inertia::render('Users/Index', ['url'=>$this->url]); 
    }
    public function unban($id)
    {
        User::find($id)->update(['is_band' => 0]);
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