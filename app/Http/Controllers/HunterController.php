<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Wallet;
use App\Models\UserType;
use App\Models\CarImagesHunter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Hunter;
 

use Intervention\Image\Facades\Image;
use File;


class HunterController extends Controller
{
    public function __construct(){
        $this->url = env('FRONTEND_URL');
        $this->userClient =  UserType::where('name', 'client')->first()->id;
        $this->currentDate = Carbon::now()->format('Y-m-d');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function index()
    {  
        return Inertia::render('Hunter/Index');
    }

    public function addCarsHunter(Request $request)
    {
        
        $car=Hunter::create([
            'note'=> $request->note??'',
            'car_owner'=> $request->car_owner,
            'car_type'=> $request->car_type,
            'vin'=> $request->vin,
            'year'=> $request->year,
            'status'=> 1,
            'price_p'=> $request->price_p,
            'price_s'=> $request->price_s,
            'car_color'=> $request->car_color,
            'date'=> $request->date ?? $this->currentDate,
             ]);

        return Response::json($car, 200);    

    }

    public function getIndexCarHunter()
    {
        $user_id =$_GET['user_id'] ?? '';
        $q = $_GET['q']??'';
        $from =  $_GET['from'] ?? 0;
        $to =$_GET['to'] ?? 0;
        $limit =$_GET['limit'] ?? 0;
        $data =  Hunter::with('client')->with('CarImagesHunter')->orderBy('created_at', 'desc');
        $totalCars = $data->count();
        if($q){
            $data = $data->orwhere('vin', 'LIKE','%'.$q.'%')->orwhere('car_type', 'LIKE','%'.$q.'%')->orWhereHas('client', function ($query) use ($q) {
                $query->where('name', 'LIKE', '%' . $q . '%');
            });
        }
 
 
        $data =$data->paginate($limit)->toArray();

        $data['totalCars']  =$totalCars;


        return Response::json($data, 200);
    }
    public function carsHunterUpload(Request $request)
    {
        $carId = $request->carId;
        $path1 = public_path('uploads');
        $path2 = public_path('uploadsResized');
        $img_type=$request->img_type??'';
        $year=Carbon::now()->format('Y');

        // Create the directories if they don't exist
        if (!file_exists($path1)) {
            mkdir($path1, 0777, true);
        }
        if (!file_exists($path2)) {
            mkdir($path2, 0777, true);
        }
    
        $file = $request->file('image');
    
        // Generate a unique file name
        $name = uniqid() . '_' . trim($file->getClientOriginalName());
    
        // Save the original image to the first directory
        $file->move($path1, $name);
    
        // Load the original image using Intervention Image
        $image = Image::make(public_path('uploads/' . $name));
    
        // Save the resized image to the second directory
        $image->resize(50, 50, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    
        $image->save(public_path('uploadsResized/' . $name));
        // Create a new record in the database
        $carImage = CarImagesHunter::create([
            'name' => $name,
            'car_id' => $carId,
            'year' => $year,
        ]);
        

        return response()->json($carImage, 200);
    }
    public function carsHunterImageDel(Request $request){
        $name = $request->name;
        $img_type=$request->img_type??'';

        File::delete(public_path('uploads/'.$name));
        File::delete(public_path('uploadsResized/'.$name));
        CarImagesHunter::where('name', $name)->delete();
    
        
        return Response::json('deleted is done', 200);

    }
    public function updateCarsHunter(Request $request){
    
        $hunter= Hunter::find($request->id)->update(
        [
        'car_type'=>$request->car_type,
        'car_color'=>$request->car_color,
        'year'=>$request->year,
        'note'=>$request->note,
         'price_p'=> $request->price_p,
        'price_s'=> $request->price_s,
        'vin'=>$request->vin]);
        return Response::json($hunter, 200);
    }
    public function delCarsHunterr(Request $request){
        $hunter = Hunter::with('CarImagesHunter')->find($request->id);

        if ($hunter) {
            foreach ($hunter->CarImagesHunter as $carImage) {
                // Delete the image file from the public directory
                File::delete(public_path('uploads/' . $carImage->name));
                File::delete(public_path('uploadsResized/' . $carImage->name));

                // Delete the image record from the database
                $carImage->delete();
            }
        
            // After deleting all associated images, delete the hunter
            $hunter->delete();
            
            return response()->json(['message' => 'Hunter and associated images deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Hunter not found'], 404);
        }
    }
    public function check_vinHunter(Request $request){
        $car_vin = $request->get('car_vin');
        $car = Hunter::where('vin',$car_vin)->first();
        if($car){
            return response()->json(true); 
        }else{
            return response()->json(false); 

        }
    }
    }