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
use Carbon\Carbon;
use App\Models\Transactions;
use App\Models\Contract;
use App\Models\InternalSale;
use App\Models\CarSale;
use App\Models\SalePayment;
use App\Models\BuyerPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\AccountingCacheService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        return Inertia::render('Users/Index', [
            'url' => $this->url,
            'usersType' => $this->assignableStaffTypes(),
            'typeLabels' => $this->userTypeLabels(),
            'branches' => $this->branchOptions(),
        ]);
    }

    /**
     * فروع النظام: owner_id 1 = أربيل ، 2 = كركوك
     */
    protected function branchOptions(): array
    {
        return [
            ['id' => 1, 'name' => 'أربيل'],
            ['id' => 2, 'name' => 'كركوك'],
        ];
    }

    protected function allowedBranchIds(): array
    {
        return array_map(fn ($b) => (int) $b['id'], $this->branchOptions());
    }

    /**
     * Client / internal-sales-client type IDs — staff user management excludes these.
     */
    protected function excludedClientTypeIds(): array
    {
        $ids = array_values(array_filter([
            (int) ($this->accounting->userClient() ?? 0),
            (int) ($this->accounting->userInternalSalesClient() ?? 0),
        ]));

        if ($ids === []) {
            $ids = UserType::query()
                ->whereIn('name', ['client', 'internal_sales_client'])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return $ids;
    }

    /**
     * Roles that can be assigned to staff (non-client) users.
     */
    protected function assignableStaffTypes()
    {
        $excluded = $this->excludedClientTypeIds();

        return UserType::query()
            ->when($excluded !== [], fn ($q) => $q->whereNotIn('id', $excluded))
            ->orderBy('id')
            ->get(['id', 'name']);
    }

    /**
     * Arabic labels for user_type.name values.
     */
    protected function userTypeLabels(): array
    {
        return [
            'admin' => 'مدير النظام',
            'client' => 'زبون',
            'account' => 'حساب / صندوق',
            'selesKirkuk' => 'مبيعات',
            'car_expenses' => 'مصاريف سيارات',
            'car_contract_user' => 'مستخدم عقود',
            'car_contract' => 'إدارة عقود',
            'internal_sales_client' => 'زبون مبيعات داخلية',
            'shipping_company' => 'شركة شحن',
        ];
    }

    protected function assertStaffUser(User $user): void
    {
        if (in_array((int) $user->type_id, $this->excludedClientTypeIds(), true)) {
            abort(403, 'لا يمكن تعديل حسابات الزبائن من هذه الصفحة.');
        }
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
        // إضافة has_internal_sales إلى بيانات client
        if ($client) {
            $client->has_internal_sales = (bool)($client->has_internal_sales ?? false);
        }
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        $config = \App\Models\SystemConfig::first();
        return Inertia::render('Clients/Show', ['url'=>$this->url,'client'=>$client,'clients'=>$clients,'client_id'=>$id,'q'=>$q,'config'=>$config]);
    }

    public function internalSales($id)
    {
        $owner_id = Auth::user()->owner_id;
        $internalSalesClientTypeId = $this->accounting->userInternalSalesClient();
        
        // تحميل زبائن المبيعات الداخلية فقط (النوع الجديد) أو الزبائن المفعلين
        // مع استبعاد التاجر نفسه من القائمة
        $clients = User::with('wallet')
            ->where('owner_id', $owner_id)
            ->where('id', '!=', $id) // استبعاد التاجر نفسه
            ->where(function($query) use ($internalSalesClientTypeId) {
                $query->where('type_id', $internalSalesClientTypeId)
                      ->orWhere('has_internal_sales', true);
            })
            ->get();
            
        $client = User::find($id);
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        return Inertia::render('Clients/InternalSales', [
            'url' => $this->url,
            'client' => $client,
            'clients' => $clients,
            'client_id' => $id,
        ]);
    }
    public function show ()
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        return Inertia::render('Users/Index', [
            'url' => $this->url,
            'usersType' => $this->assignableStaffTypes(),
            'typeLabels' => $this->userTypeLabels(),
            'branches' => $this->branchOptions(),
        ]);
    }
    public function getIndex()
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        $auth = Auth::user();
        $excluded = $this->excludedClientTypeIds();
        $q = trim((string) request()->input('q', ''));
        $typeId = request()->input('type_id');
        $branchId = request()->input('owner_id');
        $isAdmin = (int) $auth->type_id === 1;

        $query = User::with(['userType:id,name', 'wallet:id,user_id,balance'])
            ->when(! $isAdmin, fn ($builder) => $builder->where('owner_id', $auth->owner_id))
            ->when($isAdmin && $branchId !== null && $branchId !== '', fn ($builder) => $builder->where('owner_id', (int) $branchId))
            ->when($excluded !== [], fn ($builder) => $builder->whereNotIn('type_id', $excluded))
            ->when($typeId, fn ($builder) => $builder->where('type_id', (int) $typeId))
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($inner) use ($q) {
                    $inner->where('name', 'LIKE', "%{$q}%")
                        ->orWhere('email', 'LIKE', "%{$q}%")
                        ->orWhere('phone', 'LIKE', "%{$q}%");
                });
            })
            ->orderByDesc('id');

        return Response::json($query->paginate(15), 200);
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
        
        // تحسين مفتاح الكاش - إزالة الصفحة من المفتاح لتجنب التكرار
        $cacheKey = 'clients_fast_' . md5($q . $owner_id . $userClient . $from . $to);
        $cacheDuration = 3600; // كاش ساعة واحدة

        // استخدام الكاش مع استعلام محسن
        $query = Cache::remember($cacheKey, $cacheDuration, function () use ($owner_id, $userClient, $q, $from, $to) {
            // بناء استعلام أساسي بسيط وسريع
            $baseQuery = DB::table('users')
                ->select([
                    'users.id', 
                    'users.name', 
                    'users.phone', 
                    'users.has_internal_sales',
                    'users.show_in_dashboard',
                    'users.created_at'
                ])
                ->where('users.owner_id', $owner_id)
                ->where('users.type_id', $userClient);
            
            // إضافة الرصيد فقط - أسرع استعلام ممكن
            $baseQuery->addSelect([
                DB::raw('(SELECT COALESCE(balance, 0) FROM wallets WHERE user_id = users.id LIMIT 1) as balance')
            ]);
            
            // تطبيق البحث المحسن - فقط عند الحاجة (مع استثناء فلاتر الخيارات الجاهزة)
            if ($q && !in_array($q, ['debit', 'box_movement'])) {
                $baseQuery->where(function ($query) use ($q) {
                    $query->where('users.name', 'LIKE', '%' . $q . '%')
                          ->orWhereExists(function ($subQuery) use ($q) {
                              $subQuery->select(DB::raw(1))
                                      ->from('car')
                                      ->whereColumn('car.client_id', 'users.id')
                                      ->where(function ($carQuery) use ($q) {
                                          $carQuery->where('car.vin', 'LIKE', '%' . $q . '%')
                                                  ->orWhere('car.car_number', 'LIKE', '%' . $q . '%');
                                      });
                          });
                });
            }

            // فلتر: التجار الذين عندهم حركة على القاسة (وليس على المحفظة)
            // الحركات على القاسة هي transactions من نوع inUserBox / outUserBox
            // والتي تكون مرتبطة بالتاجر عبر morphed_id
            if ($q === 'box_movement') {
                $baseQuery->whereExists(function ($subQuery) use ($from, $to) {
                    $subQuery->select(DB::raw(1))
                            ->from('transactions')
                            ->whereColumn('transactions.morphed_id', 'users.id')
                            ->where('transactions.morphed_type', 'App\\Models\\User')
                            ->whereIn('transactions.type', ['inUserBox', 'outUserBox'])
                            ->whereNull('transactions.deleted_at');

                    if ($from && $to) {
                        $subQuery->whereBetween('transactions.created_at', [$from, $to]);
                    }
                });
            }
            
            // تطبيق فلترة التاريخ (فقط في الفلاتر غير الخاصة بالقاسة، لأنها مطبقة داخل subquery)
            if ($from && $to && $q !== 'box_movement') {
                $baseQuery->whereBetween('users.created_at', [$from, $to]);
            }
            
            // ترتيب النتائج
            $baseQuery->orderByRaw('(SELECT COALESCE(balance, 0) FROM wallets WHERE user_id = users.id LIMIT 1) DESC');
            
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

        // تطبيق التصفح السريع (يشمل فلتر القاسة لتوافق InfiniteLoading)
        $paginationLimit = 25;
        $currentPage = max(1, (int)$page);
        
        $paginatedData = collect($query)
            ->forPage($currentPage, $paginationLimit)
            ->values()
            ->toArray();
        
        return response()->json(['data' => $paginatedData], 200);
    }
    
    
    
    
    public function create()
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        return Inertia::render('Users/Create', [
            'usersType' => $this->assignableStaffTypes(),
            'typeLabels' => $this->userTypeLabels(),
        ]);
    }

    public function store(Request $request)
    {
        $assignableIds = $this->assignableStaffTypes()->pluck('id')->all();

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email',
            'password' => ['required', Rules\Password::defaults()],
            'userType' => 'required|integer|in:' . implode(',', $assignableIds ?: [0]),
            'phone' => 'nullable|string|max:255',
            'organizer_name' => 'nullable|string|max:255',
        ])->validate();

        $user = User::create([
            'name' => $validated['name'],
            'type_id' => $validated['userType'],
            'email' => $validated['email'],
            'created' => Carbon::now()->format('Y-m-d'),
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'organizer_name' => $validated['organizer_name'] ?? null,
            'owner_id' => Auth::user()->owner_id,
        ]);

        Wallet::create(['user_id' => $user->id]);
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        return redirect()->route('users.index')->with('success', 'تم إنشاء المستخدم بنجاح');
    }
    public function clientsStore(Request $request)
    {
        $year_date = Carbon::now()->format('Y');
        $owner_id = Auth::user()->owner_id;

        Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            'show_in_dashboard' => 'nullable|boolean',
        ])->validate();

        User::ensureOptionalColumns();

        $user = User::create([
            'name' => $request->name,
            'type_id' => $this->accounting->userClient(),
            'phone' => $request->phone,
            'year_date' => $year_date,
            'owner_id' => $owner_id,
            'created' => Carbon::now()->format('Y-m-d'),
            'show_in_dashboard' => $request->has('show_in_dashboard')
                ? (bool) $request->boolean('show_in_dashboard')
                : true,
        ]);

        Wallet::create(['user_id' => $user->id]);
        $this->forgetClientsListCache($owner_id);

        return Response::json($user, 200);
    }

    public function clientsEdit(Request $request)
    {
        Validator::make($request->all(), [
            'id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'has_internal_sales' => 'nullable|boolean',
            'show_in_dashboard' => 'nullable|boolean',
        ])->validate();

        $user = User::where('id', $request->id)
            ->where('owner_id', Auth::user()->owner_id)
            ->firstOrFail();

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'has_internal_sales' => (bool) ($request->has_internal_sales ?? false),
            'show_in_dashboard' => (bool) ($request->show_in_dashboard ?? false),
        ]);

        $this->forgetClientsListCache(Auth::user()->owner_id);

        return Response::json($user->fresh(), 200);
    }

    protected function forgetClientsListCache($ownerId): void
    {
        $userClient = $this->accounting->userClient() ?? 0;
        $queries = ['', '0', 'debit', 'box_movement'];
        $ranges = [
            [0, 0],
            ['0', '0'],
            ['', ''],
        ];

        foreach ($queries as $q) {
            foreach ($ranges as [$from, $to]) {
                Cache::forget('clients_fast_' . md5($q . $ownerId . $userClient . $from . $to));
            }
        }
    }
    public function updateClientPhone(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'userId' => 'required|integer|exists:users,id',
            'phone' => 'nullable|string|max:255',
        ])->validate();

        $user = User::where('id', $validated['userId'])
            ->where('owner_id', Auth::user()->owner_id)
            ->firstOrFail();

        $user->phone = $validated['phone'];
        $user->save();

        return response()->json([
            'message' => 'Client phone updated successfully',
            'phone' => $user->phone,
        ], 200);
    }

    // Internal Sales Functions
    public function toggleInternalSales(Request $request)
    {
        User::ensureOptionalColumns();

        $validated = Validator::make($request->all(), [
            'client_id' => 'required|integer|exists:users,id',
            'has_internal_sales' => 'required|boolean',
        ])->validate();

        $client = User::where('id', $validated['client_id'])
            ->where('owner_id', Auth::user()->owner_id)
            ->firstOrFail();

        $client->has_internal_sales = (bool) $validated['has_internal_sales'];
        $client->save();
        $this->forgetClientsListCache(Auth::user()->owner_id);

        return response()->json([
            'message' => 'Internal sales status updated successfully',
            'has_internal_sales' => (bool) $client->has_internal_sales,
        ], 200);
    }

    public function toggleShowInDashboard(Request $request)
    {
        User::ensureOptionalColumns();

        $validated = Validator::make($request->all(), [
            'client_id' => 'required|integer|exists:users,id',
            'show_in_dashboard' => 'required|boolean',
        ])->validate();

        $client = User::where('id', $validated['client_id'])
            ->where('owner_id', Auth::user()->owner_id)
            ->firstOrFail();

        $client->show_in_dashboard = (bool) $validated['show_in_dashboard'];
        $client->save();
        $this->forgetClientsListCache(Auth::user()->owner_id);

        return response()->json([
            'message' => 'Show in dashboard status updated successfully',
            'show_in_dashboard' => (bool) $client->show_in_dashboard,
        ], 200);
    }

    public function getInternalSales(Request $request)
    {
        $merchant_id = $request->input('client_id'); // التاجر/المالك
        
        if (!$merchant_id) {
            return response()->json(['error' => 'Client ID (merchant) is required'], 400);
        }

        // التحقق من أن التاجر موجود
        $merchant = User::where('id', $merchant_id)
            ->where('owner_id', Auth::user()->owner_id)
            ->firstOrFail();

        // جلب جميع المبيعات الداخلية حيث السيارة تخص هذا التاجر فقط
        // لا نجلب المبيعات حيث التاجر هو المشتري (هذا خطأ)
        $sales = InternalSale::with(['car.client', 'client'])
            ->whereHas('car', function($query) use ($merchant_id) {
                $query->where('client_id', $merchant_id); // السيارة تخص التاجر
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals
        $totalSales = $sales->sum('sale_price');
        $totalPaid = $sales->sum('paid_amount');
        $totalExpenses = $sales->sum('expenses');
        $totalProfit = $sales->sum('profit');

        // جلب ملاحظات الدفعات لكل مبيعة
        $saleIds = $sales->pluck('id')->toArray();
        $paymentsNotes = BuyerPayment::whereIn('internal_sale_id', $saleIds)
            ->whereNotNull('note')
            ->where('note', '!=', '')
            ->select('internal_sale_id', 'note')
            ->get()
            ->groupBy('internal_sale_id')
            ->map(function($payments) {
                // جمع جميع الملاحظات غير الفارغة
                return $payments->pluck('note')->filter()->unique()->values()->toArray();
            });

        // Ensure numeric values are returned as numbers
        $salesData = $sales->map(function($sale) use ($paymentsNotes) {
            $paymentNotes = $paymentsNotes->get($sale->id, []);
            
            return [
                'id' => $sale->id,
                'client_id' => $sale->client_id,
                'car_id' => $sale->car_id,
                'car_price' => (float) ($sale->car_price ?? 0),
                'shipping' => (float) ($sale->shipping ?? 0),
                'sale_price' => (float) $sale->sale_price,
                'paid_amount' => (float) $sale->paid_amount,
                'expenses' => (float) $sale->expenses,
                'additional_expenses' => (float) ($sale->additional_expenses ?? 0),
                'profit' => (float) $sale->profit,
                'note' => $sale->note,
                'payment_notes' => $paymentNotes, // ملاحظات الدفعات
                'sale_date' => $sale->sale_date ? Carbon::parse($sale->sale_date)->format('Y-m-d') : null,
                'created_at' => $sale->created_at,
                'updated_at' => $sale->updated_at,
                'car' => $sale->car,
                'client' => $sale->client,
            ];
        });

        return response()->json([
            'sales' => $salesData,
            'totals' => [
                'total_sales' => (float) $totalSales,
                'total_paid' => (float) $totalPaid,
                'total_expenses' => (float) $totalExpenses,
                'total_profit' => (float) $totalProfit,
            ],
            'has_internal_sales' => $merchant->has_internal_sales ?? false,
            'client' => [
                'id' => $merchant->id,
                'name' => $merchant->name,
                'phone' => $merchant->phone,
            ],
        ], 200);
    }

    public function getUnsoldCars(Request $request)
    {
        $merchant_id = $request->input('client_id'); // التاجر/المالك
        
        if (!$merchant_id) {
            return response()->json(['error' => 'Client ID (merchant) is required'], 400);
        }

        // التحقق من أن التاجر موجود (لا نحتاج للتحقق من نوعه)
        $merchant = User::where('id', $merchant_id)
            ->where('owner_id', Auth::user()->owner_id)
            ->firstOrFail();

        // Get all cars for this merchant
        $allCars = Car::where('client_id', $merchant_id)
            ->select('id', 'car_type', 'year', 'vin', 'car_number', 'total_s', 'car_price')
            ->get();

        // Get cars that already have internal sales (regardless of who bought them)
        $soldCarIds = InternalSale::whereIn('car_id', $allCars->pluck('id'))
            ->pluck('car_id')
            ->toArray();

        // Filter out cars that are already sold
        $unsoldCars = $allCars->filter(function($car) use ($soldCarIds) {
            return !in_array($car->id, $soldCarIds);
        })->values();

        // Ensure total_s and car_price are returned as numbers
        $unsoldCarsData = $unsoldCars->map(function($car) {
            return [
                'id' => $car->id,
                'car_type' => $car->car_type,
                'year' => $car->year,
                'vin' => $car->vin,
                'car_number' => $car->car_number,
                'total_s' => (float) ($car->total_s ?? 0),
                'car_price' => (float) ($car->car_price ?? 0),
            ];
        });

        return response()->json([
            'cars' => $unsoldCarsData,
        ], 200);
    }

    public function addInternalSale(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'client_id' => 'nullable|integer|exists:users,id',
            'client_name' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:255',
            'car_id' => 'required|integer|exists:car,id',
            'car_price' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'expenses' => 'nullable|numeric|min:0',
            'additional_expenses' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'sale_date' => 'nullable|date',
        ])->validate();

        $owner_id = Auth::user()->owner_id;
        DB::beginTransaction();
        try {
            // إنشاء عميل جديد إذا لم يكن موجوداً (نوع مبيعات داخلية)
            $client_id = $validated['client_id'];
            if (!$client_id && $validated['client_name']) {
                $internalSalesClientTypeId = $this->accounting->userInternalSalesClient();
                
                Log::info('addInternalSale: Checking for internal_sales_client user type', [
                    'owner_id' => $owner_id,
                    'cache_value' => $internalSalesClientTypeId,
                    'client_name' => $validated['client_name'],
                ]);
                
                // إذا لم يكن موجوداً في الكاش، جلب من قاعدة البيانات مباشرة
                if (!$internalSalesClientTypeId) {
                    Log::warning('addInternalSale: internal_sales_client not found in cache, checking database', [
                        'owner_id' => $owner_id,
                    ]);
                    
                    $internalSalesClientTypeId = \App\Models\UserType::where('name', 'internal_sales_client')->first()?->id;
                    
                    // إذا لم يكن موجوداً في قاعدة البيانات أيضاً، إرجاع خطأ تفصيلي
                    if (!$internalSalesClientTypeId) {
                        // جلب جميع أنواع المستخدمين الموجودة للمساعدة في التشخيص
                        $allUserTypes = \App\Models\UserType::select('id', 'name')->get()->toArray();
                        $cacheStatus = Cache::get('user_type_internal_sales_client');
                        
                        Log::error('addInternalSale: internal_sales_client user type not found', [
                            'owner_id' => $owner_id,
                            'cache_status' => $cacheStatus,
                            'available_user_types' => $allUserTypes,
                            'user_agent' => $request->userAgent(),
                            'request_data' => $validated,
                        ]);
                        
                        return response()->json([
                            'error' => 'نوع المستخدم للمبيعات الداخلية غير موجود. يرجى تشغيل migration أولاً.',
                            'debug' => [
                                'cache_value' => $cacheStatus,
                                'available_user_types' => $allUserTypes,
                                'searched_for' => 'internal_sales_client',
                            ],
                        ], 500);
                    }
                    
                    // تحديث الكاش
                    Log::info('addInternalSale: Found internal_sales_client in database, updating cache', [
                        'type_id' => $internalSalesClientTypeId,
                        'owner_id' => $owner_id,
                    ]);
                    
                    \Illuminate\Support\Facades\Cache::rememberForever('user_type_internal_sales_client', fn () => $internalSalesClientTypeId);
                }
                
                $client = User::create([
                    'name' => $validated['client_name'],
                    'phone' => $validated['client_phone'] ?? null,
                    'type_id' => $internalSalesClientTypeId,
                    'owner_id' => $owner_id,
                    'created' => Carbon::now()->format('Y-m-d'),
                    'year_date' => Carbon::now()->format('Y'),
                    'has_internal_sales' => true, // تمكين المبيعات الداخلية
                ]);
                Wallet::create(['user_id' => $client->id, 'balance' => 0]);
                $client_id = $client->id;
            }

            if (!$client_id) {
                return response()->json([
                    'error' => 'يجب تحديد عميل أو إدخال بيانات عميل جديد',
                ], 400);
            }

            // Verify client belongs to owner and is internal sales client or has internal sales enabled
            $internalSalesClientTypeId = $this->accounting->userInternalSalesClient();
            
            Log::debug('addInternalSale: Verifying client', [
                'client_id' => $client_id,
                'owner_id' => $owner_id,
                'internal_sales_client_type_id' => $internalSalesClientTypeId,
            ]);
            
            $client = User::where('id', $client_id)
                ->where('owner_id', $owner_id)
                ->where(function($query) use ($internalSalesClientTypeId) {
                    $query->where('type_id', $internalSalesClientTypeId)
                          ->orWhere('has_internal_sales', true);
                })
                ->first();
            
            if (!$client) {
                Log::warning('addInternalSale: Client verification failed - client does not match internal sales criteria', [
                    'client_id' => $client_id,
                    'owner_id' => $owner_id,
                    'internal_sales_client_type_id' => $internalSalesClientTypeId,
                    'client_exists' => User::where('id', $client_id)->exists(),
                    'client_owner_id' => User::where('id', $client_id)->value('owner_id'),
                    'client_type_id' => User::where('id', $client_id)->value('type_id'),
                    'client_has_internal_sales' => User::where('id', $client_id)->value('has_internal_sales'),
                ]);
                
                // محاولة العثور على العميل بدون التحقق من نوعه
                $client = User::where('id', $client_id)
                    ->where('owner_id', $owner_id)
                    ->first();
                    
                if (!$client) {
                    Log::error('addInternalSale: Client not found or does not belong to owner', [
                        'client_id' => $client_id,
                        'owner_id' => $owner_id,
                    ]);
                    throw new \Exception('العميل غير موجود أو لا ينتمي للمالك المحدد');
                }
                
                throw new \Exception('العميل غير مفعل للمبيعات الداخلية. يرجى تفعيل المبيعات الداخلية للعميل أو اختيار عميل آخر.');
            }

            // Check if sale already exists for this car
            $existingSale = InternalSale::where('car_id', $validated['car_id'])
                ->first();

            if ($existingSale) {
                return response()->json([
                    'error' => 'هذه السيارة مباعة بالفعل في المبيعات الداخلية',
                ], 400);
            }

            // Get car to get total_s if not provided
            $car = Car::findOrFail($validated['car_id']);
            
            // منع بيع التاجر لنفسه (السيارة تخص التاجر، فلا يمكن أن يكون هو المشتري أيضاً)
            // تحويل القيم إلى أرقام للمقارنة الصحيحة
            $carOwnerId = (int) $car->client_id;
            $buyerId = (int) $client_id;
            
            if ($carOwnerId === $buyerId) {
                return response()->json([
                    'error' => 'لا يمكن بيع السيارة للتاجر نفسه. يجب اختيار عميل آخر.',
                ], 400);
            }
            
            $sale = InternalSale::create([
                'client_id' => $client_id,
                'car_id' => $validated['car_id'],
                'car_price' => $validated['car_price'] ?? ($car->total_s ?? 0),
                'shipping' => $validated['shipping'] ?? ($car->total_s ?? 0),
                'sale_price' => $validated['sale_price'],
                'paid_amount' => $validated['paid_amount'] ?? 0,
                'expenses' => $validated['expenses'] ?? 0,
                'additional_expenses' => $validated['additional_expenses'] ?? 0,
                'note' => $validated['note'] ?? '',
                'sale_date' => $validated['sale_date'] ?? now(),
            ]);

            // إنشاء سجل دفعة إذا كان هناك مبلغ مدفوع عند الشراء
            $paidAmount = $validated['paid_amount'] ?? 0;
            if ($paidAmount > 0) {
                $merchant_id = $car->client_id; // التاجر هو مالك السيارة
                $paymentDate = $validated['sale_date'] ?? Carbon::now()->format('Y-m-d');
                $paymentNote = $validated['payment_note'] ?? '';
                
                BuyerPayment::create([
                    'buyer_id' => $client_id,
                    'merchant_id' => $merchant_id,
                    'internal_sale_id' => $sale->id,
                    'amount' => $paidAmount,
                    'payment_date' => $paymentDate,
                    'owner_id' => $owner_id,
                    'created_by' => Auth::id(),
                    'payment_id' => uniqid('pay_', true),
                    'note' => $paymentNote, // إضافة الملاحظة للدفعة الأولى
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'تم إضافة المبيعة الداخلية بنجاح',
                'sale' => $sale->load(['car', 'client']),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // تسجيل تفصيلي للخطأ
            Log::error('addInternalSale: Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'owner_id' => $owner_id ?? null,
                'request_data' => $validated ?? null,
                'user_id' => Auth::id() ?? null,
            ]);
            
            return response()->json([
                'error' => 'حدث خطأ: ' . $e->getMessage(),
                'debug' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    public function addBulkInternalSale(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'client_id' => 'nullable|integer|exists:users,id',
            'client_name' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:255',
            'cars' => 'required|array|min:1',
            'cars.*.car_id' => 'required|integer|exists:car,id',
            'cars.*.sale_price' => 'required|numeric|min:0',
            'cars.*.paid_amount' => 'nullable|numeric|min:0',
            'cars.*.expenses' => 'nullable|numeric|min:0',
            'cars.*.additional_expenses' => 'nullable|numeric|min:0',
            'sale_date' => 'nullable|date',
        ])->validate();

        $owner_id = Auth::user()->owner_id;
        DB::beginTransaction();
        try {
            // إنشاء عميل جديد إذا لم يكن موجوداً
            $client_id = $validated['client_id'];
            if (!$client_id && $validated['client_name']) {
                $internalSalesClientTypeId = $this->accounting->userInternalSalesClient();
                
                Log::info('addBulkInternalSale: Checking for internal_sales_client user type', [
                    'owner_id' => $owner_id,
                    'cache_value' => $internalSalesClientTypeId,
                    'client_name' => $validated['client_name'],
                    'cars_count' => count($validated['cars'] ?? []),
                ]);
                
                if (!$internalSalesClientTypeId) {
                    Log::warning('addBulkInternalSale: internal_sales_client not found in cache, checking database', [
                        'owner_id' => $owner_id,
                    ]);
                    
                    $internalSalesClientTypeId = \App\Models\UserType::where('name', 'internal_sales_client')->first()?->id;
                    
                    if (!$internalSalesClientTypeId) {
                        // جلب جميع أنواع المستخدمين الموجودة للمساعدة في التشخيص
                        $allUserTypes = \App\Models\UserType::select('id', 'name')->get()->toArray();
                        $cacheStatus = Cache::get('user_type_internal_sales_client');
                        
                        Log::error('addBulkInternalSale: internal_sales_client user type not found', [
                            'owner_id' => $owner_id,
                            'cache_status' => $cacheStatus,
                            'available_user_types' => $allUserTypes,
                            'user_agent' => $request->userAgent(),
                            'cars_count' => count($validated['cars'] ?? []),
                        ]);
                        
                        return response()->json([
                            'error' => 'نوع المستخدم للمبيعات الداخلية غير موجود. يرجى تشغيل migration أولاً.',
                            'debug' => [
                                'cache_value' => $cacheStatus,
                                'available_user_types' => $allUserTypes,
                                'searched_for' => 'internal_sales_client',
                            ],
                        ], 500);
                    }
                    
                    Log::info('addBulkInternalSale: Found internal_sales_client in database, updating cache', [
                        'type_id' => $internalSalesClientTypeId,
                        'owner_id' => $owner_id,
                    ]);
                    
                    \Illuminate\Support\Facades\Cache::rememberForever('user_type_internal_sales_client', fn () => $internalSalesClientTypeId);
                }
                
                $client = User::create([
                    'name' => $validated['client_name'],
                    'phone' => $validated['client_phone'] ?? null,
                    'type_id' => $internalSalesClientTypeId,
                    'owner_id' => $owner_id,
                    'created' => Carbon::now()->format('Y-m-d'),
                    'year_date' => Carbon::now()->format('Y'),
                    'has_internal_sales' => true,
                ]);
                Wallet::create(['user_id' => $client->id, 'balance' => 0]);
                $client_id = $client->id;
            }

            if (!$client_id) {
                return response()->json([
                    'error' => 'يجب تحديد عميل أو إدخال بيانات عميل جديد',
                ], 400);
            }

            // التحقق من العميل
            $internalSalesClientTypeId = $this->accounting->userInternalSalesClient();
            $client = User::where('id', $client_id)
                ->where('owner_id', $owner_id)
                ->where(function($query) use ($internalSalesClientTypeId) {
                    $query->where('type_id', $internalSalesClientTypeId)
                          ->orWhere('has_internal_sales', true);
                })
                ->firstOrFail();

            $createdSales = [];
            $saleDate = $validated['sale_date'] ?? now();

            // إنشاء مبيعة لكل سيارة
            foreach ($validated['cars'] as $carData) {
                $car = Car::findOrFail($carData['car_id']);
                
                // منع بيع التاجر لنفسه
                if ((int) $car->client_id === (int) $client_id) {
                    continue; // تخطي هذه السيارة
                }

                // التحقق من عدم وجود مبيعة سابقة
                $existingSale = InternalSale::where('car_id', $carData['car_id'])->first();
                if ($existingSale) {
                    continue; // تخطي هذه السيارة
                }

                $sale = InternalSale::create([
                    'client_id' => $client_id,
                    'car_id' => $carData['car_id'],
                    'car_price' => $car->total_s ?? 0, // سعر السيارة من total_s
                    'shipping' => 0,
                    'sale_price' => $carData['sale_price'],
                    'paid_amount' => $carData['paid_amount'] ?? 0,
                    'expenses' => $carData['expenses'] ?? ($car->total_s ?? 0),
                    'additional_expenses' => $carData['additional_expenses'] ?? 0,
                    'note' => '',
                    'sale_date' => $saleDate,
                ]);

                // إنشاء سجل دفعة إذا كان هناك مبلغ مدفوع
                $paidAmount = $carData['paid_amount'] ?? 0;
                if ($paidAmount > 0) {
                    $merchant_id = $car->client_id;
                    $paymentDate = $saleDate;
                    
                    BuyerPayment::create([
                        'buyer_id' => $client_id,
                        'merchant_id' => $merchant_id,
                        'internal_sale_id' => $sale->id,
                        'amount' => $paidAmount,
                        'payment_date' => $paymentDate,
                        'owner_id' => $owner_id,
                        'created_by' => Auth::id(),
                        'payment_id' => uniqid('pay_', true),
                        'note' => '',
                    ]);
                }

                $createdSales[] = $sale;
            }

            if (count($createdSales) === 0) {
                DB::rollBack();
                return response()->json([
                    'error' => 'لم يتم إنشاء أي مبيعة. قد تكون السيارات مباعة بالفعل أو تخص نفس التاجر.',
                ], 400);
            }

            DB::commit();
            return response()->json([
                'message' => 'تم إضافة ' . count($createdSales) . ' مبيعة بنجاح',
                'sales_count' => count($createdSales),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // تسجيل تفصيلي للخطأ
            Log::error('addBulkInternalSale: Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'owner_id' => $owner_id ?? null,
                'request_data' => $validated ?? null,
                'user_id' => Auth::id() ?? null,
            ]);
            
            return response()->json([
                'error' => 'حدث خطأ: ' . $e->getMessage(),
                'debug' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    public function updateInternalSale(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'id' => 'required|integer|exists:internal_sales,id',
            'car_price' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'expenses' => 'nullable|numeric|min:0',
            'additional_expenses' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'sale_date' => 'nullable|date',
        ])->validate();

        $sale = InternalSale::with('car')->findOrFail($validated['id']);

        // Verify client belongs to owner
        $client = User::where('id', $sale->client_id)
            ->where('owner_id', Auth::user()->owner_id)
            ->firstOrFail();

        $sale->update(array_filter($validated, fn($key) => $key !== 'id', ARRAY_FILTER_USE_KEY));

        return response()->json([
            'message' => 'Internal sale updated successfully',
            'sale' => $sale->load('car'),
        ], 200);
    }

    public function deleteInternalSale(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'id' => 'required|integer|exists:internal_sales,id',
        ])->validate();

        $sale = InternalSale::findOrFail($validated['id']);

        // Verify client belongs to owner
        $client = User::where('id', $sale->client_id)
            ->where('owner_id', Auth::user()->owner_id)
            ->firstOrFail();

        $sale->delete();

        return response()->json([
            'message' => 'Internal sale deleted successfully',
        ], 200);
    }

    public function bulkUpdateInternalSales(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'sale_ids' => 'required|array|min:1',
            'sale_ids.*' => 'required|integer|exists:internal_sales,id',
            'car_price' => 'nullable|numeric|min:0',
            'expenses' => 'nullable|numeric|min:0',
            'additional_expenses' => 'nullable|numeric|min:0',
        ])->validate();

        $owner_id = Auth::user()->owner_id;
        $saleIds = $validated['sale_ids'];
        
        // التحقق من أن جميع المبيعات تخص نفس owner_id
        $sales = InternalSale::whereIn('id', $saleIds)
            ->whereHas('car', function($query) use ($owner_id) {
                $query->whereHas('client', function($q) use ($owner_id) {
                    $q->where('owner_id', $owner_id);
                });
            })
            ->get();

        if ($sales->count() !== count($saleIds)) {
            return response()->json([
                'error' => 'بعض المبيعات المحددة غير موجودة أو لا تنتمي لك',
            ], 403);
        }

        // إعداد بيانات التحديث (فقط الحقول التي تم إرسالها)
        $updateData = [];
        if (isset($validated['car_price'])) {
            $updateData['car_price'] = $validated['car_price'];
        }
        if (isset($validated['expenses'])) {
            $updateData['expenses'] = $validated['expenses'];
        }
        if (isset($validated['additional_expenses'])) {
            $updateData['additional_expenses'] = $validated['additional_expenses'];
        }

        if (empty($updateData)) {
            return response()->json([
                'error' => 'لم يتم تحديد أي حقول للتعديل',
            ], 400);
        }

        // تحديث جميع المبيعات
        InternalSale::whereIn('id', $saleIds)->update($updateData);

        return response()->json([
            'message' => 'تم تعديل ' . count($saleIds) . ' مبيعة بنجاح',
            'updated_count' => count($saleIds),
        ], 200);
    }

    // Get all buyers with internal sales and their debts
    public function getInternalSalesBuyers(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $merchant_id = $request->input('client_id'); // التاجر/المالك
        
        if (!$merchant_id) {
            return response()->json(['error' => 'Client ID (merchant) is required'], 400);
        }

        // التحقق من أن التاجر موجود وصحيح
        $merchant = User::where('id', $merchant_id)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        // جلب جميع المبيعات الداخلية حيث السيارة تخص هذا التاجر
        $internalSales = InternalSale::with(['car', 'client'])
            ->whereHas('car', function($query) use ($merchant_id) {
                $query->where('client_id', $merchant_id); // السيارة تخص التاجر
            })
            ->get();

        // تجميع المشترين من هذه المبيعات فقط
        $buyersMap = [];
        foreach ($internalSales as $sale) {
            $buyerId = $sale->client_id; // المشتري
            if (!isset($buyersMap[$buyerId])) {
                $buyer = $sale->client;
                if ($buyer) {
                    $buyersMap[$buyerId] = [
                        'id' => $buyer->id,
                        'name' => $buyer->name,
                        'phone' => $buyer->phone,
                        'sales' => [],
                    ];
                }
            }
            if (isset($buyersMap[$buyerId])) {
                $buyersMap[$buyerId]['sales'][] = $sale;
            }
        }

        // حساب الإجماليات لكل مشتري
        $buyers = collect($buyersMap)->map(function($buyer) {
            $sales = collect($buyer['sales']);
            $totalSales = $sales->sum('sale_price');
            $totalPaid = $sales->sum('paid_amount');
            $totalExpenses = $sales->sum('expenses');
            $totalDebt = $totalSales - $totalPaid; // الدين المتبقي
            
            return [
                'id' => $buyer['id'],
                'name' => $buyer['name'],
                'phone' => $buyer['phone'],
                'total_sales' => (float) $totalSales,
                'total_paid' => (float) $totalPaid,
                'total_expenses' => (float) $totalExpenses,
                'remaining_debt' => (float) $totalDebt, // المطلوب
                'sales_count' => $sales->count(),
            ];
        })
        ->sortByDesc('remaining_debt')
        ->values();

        return response()->json(['buyers' => $buyers], 200);
    }

    public function addPaymentToBuyer(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        
        Log::info('addPaymentToBuyer: Request received', [
            'owner_id' => $owner_id,
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
        ]);
        
        try {
            $validated = Validator::make($request->all(), [
                'buyer_id' => 'required|integer|exists:users,id',
                'merchant_id' => 'required|integer|exists:users,id',
                'amount' => 'required|numeric|min:0.01',
                'note' => 'nullable|string|max:1000',
            ])->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('addPaymentToBuyer: Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        $buyer_id = $validated['buyer_id'];
        $merchant_id = $validated['merchant_id'];
        $amount = $validated['amount'];
        $note = $validated['note'] ?? '';

        Log::debug('addPaymentToBuyer: Validated data', [
            'buyer_id' => $buyer_id,
            'merchant_id' => $merchant_id,
            'amount' => $amount,
            'owner_id' => $owner_id,
        ]);

        // التحقق من أن المشتري والتاجر يتبعان نفس owner_id
        $buyer = User::where('id', $buyer_id)
            ->where('owner_id', $owner_id)
            ->first();
            
        if (!$buyer) {
            Log::error('addPaymentToBuyer: Buyer not found or does not belong to owner', [
                'buyer_id' => $buyer_id,
                'owner_id' => $owner_id,
                'buyer_exists' => User::where('id', $buyer_id)->exists(),
                'buyer_owner_id' => User::where('id', $buyer_id)->value('owner_id'),
            ]);
            return response()->json([
                'error' => 'المشتري غير موجود أو لا ينتمي للمالك المحدد',
            ], 404);
        }

        $merchant = User::where('id', $merchant_id)
            ->where('owner_id', $owner_id)
            ->first();
            
        if (!$merchant) {
            Log::error('addPaymentToBuyer: Merchant not found or does not belong to owner', [
                'merchant_id' => $merchant_id,
                'owner_id' => $owner_id,
                'merchant_exists' => User::where('id', $merchant_id)->exists(),
                'merchant_owner_id' => User::where('id', $merchant_id)->value('owner_id'),
            ]);
            return response()->json([
                'error' => 'التاجر غير موجود أو لا ينتمي للمالك المحدد',
            ], 404);
        }

        // جلب جميع المبيعات غير المدفوعة بالكامل للزبون من هذا التاجر
        // ترتيبها حسب تاريخ الإنشاء (الأقدم أولاً) لتطبيق الدفعات بشكل تسلسلي
        $unpaidSales = InternalSale::with('car')
            ->where('client_id', $buyer_id)
            ->whereHas('car', function($query) use ($merchant_id) {
                $query->where('client_id', $merchant_id);
            })
            ->orderBy('created_at', 'asc') // ترتيب حسب تاريخ الإنشاء (الأقدم أولاً)
            ->orderBy('id', 'asc') // في حالة تساوي التواريخ، ترتيب حسب ID
            ->get()
            ->filter(function($sale) {
                // المبيعات التي لم يتم دفعها بالكامل
                return $sale->paid_amount < $sale->sale_price;
            })
            ->values();

        Log::debug('addPaymentToBuyer: Checking unpaid sales', [
            'unpaid_sales_count' => $unpaidSales->count(),
            'buyer_id' => $buyer_id,
            'merchant_id' => $merchant_id,
        ]);

        if ($unpaidSales->isEmpty()) {
            Log::warning('addPaymentToBuyer: No unpaid sales found', [
                'buyer_id' => $buyer_id,
                'merchant_id' => $merchant_id,
                'all_sales_count' => InternalSale::where('client_id', $buyer_id)
                    ->whereHas('car', function($query) use ($merchant_id) {
                        $query->where('client_id', $merchant_id);
                    })
                    ->count(),
            ]);
            return response()->json([
                'error' => 'لا توجد مبيعات غير مدفوعة لهذا الزبون',
            ], 400);
        }

        // حساب إجمالي الدين المتبقي
        $totalDebt = $unpaidSales->sum(function($sale) {
            return $sale->sale_price - $sale->paid_amount;
        });

        Log::debug('addPaymentToBuyer: Debt calculation', [
            'total_debt' => $totalDebt,
            'requested_amount' => $amount,
        ]);

        if ($amount > $totalDebt) {
            Log::warning('addPaymentToBuyer: Amount exceeds total debt', [
                'amount' => $amount,
                'total_debt' => $totalDebt,
            ]);
            return response()->json([
                'error' => 'المبلغ أكبر من الدين المتبقي (' . number_format($totalDebt, 2) . ' $)',
            ], 400);
        }

        // تطبيق الدفعة بشكل تسلسلي (سيارة تلو الأخرى)
        // بدلاً من التوزيع بالتساوي
        $remainingAmount = $amount;

        DB::beginTransaction();
        try {
            $paymentDate = Carbon::now()->format('Y-m-d');
            $createdBy = Auth::id();
            $salesUpdated = 0;
            
            // تطبيق الدفعة على السيارات بشكل تسلسلي
            foreach ($unpaidSales as $sale) {
                if ($remainingAmount <= 0) {
                    break; // تم استنفاد المبلغ بالكامل
                }
                
                $debtForThisSale = $sale->sale_price - $sale->paid_amount;
                
                if ($debtForThisSale <= 0) {
                    continue; // هذه السيارة مدفوعة بالكامل، انتقل للتي تليها
                }
                
                // تطبيق المبلغ المتبقي على هذه السيارة
                $paymentForThisSale = min($remainingAmount, $debtForThisSale);
                
                if ($paymentForThisSale > 0) {
                    // إنشاء سجل دفعة جديد في جدول buyer_payments
                    BuyerPayment::create([
                        'buyer_id' => $buyer_id,
                        'merchant_id' => $merchant_id,
                        'internal_sale_id' => $sale->id,
                        'amount' => $paymentForThisSale,
                        'payment_date' => $paymentDate,
                        'owner_id' => $owner_id,
                        'created_by' => $createdBy,
                        'payment_id' => uniqid('pay_', true), // معرف فريد لكل دفعة
                        'note' => $note, // إضافة الملاحظة
                    ]);
                    
                    // تحديث المبلغ المدفوع في المبيعة
                    $sale->paid_amount += $paymentForThisSale;
                    $sale->save();
                    $remainingAmount -= $paymentForThisSale;
                    $salesUpdated++;
                }
            }

            DB::commit();

            Log::info('addPaymentToBuyer: Payment added successfully', [
                'amount' => $amount,
                'sales_updated' => $salesUpdated,
                'buyer_id' => $buyer_id,
                'merchant_id' => $merchant_id,
            ]);

            return response()->json([
                'message' => 'تم إضافة الدفعة بنجاح',
                'amount' => $amount,
                'sales_updated' => $salesUpdated,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('addPaymentToBuyer: Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'owner_id' => $owner_id,
                'buyer_id' => $buyer_id ?? null,
                'merchant_id' => $merchant_id ?? null,
                'amount' => $amount ?? null,
            ]);
            
            return response()->json([
                'error' => 'حدث خطأ: ' . $e->getMessage(),
                'debug' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    public function getBuyerPaymentDetails(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $buyer_id = $request->input('buyer_id');
        $merchant_id = $request->input('merchant_id');
        
        if (!$buyer_id || !$merchant_id) {
            return response()->json(['error' => 'Buyer ID and Merchant ID are required'], 400);
        }

        // التحقق من أن المشتري والتاجر يتبعان نفس owner_id
        $buyer = User::where('id', $buyer_id)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $merchant = User::where('id', $merchant_id)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        // جلب جميع المبيعات للزبون من هذا التاجر
        $sales = InternalSale::with(['car'])
            ->where('client_id', $buyer_id)
            ->whereHas('car', function($query) use ($merchant_id) {
                $query->where('client_id', $merchant_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        // جلب جميع الدفعات المرتبطة بهذه المبيعات
        $saleIds = $sales->pluck('id')->toArray();
        $allPaymentsData = BuyerPayment::whereIn('internal_sale_id', $saleIds)
            ->where('buyer_id', $buyer_id)
            ->where('merchant_id', $merchant_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('internal_sale_id');
        
        // ربط الدفعات بالمبيعات
        $sales = $sales->map(function($sale) use ($allPaymentsData) {
                // جمع جميع الدفعات من جدول buyer_payments
                $payments = [];
                $salePayments = $allPaymentsData->get($sale->id);
                
                if ($salePayments && $salePayments->count() > 0) {
                    $payments = $salePayments->map(function($payment) {
                        return [
                            'id' => $payment->payment_id ?? $payment->id,
                            'amount' => (float) $payment->amount,
                            'date' => $payment->payment_date ? Carbon::parse($payment->payment_date)->format('Y-m-d') : null,
                            'created_at' => $payment->created_at ? $payment->created_at->toDateTimeString() : Carbon::now()->toDateTimeString(),
                            'note' => $payment->note,
                        ];
                    })->toArray();
                }
                
                return [
                    'id' => $sale->id,
                    'sale_price' => (float) $sale->sale_price,
                    'paid_amount' => (float) $sale->paid_amount,
                    'remaining' => (float) ($sale->sale_price - $sale->paid_amount),
                    'sale_date' => $sale->sale_date ? Carbon::parse($sale->sale_date)->format('Y-m-d') : null,
                    'created_at' => $sale->created_at,
                    'payments' => $payments, // إرجاع الدفعات
                    'car' => $sale->car ? [
                        'id' => $sale->car->id,
                        'car_type' => $sale->car->car_type,
                        'year' => $sale->car->year,
                        'vin' => $sale->car->vin,
                        'car_number' => $sale->car->car_number,
                    ] : null,
                ];
            });

        // جمع جميع الدفعات من جميع المبيعات وترتيبها حسب التاريخ
        $allPayments = collect();
        foreach ($sales as $sale) {
            if (isset($sale['payments']) && is_array($sale['payments'])) {
                foreach ($sale['payments'] as $payment) {
                    $allPayments->push([
                        'id' => $payment['id'] ?? uniqid(),
                        'sale_id' => $sale['id'],
                        'amount' => (float) ($payment['amount'] ?? 0),
                        'date' => $payment['date'] ?? Carbon::now()->format('Y-m-d'),
                        'created_at' => $payment['created_at'] ?? Carbon::now()->toDateTimeString(),
                        'note' => $payment['note'] ?? null,
                        'car' => $sale['car'],
                    ]);
                }
            }
        }
        
        // ترتيب الدفعات حسب التاريخ (الأحدث أولاً)
        $allPayments = $allPayments->sortByDesc(function($payment) {
            return $payment['created_at'] ?? $payment['date'];
        })->values();

        return response()->json([
            'sales' => $sales,
            'payments' => $allPayments, // جميع الدفعات مجمعة
        ], 200);
    }

    public function printBuyerPaymentReceipt(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $sale_id = $request->input('sale_id');
        $payment_id = $request->input('payment_id');
        $buyer_id = $request->input('buyer_id');
        $merchant_id = $request->input('merchant_id');

        if (!$sale_id || !$payment_id || !$buyer_id || !$merchant_id) {
            abort(400, 'Missing required parameters');
        }

        $payment = BuyerPayment::where('internal_sale_id', $sale_id)
            ->where('buyer_id', $buyer_id)
            ->where('merchant_id', $merchant_id)
            ->where('owner_id', $owner_id)
            ->where(function ($query) use ($payment_id) {
                $query->where('payment_id', $payment_id)
                    ->orWhere('id', $payment_id);
            })
            ->firstOrFail();

        $sale = InternalSale::with('car')
            ->where('id', $sale_id)
            ->where('client_id', $buyer_id)
            ->firstOrFail();

        if (!$sale->car || (int) $sale->car->client_id !== (int) $merchant_id) {
            abort(403);
        }

        $buyer = User::where('id', $buyer_id)->where('owner_id', $owner_id)->firstOrFail();
        $merchant = User::where('id', $merchant_id)->where('owner_id', $owner_id)->firstOrFail();
        $config = SystemConfig::first();
        $car = $sale->car;

        return view('receiptBuyerPayment', compact('payment', 'buyer', 'merchant', 'config', 'car', 'owner_id'));
    }

    public function deletePayment(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'sale_id' => 'required|integer|exists:internal_sales,id',
            'payment_id' => 'required|string',
        ])->validate();

        $owner_id = Auth::user()->owner_id;
        $sale_id = $validated['sale_id'];
        $payment_id = $validated['payment_id'];

        // جلب المبيعة
        $sale = InternalSale::with('car')
            ->whereHas('car', function($query) use ($owner_id) {
                $query->whereHas('client', function($q) use ($owner_id) {
                    $q->where('owner_id', $owner_id);
                });
            })
            ->findOrFail($sale_id);

        // البحث عن الدفعة في جدول buyer_payments
        $payment = BuyerPayment::where('internal_sale_id', $sale_id)
            ->where(function($query) use ($payment_id) {
                $query->where('payment_id', $payment_id)
                      ->orWhere('id', $payment_id);
            })
            ->where('owner_id', $owner_id)
            ->first();

        if (!$payment) {
            return response()->json([
                'error' => 'الدفعة غير موجودة',
            ], 404);
        }

        $paymentAmount = (float) $payment->amount;

        // تحديث المبيعة
        DB::beginTransaction();
        try {
            // حذف الدفعة من جدول buyer_payments
            $payment->delete();
            
            // تحديث المبلغ المدفوع في المبيعة
            $sale->paid_amount = max(0, $sale->paid_amount - $paymentAmount);
            $sale->save();

            DB::commit();

            return response()->json([
                'message' => 'تم حذف الدفعة بنجاح',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAllClients(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $merchant_id = $request->input('client_id'); // التاجر/المالك
        
        if (!$merchant_id) {
            return response()->json(['error' => 'Client ID (merchant) is required'], 400);
        }

        // جلب جميع الزبائن الذين اشتروا سيارات من هذا التاجر
        // أي InternalSale حيث car.client_id = merchant_id
        $buyerIds = InternalSale::whereHas('car', function($query) use ($merchant_id) {
                $query->where('client_id', $merchant_id); // السيارة تخص التاجر
            })
            ->distinct()
            ->pluck('client_id')
            ->toArray();

        $internalSalesClientTypeId = $this->accounting->userInternalSalesClient();

        // جلب بيانات الزبائن:
        // 1. الزبائن الذين اشتروا من هذا التاجر
        // 2. الزبائن من نوع المبيعات الداخلية الذين يمكنهم الشراء (حتى لو لم يشتروا بعد)
        $query = User::where('owner_id', $owner_id)
            ->where('id', '!=', $merchant_id); // استبعاد التاجر نفسه

        if (!empty($buyerIds)) {
            // إذا كان هناك مشترين، أضفهم
            $query->where(function($q) use ($buyerIds, $internalSalesClientTypeId) {
                $q->whereIn('id', $buyerIds); // المشترين
                // أو الزبائن من نوع المبيعات الداخلية
                if ($internalSalesClientTypeId) {
                    $q->orWhere(function($subQ) use ($internalSalesClientTypeId) {
                        $subQ->where('type_id', $internalSalesClientTypeId)
                             ->orWhere('has_internal_sales', true);
                    });
                } else {
                    $q->orWhere('has_internal_sales', true);
                }
            });
        } else {
            // إذا لم يكن هناك مشترين بعد، أظهر فقط الزبائن من نوع المبيعات الداخلية
            if ($internalSalesClientTypeId) {
                $query->where(function($q) use ($internalSalesClientTypeId) {
                    $q->where('type_id', $internalSalesClientTypeId)
                      ->orWhere('has_internal_sales', true);
                });
            } else {
                $query->where('has_internal_sales', true);
            }
        }

        $clients = $query->select('id', 'name', 'phone')
            ->orderBy('name')
            ->get();
            
        return response()->json(['clients' => $clients], 200);
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
        $user = User::with('userType:id,name')->findOrFail($User->id);
        $this->assertStaffUser($user);
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        return Inertia::render('Users/Edit', [
            'usersType' => $this->assignableStaffTypes(),
            'typeLabels' => $this->userTypeLabels(),
            'user' => $user,
        ]);
    }

    /**
     * Update staff user: profile, optional password, and role (not clients).
     */
    public function update($id, Request $request)
    {
        $auth = Auth::user();
        $isAdmin = (int) $auth->type_id === 1;

        $userQuery = User::where('id', $id);
        if (! $isAdmin) {
            $userQuery->where('owner_id', $auth->owner_id);
        }
        $user = $userQuery->firstOrFail();

        $this->assertStaffUser($user);

        $assignableIds = $this->assignableStaffTypes()->pluck('id')->all();
        $typeId = (int) ($request->input('type_id') ?? $request->input('userType') ?? $user->type_id);
        $branchIds = $this->allowedBranchIds();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:255',
            'organizer_name' => 'nullable|string|max:255',
            'type_id' => 'nullable|integer|in:' . implode(',', $assignableIds ?: [0]),
            'userType' => 'nullable|integer|in:' . implode(',', $assignableIds ?: [0]),
            'owner_id' => 'nullable|integer|in:' . implode(',', $branchIds ?: [0]),
            'password' => ['nullable', 'string', Rules\Password::defaults()],
        ];

        $validated = Validator::make($request->all(), $rules)->validate();

        if (! in_array($typeId, $assignableIds, true)) {
            abort(422, 'صلاحية غير مسموحة.');
        }

        $ownerId = (int) ($validated['owner_id'] ?? $user->owner_id);
        if (! in_array($ownerId, $branchIds, true)) {
            abort(422, 'فرع غير صالح.');
        }

        // Non-admins cannot move users to another branch
        if (! $isAdmin) {
            $ownerId = (int) $auth->owner_id;
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
            'organizer_name' => $validated['organizer_name'] ?? $user->organizer_name,
            'type_id' => $typeId,
            'owner_id' => $ownerId,
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        // Protect primary admin login identity from accidental role demotion via UI mistakes
        if ($user->email === 'admin@admin.com') {
            unset($payload['type_id'], $payload['email'], $payload['owner_id']);
        }

        $user->update($payload);
        $this->accounting->loadAccounts($auth->owner_id);

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return Response::json([
                'status' => 200,
                'message' => 'تم تحديث المستخدم بنجاح',
                'data' => $user->fresh(['userType:id,name', 'wallet:id,user_id,balance']),
            ], 200);
        }

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function destroy($id)
    {
        $user = User::where('id', $id)
            ->where('owner_id', Auth::user()->owner_id)
            ->firstOrFail();

        $this->assertStaffUser($user);

        if ($user->email === 'admin@admin.com') {
            abort(403, 'لا يمكن حذف حساب المدير الرئيسي.');
        }

        $user->delete();
        Log::info('Staff user deleted', ['user_id' => $id, 'by' => Auth::id()]);
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم');
    }

    public function ban($id)
    {
        $user = User::where('id', $id)
            ->where('owner_id', Auth::user()->owner_id)
            ->firstOrFail();

        $this->assertStaffUser($user);

        if ($user->email === 'admin@admin.com') {
            abort(403, 'لا يمكن تقييد حساب المدير الرئيسي.');
        }

        $user->update(['is_band' => 1]);
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return Response::json(['status' => 200, 'message' => 'تم تقييد المستخدم'], 200);
        }

        return redirect()->route('users.index');
    }

    public function unban($id)
    {
        $user = User::where('id', $id)
            ->where('owner_id', Auth::user()->owner_id)
            ->firstOrFail();

        $this->assertStaffUser($user);
        $user->update(['is_band' => 0]);
        $this->accounting->loadAccounts(Auth::user()->owner_id);

        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return Response::json(['status' => 200, 'message' => 'تم إلغاء التقييد'], 200);
        }

        return redirect()->route('users.index');
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