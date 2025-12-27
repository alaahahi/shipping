<?php
   
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Car;
use App\Models\UserType;
use App\Models\DoctorResults;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HospitalController extends Controller
{
    public function __construct(){
         $this->url = env('FRONTEND_URL');
         $this->userAdmin =  UserType::where('name', 'admin')->first()->id;
         $this->userClient =  UserType::where('name', 'client')->first()->id;
         $this->userAccount =  UserType::where('name', 'account')->first()->id;

    }
    public function index()
    {
        $users = User::where('type_id',$this->userDoctor)->get();

        return Inertia::render('Hospital/Index', ['url'=>$this->url,'users'=>$users]);
    }
    public function show ()
    {
        return Inertia::render('Hospital/Index', ['url'=>$this->url]);
    }
    public function getIndex()
    {
        return Response::json([], 200);
    }
    public function create()
    {
        $userDoctor = User::where('type_id',$this->userDoctor)->get();
        return Inertia::render('Hospital/Add', ['url'=>$this->url,'userDoctor'=>$userDoctor]);
    }
    public function store(Request $request)
    {

        $userDoctor = User::where('type_id',$this->userDoctor)->get();
        $users = User::where('type_id',$this->userDoctor)->get();


        Validator::make($request->all(), [
        'user_id'=> 'required|int|max:50000',
        'card_id'=> 'required|int|max:50000',
       ])->validate();

        return Inertia::render('Hospital/Index', ['url'=>$this->url,'users'=>$users])->with('success', 'Model removed');
    }
    public function edit(Request $request,$id)
    {
        $userDoctor = User::where('type_id',$this->userDoctor)->get();
        return Inertia::render('Hospital/Edit', ['url'=>$this->url,'userDoctor'=>$userDoctor]);
    }
    public function livesearchAppointment(Request $request)
    {
        return response()->json([]); 
    }
    public function storeEdit(Request $request)
    {
        $userDoctor = User::where('type_id',$this->userDoctor)->get();
        $users = User::where('type_id',$this->userDoctor)->get();

        Validator::make($request->all(), [
        'user_id'=> 'required|int|max:50000',
        'card_id'=> 'required|int|max:50000',
       ])->validate();

        return Inertia::render('Hospital/Index', ['url'=>$this->url,'users'=>$users])->with('success', 'Model removed');
    }
    public function convertToTimestamp($datetime)
    {
        $carbon = Carbon::parse($datetime);
        $timestamp = $carbon;
        return $timestamp;
    }

    public function appointmentCancel()
    {
        return Response::json(['message' => 'Model removed'], 200);
    }
    public function appointmentCome()
    {
        return Response::json(['message' => 'Model removed'], 200);
    }
}