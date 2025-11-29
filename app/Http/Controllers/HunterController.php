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
use App\Models\Car;
use App\Models\CarExpenses;
use App\Models\Transactions;
 

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;


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

    /**
     * Check if car exists in CARS table by VIN
     */
    public function checkCarInCars(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $vin = $request->get('vin');
        
        if (!$vin) {
            return response()->json(['exists' => false], 200);
        }

        $car = Car::where('vin', $vin)
            ->where('owner_id', $owner_id)
            ->with(['client', 'carexpenses'])
            ->first();

        if ($car) {
            // Calculate expenses from purchases (total, expenses, etc.)
            $purchaseExpenses = [
                'total' => $car->total ?? 0,
                'expenses' => $car->expenses ?? 0,
                'checkout' => $car->checkout ?? 0,
                'shipping_dolar' => $car->shipping_dolar ?? 0,
                'coc_dolar' => $car->coc_dolar ?? 0,
                'dinar' => $car->dinar ?? 0,
                'land_shipping' => $car->land_shipping ?? 0,
                'land_shipping_dinar' => $car->land_shipping_dinar ?? 0,
            ];

            // Calculate expenses from sales (total_s, expenses_s, etc.)
            $salesExpenses = [
                'total_s' => $car->total_s ?? 0,
                'expenses_s' => $car->expenses_s ?? 0,
                'checkout_s' => $car->checkout_s ?? 0,
                'shipping_dolar_s' => $car->shipping_dolar_s ?? 0,
                'coc_dolar_s' => $car->coc_dolar_s ?? 0,
                'dinar_s' => $car->dinar_s ?? 0,
                'land_shipping_s' => $car->land_shipping_s ?? 0,
                'land_shipping_dinar_s' => $car->land_shipping_dinar_s ?? 0,
            ];

            // Get additional expenses from car_expenses table
            $additionalExpenses = $car->carexpenses->map(function($expense) {
                return [
                    'id' => $expense->id,
                    'note' => $expense->note,
                    'amount_dollar' => $expense->amount_dollar ?? 0,
                    'amount_dinar' => $expense->amount_dinar ?? 0,
                    'created' => $expense->created,
                ];
            });

            // Calculate total additional expenses in dollars
            $totalAdditionalExpenses = $car->carexpenses->sum('amount_dollar');

            // Calculate total purchases (including additional expenses)
            $totalPurchases = ($car->total ?? 0) + $totalAdditionalExpenses;

            // Calculate total sales
            $totalSales = $car->total_s ?? 0;

            // Calculate profit (sales - purchases including additional expenses)
            $profit = $totalSales - $totalPurchases;

            return response()->json([
                'exists' => true,
                'car' => [
                    'id' => $car->id,
                    'vin' => $car->vin,
                    'car_type' => $car->car_type,
                    'year' => $car->year,
                    'car_color' => $car->car_color,
                    'note' => $car->note,
                    'client' => $car->client ? [
                        'id' => $car->client->id,
                        'name' => $car->client->name,
                    ] : null,
                    'purchase_expenses' => $purchaseExpenses,
                    'sales_expenses' => $salesExpenses,
                    'additional_expenses' => $additionalExpenses,
                    'total_purchases' => $totalPurchases, // إجمالي المشتريات (بما في ذلك المصاريف الإضافية)
                    'total_sales' => $totalSales, // إجمالي المبيعات
                    'profit' => $profit, // الربح
                    'paid' => $car->paid ?? 0,
                    'discount' => $car->discount ?? 0,
                    'results' => $car->results ?? 0,
                ]
            ], 200);
        }

        // If car not found in current project, check in second project
        $secondProjectUrl = env('SECOND_PROJECT_URL');
        $apiKey = env('API_KEY');
        
        if ($secondProjectUrl && $apiKey) {
            try {
                $response = Http::withHeaders([
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json',
                ])->timeout(5)->get(rtrim($secondProjectUrl, '/') . '/api/external/checkCar', [
                    'vin' => $vin,
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    if (isset($responseData['exists']) && $responseData['exists']) {
                        return response()->json([
                            'exists' => true,
                            'from_second_project' => true,
                            'message' => 'تم العثور على السيارة في المشروع الثاني',
                            'car' => $responseData['car'] ?? null
                        ], 200);
                    }
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Connection timeout or network error
                \Log::warning('Failed to connect to second project: ' . $e->getMessage());
            } catch (\Exception $e) {
                // Other errors
                \Log::warning('Failed to check car in second project: ' . $e->getMessage());
            }
        }

        return response()->json(['exists' => false], 200);
    }

    /**
     * External API: Check car by VIN (for second project)
     * This endpoint is public but requires API key
     */
    public function externalCheckCar(Request $request)
    {
        $vin = $request->get('vin');
        
        if (!$vin) {
            return response()->json(['exists' => false], 200);
        }

        // Get all owner_ids or use a specific one
        // For now, we'll search across all owners
        $car = Car::where('vin', $vin)
            ->with(['client', 'carexpenses'])
            ->first();

        if ($car) {
            // Calculate expenses from purchases
            $purchaseExpenses = [
                'total' => $car->total ?? 0,
                'expenses' => $car->expenses ?? 0,
                'checkout' => $car->checkout ?? 0,
                'shipping_dolar' => $car->shipping_dolar ?? 0,
                'coc_dolar' => $car->coc_dolar ?? 0,
                'dinar' => $car->dinar ?? 0,
                'land_shipping' => $car->land_shipping ?? 0,
                'land_shipping_dinar' => $car->land_shipping_dinar ?? 0,
            ];

            // Calculate expenses from sales
            $salesExpenses = [
                'total_s' => $car->total_s ?? 0,
                'expenses_s' => $car->expenses_s ?? 0,
                'checkout_s' => $car->checkout_s ?? 0,
                'shipping_dolar_s' => $car->shipping_dolar_s ?? 0,
                'coc_dolar_s' => $car->coc_dolar_s ?? 0,
                'dinar_s' => $car->dinar_s ?? 0,
                'land_shipping_s' => $car->land_shipping_s ?? 0,
                'land_shipping_dinar_s' => $car->land_shipping_dinar_s ?? 0,
            ];

            // Get additional expenses
            $additionalExpenses = $car->carexpenses->map(function($expense) {
                return [
                    'id' => $expense->id,
                    'note' => $expense->note,
                    'amount_dollar' => $expense->amount_dollar ?? 0,
                    'amount_dinar' => $expense->amount_dinar ?? 0,
                    'created' => $expense->created,
                ];
            });

            // Calculate profit
            $profit = ($car->total_s ?? 0) - ($car->total ?? 0);

            return response()->json([
                'exists' => true,
                'car' => [
                    'id' => $car->id,
                    'vin' => $car->vin,
                    'car_type' => $car->car_type,
                    'year' => $car->year,
                    'car_color' => $car->car_color,
                    'note' => $car->note,
                    'client' => $car->client ? [
                        'id' => $car->client->id,
                        'name' => $car->client->name,
                    ] : null,
                    'purchase_expenses' => $purchaseExpenses,
                    'sales_expenses' => $salesExpenses,
                    'additional_expenses' => $additionalExpenses,
                    'profit' => $profit,
                    'paid' => $car->paid ?? 0,
                    'discount' => $car->discount ?? 0,
                    'results' => $car->results ?? 0,
                ]
            ], 200);
        }

        return response()->json(['exists' => false], 200);
    }

    /**
     * External API: Get sales data (for trader) - similar to trader page
     * This endpoint is public but requires API key
     */
    public function externalGetSales(Request $request)
    {
        $client_id = $request->get('id') ?? $request->get('client_id');
        
        if (!$client_id) {
            return response()->json(['error' => 'id (client_id) is required'], 400);
        }
        
        // Get all cars for this client
        $cars = Car::where('client_id', $client_id)
            ->with(['client', 'contract', 'exitcar'])
            ->get();
        
        // Calculate totals
        $cars_sum = $cars->sum('total_s');
        $cars_paid = $cars->sum('paid');
        $cars_discount = $cars->sum('discount');
        $cars_need_paid = $cars_sum - ($cars_paid + $cars_discount);
        $car_total = $cars->count();
        $car_total_unpaid = $cars->where('results', 0)->count();
        $car_total_uncomplete = $cars->where('results', 1)->count();
        $car_total_complete = $cars->where('results', 2)->count();

        // Format cars data
        $carsData = $cars->map(function($car) {
            return [
                'id' => $car->id,
                'vin' => $car->vin,
                'car_type' => $car->car_type,
                'year' => $car->year,
                'car_color' => $car->car_color,
                'date' => $car->date,
                'total_s' => $car->total_s ?? 0,
                'total' => $car->total ?? 0,
                'paid' => $car->paid ?? 0,
                'discount' => $car->discount ?? 0,
                'profit' => ($car->total_s ?? 0) - ($car->total ?? 0),
                'results' => $car->results ?? 0,
                'note' => $car->note,
                'client' => $car->client ? [
                    'id' => $car->client->id,
                    'name' => $car->client->name,
                ] : null,
                'has_contract' => $car->contract ? true : false,
                'is_exit' => $car->is_exit ?? 0,
            ];
        });

        return response()->json([
            'success' => true,
            'cars' => $carsData,
            'summary' => [
                'car_total' => $car_total,
                'car_total_unpaid' => $car_total_unpaid,
                'car_total_uncomplete' => $car_total_uncomplete,
                'car_total_complete' => $car_total_complete,
                'cars_sum' => $cars_sum,
                'cars_paid' => $cars_paid,
                'cars_discount' => $cars_discount,
                'cars_need_paid' => $cars_need_paid,
            ]
        ], 200);
    }

    /**
     * External API: Get payments for a client (for trader)
     * This endpoint is public but requires API key
     */
    public function externalGetPayments(Request $request)
    {
        $client_id = $request->get('client_id');
        $from = $request->get('from');
        $to = $request->get('to');
        
        if (!$client_id) {
            return response()->json(['error' => 'client_id is required'], 400);
        }

        $client = User::with('wallet')->where('id', $client_id)->first();
        
        if (!$client || !$client->wallet) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        // Get payments (transactions with type 'out' and is_pay = 1)
        $query = Transactions::where('wallet_id', $client->wallet->id)
            ->where('type', 'out')
            ->where('is_pay', 1)
            ->where('amount', '<', 0) // Payments are negative
            ->orderBy('created', 'desc');

        if ($from && $to) {
            $query->whereBetween('created', [$from, $to]);
        }

        $payments = $query->get()->map(function($transaction) {
            return [
                'id' => $transaction->id,
                'amount' => abs($transaction->amount), // Convert to positive
                'currency' => $transaction->currency ?? '$',
                'description' => $transaction->description,
                'date' => $transaction->created,
                'type' => $transaction->type,
            ];
        });

        // Calculate totals
        $totalPaymentsDollar = $payments->where('currency', '$')->sum('amount');
        $totalPaymentsDinar = $payments->where('currency', 'IQD')->sum('amount');

        return response()->json([
            'success' => true,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'wallet_balance' => $client->wallet->balance ?? 0,
            ],
            'payments' => $payments,
            'summary' => [
                'total_payments_dollar' => $totalPaymentsDollar,
                'total_payments_dinar' => $totalPaymentsDinar,
                'count' => $payments->count(),
            ]
        ], 200);
    }

    /**
     * Update car expenses and recalculate profit
     */
    public function updateCarExpenses(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $carId = $request->get('car_id');
        
        $car = Car::where('id', $carId)
            ->where('owner_id', $owner_id)
            ->first();

        if (!$car) {
            return response()->json(['error' => 'Car not found'], 404);
        }

        // Update purchase expenses if provided
        if ($request->has('purchase_expenses')) {
            $purchaseData = $request->get('purchase_expenses');
            $updateData = [];
            
            if (isset($purchaseData['expenses'])) $updateData['expenses'] = $purchaseData['expenses'];
            if (isset($purchaseData['checkout'])) $updateData['checkout'] = $purchaseData['checkout'];
            if (isset($purchaseData['shipping_dolar'])) $updateData['shipping_dolar'] = $purchaseData['shipping_dolar'];
            if (isset($purchaseData['coc_dolar'])) $updateData['coc_dolar'] = $purchaseData['coc_dolar'];
            if (isset($purchaseData['dinar'])) $updateData['dinar'] = $purchaseData['dinar'];
            if (isset($purchaseData['land_shipping'])) $updateData['land_shipping'] = $purchaseData['land_shipping'];
            if (isset($purchaseData['land_shipping_dinar'])) $updateData['land_shipping_dinar'] = $purchaseData['land_shipping_dinar'];
            if (isset($purchaseData['dolar_price'])) $updateData['dolar_price'] = $purchaseData['dolar_price'];

            // Recalculate total
            $dolar_price = $updateData['dolar_price'] ?? $car->dolar_price ?? 1;
            $calc_rate = $dolar_price;
            if ($calc_rate == 0) {
                $calc_rate = 1;
            } elseif ($calc_rate > 9999) {
                $calc_rate = $calc_rate / 100;
            }

            $checkout = $updateData['checkout'] ?? $car->checkout ?? 0;
            $shipping_dolar = $updateData['shipping_dolar'] ?? $car->shipping_dolar ?? 0;
            $coc_dolar = $updateData['coc_dolar'] ?? $car->coc_dolar ?? 0;
            $dinar = $updateData['dinar'] ?? $car->dinar ?? 0;
            $expenses = $updateData['expenses'] ?? $car->expenses ?? 0;
            $land_shipping = $updateData['land_shipping'] ?? $car->land_shipping ?? 0;
            $land_shipping_dinar = $updateData['land_shipping_dinar'] ?? $car->land_shipping_dinar ?? 0;

            $updateData['total'] = (int)(($checkout + $shipping_dolar + $coc_dolar + (int)($dinar / $calc_rate) + (int)($land_shipping_dinar / $calc_rate) + $expenses + $land_shipping) ?? 0);
            
            $car->update($updateData);
        }

        // Update sales expenses if provided
        if ($request->has('sales_expenses')) {
            $salesData = $request->get('sales_expenses');
            $updateData = [];
            
            if (isset($salesData['expenses_s'])) $updateData['expenses_s'] = $salesData['expenses_s'];
            if (isset($salesData['checkout_s'])) $updateData['checkout_s'] = $salesData['checkout_s'];
            if (isset($salesData['shipping_dolar_s'])) $updateData['shipping_dolar_s'] = $salesData['shipping_dolar_s'];
            if (isset($salesData['coc_dolar_s'])) $updateData['coc_dolar_s'] = $salesData['coc_dolar_s'];
            if (isset($salesData['dinar_s'])) $updateData['dinar_s'] = $salesData['dinar_s'];
            if (isset($salesData['land_shipping_s'])) $updateData['land_shipping_s'] = $salesData['land_shipping_s'];
            if (isset($salesData['land_shipping_dinar_s'])) $updateData['land_shipping_dinar_s'] = $salesData['land_shipping_dinar_s'];
            if (isset($salesData['dolar_price_s'])) $updateData['dolar_price_s'] = $salesData['dolar_price_s'];

            // Recalculate total_s
            $dolar_price_s = $updateData['dolar_price_s'] ?? $car->dolar_price_s ?? 1;
            $calc_rate_s = $dolar_price_s;
            if ($calc_rate_s == 0) {
                $calc_rate_s = 1;
            } elseif ($calc_rate_s > 9999) {
                $calc_rate_s = $calc_rate_s / 100;
            }

            $checkout_s = $updateData['checkout_s'] ?? $car->checkout_s ?? 0;
            $shipping_dolar_s = $updateData['shipping_dolar_s'] ?? $car->shipping_dolar_s ?? 0;
            $coc_dolar_s = $updateData['coc_dolar_s'] ?? $car->coc_dolar_s ?? 0;
            $dinar_s = $updateData['dinar_s'] ?? $car->dinar_s ?? 0;
            $expenses_s = $updateData['expenses_s'] ?? $car->expenses_s ?? 0;
            $land_shipping_s = $updateData['land_shipping_s'] ?? $car->land_shipping_s ?? 0;
            $land_shipping_dinar_s = $updateData['land_shipping_dinar_s'] ?? $car->land_shipping_dinar_s ?? 0;

            $updateData['total_s'] = (int)(($checkout_s + $shipping_dolar_s + $coc_dolar_s + (int)($dinar_s / $calc_rate_s) + (int)($land_shipping_dinar_s / $calc_rate_s) + $expenses_s + $land_shipping_s) ?? 0);
            
            $car->update($updateData);
        }

        // Recalculate profit
        $car->refresh();
        $profit = ($car->total_s ?? 0) - ($car->total ?? 0);
        $car->update(['profit' => $profit]);

        // Update note if provided
        if ($request->has('note')) {
            $car->update(['note' => $request->get('note')]);
        }

        return response()->json([
            'success' => true,
            'car' => $car->fresh(['client', 'carexpenses']),
            'profit' => $profit
        ], 200);
    }
    }