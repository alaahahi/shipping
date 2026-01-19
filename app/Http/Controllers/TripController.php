<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use App\Models\Trip;
use App\Models\TripCompany;
use App\Models\TripCar;
use App\Models\TripExpense;
use App\Models\User;
use App\Models\Car;
use App\Models\Wallet;
use App\Services\AccountingCacheService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TripCarImport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TripController extends Controller
{
    protected $accounting;
    protected $url;

    public function __construct(AccountingCacheService $accounting)
    {
        $this->accounting = $accounting;
        $this->url = env('FRONTEND_URL');
    }

    /**
     * عرض قائمة الرحلات
     */
    public function index(Request $request)
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        return Inertia::render('Trips/Index');
    }

    /**
     * عرض نموذج إنشاء رحلة
     */
    public function create()
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        return Inertia::render('Trips/Create');
    }

    /**
     * حفظ رحلة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sailing_date' => 'required|date',
            'captain' => 'nullable|string|max:255',
            'pol' => 'required|string|max:255',
            'pod' => 'required|string|max:255',
            'flag' => 'nullable|string|max:255',
            'ship_name' => 'required|string|max:255',
            'voy_no' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        try {
            $trip = Trip::create([
                'sailing_date' => $validated['sailing_date'],
                'captain' => $validated['captain'] ?? null,
                'pol' => $validated['pol'],
                'pod' => $validated['pod'],
                'flag' => $validated['flag'] ?? null,
                'ship_name' => $validated['ship_name'],
                'voy_no' => $validated['voy_no'] ?? null,
                'note' => $validated['note'] ?? null,
                'total_expenses' => 0,
                'expenses_currency' => 'dollar',
                'owner_id' => Auth::user()->owner_id,
            ]);

            return redirect()->route('trips.show', $trip->id)
                ->with('success', 'تم إنشاء الرحلة بنجاح');
        } catch (\Exception $e) {
            Log::error('Error creating trip: ' . $e->getMessage());
            return back()->withErrors(['error' => 'حدث خطأ أثناء إنشاء الرحلة: ' . $e->getMessage()]);
        }
    }

    /**
     * عرض تفاصيل الرحلة
     */
    public function show($id)
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        $owner_id = Auth::user()->owner_id;

        try {
            $trip = Trip::where('id', $id)
                ->where('owner_id', $owner_id)
                ->with(['companies.company', 'cars.consignee', 'expenses'])
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::error('Trip not found', [
                'trip_id' => $id,
                'owner_id' => $owner_id,
                'user_id' => Auth::id(),
            ]);
            
            return redirect()->route('trips')
                ->with('error', 'الرحلة غير موجودة أو ليس لديك صلاحية للوصول إليها');
        }

        // حساب الإحصائيات
        $stats = [
            'total_cars' => $trip->total_cars,
            'total_companies' => $trip->total_companies,
            'total_consignees' => $trip->total_consignees,
            'total_weight' => $trip->total_weight,
            'total_expenses_dollar' => $trip->total_expenses_dollar,
            'total_expenses_dinar' => $trip->total_expenses_dinar,
        ];

        // تجميع السيارات حسب CONSIGNEE مع معلومات الشركة
        $carsByConsignee = TripCar::where('trip_id', $trip->id)
            ->whereNotNull('consignee_id')
            ->with(['consignee', 'tripCompany.company'])
            ->get()
            ->groupBy('consignee_id')
            ->map(function ($cars, $consigneeId) {
                $consignee = $cars->first()->consignee;
                $wallet = $consignee->wallet ?? null;
                
                return [
                    'consignee' => $consignee,
                    'cars_count' => $cars->count(),
                    'cars' => $cars->map(function ($car) {
                        return [
                            'id' => $car->id,
                            'weight' => $car->weight,
                            'description' => $car->description,
                            'chassis_no' => $car->chassis_no,
                            'code' => $car->code,
                            'company_name' => $car->tripCompany->company->name ?? 'غير محدد',
                            'trip_company_id' => $car->trip_company_id,
                        ];
                    }),
                    'balance' => $wallet ? $wallet->balance : 0,
                ];
            })
            ->values();

        // جلب سعر الصرف من إعدادات النظام (سعر 100 دولار بالدينار)
        $systemConfig = \App\Models\SystemConfig::first();
        $exchangeRate = $systemConfig ? $systemConfig->usd_to_dinar_rate : 150.00;

        return Inertia::render('Trips/Show', [
            'trip' => $trip,
            'stats' => $stats,
            'carsByConsignee' => $carsByConsignee,
            'exchangeRate' => $exchangeRate,
        ]);
    }

    /**
     * جلب قائمة الرحلات (API)
     */
    public function getIndex(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 15);
        $search = $request->input('search', '');

        $query = Trip::where('owner_id', $owner_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ship_name', 'LIKE', "%{$search}%")
                    ->orWhere('voy_no', 'LIKE', "%{$search}%")
                    ->orWhere('pol', 'LIKE', "%{$search}%")
                    ->orWhere('pod', 'LIKE', "%{$search}%");
            });
        }

        $trips = $query->orderBy('sailing_date', 'DESC')
            ->paginate($limit);

        // إضافة الإحصائيات لكل رحلة
        $trips->getCollection()->transform(function ($trip) {
            $trip->total_cars = $trip->total_cars;
            $trip->total_companies = $trip->total_companies;
            $trip->total_expenses = $trip->total_expenses;
            return $trip;
        });

        return Response::json($trips);
    }

    /**
     * رفع ملف Excel لشركة معينة
     */
    public function uploadExcel(Request $request, $tripId)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:users,id',
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        $owner_id = Auth::user()->owner_id;
        $trip = Trip::where('id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // حفظ الملف
            $file = $request->file('file');
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = 'trip_' . $tripId . '_company_' . $validated['company_id'] . '_' . time() . '.' . $fileExtension;
            $filePath = $file->storeAs('trips/excel', $fileName, 'public');

            // البحث عن أو إنشاء TripCompany
            $tripCompany = TripCompany::firstOrCreate(
                [
                    'trip_id' => $trip->id,
                    'company_id' => $validated['company_id'],
                ],
                [
                    'owner_id' => $owner_id,
                ]
            );

            $tripCompany->update([
                'excel_file_path' => $filePath,
                'uploaded_at' => now(),
            ]);

            // استيراد البيانات مباشرة من الملف (نفس منطق المعاينة)
            $fileRealPath = $file->getRealPath();
            $importer = new TripCarImport($trip->id, $tripCompany->id, $owner_id, $fileRealPath);
            
            // استيراد مباشر - يقرأ من Excel مباشرة بدون Laravel Excel package
            $result = $importer->importDirectly();

            DB::commit();

            // بناء رسالة النجاح مع عدد السيارات المتخطاة
            $message = 'تم استيراد الملف بنجاح';
            $skippedDuplicates = $importer->skippedDuplicates ?? 0;
            
            if ($skippedDuplicates > 0) {
                $message .= ". تم تخطي {$skippedDuplicates} سيارة موجودة مسبقاً في هذه الرحلة";
            }

            return Response::json([
                'success' => true,
                'message' => $message,
                'skipped_duplicates' => $skippedDuplicates,
                'trip_company' => $tripCompany->load('company'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing trip Excel: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء استيراد الملف: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * البحث عن الشركات باستخدام LLC
     */
    public function searchCompanies(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $search = $request->input('q', '');

        Log::info('searchCompanies called', [
            'owner_id' => $owner_id,
            'search' => $search,
            'user_id' => Auth::id(),
        ]);

        // البحث عن الشركات فقط (type_id = shipping_company)
        // ملاحظة: هذا الـ endpoint يستخدم للبحث عن شركات الشحن
        $shippingCompanyTypeId = $this->accounting->userShippingCompany();
        
        $query = User::where('owner_id', $owner_id)
            ->where('type_id', $shippingCompanyTypeId); // الشركات فقط

        // إذا كان البحث فارغاً، نعيد جميع العملاء (محدود بـ 50)
        // إذا كان هناك بحث، نبحث في الاسم
        if (!empty($search)) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $companies = $query
            ->orderBy('name', 'asc')
            ->limit(empty($search) ? 50 : 20) // عند البحث الفارغ، نعيد 50، عند البحث نعيد 20
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'phone' => $company->phone ?? '',
                ];
            });

        Log::info('searchCompanies result', [
            'type_id' => $shippingCompanyTypeId,
            'count' => $companies->count(),
            'companies' => $companies->toArray(),
        ]);

        return Response::json($companies);
    }

    /**
     * إنشاء شركة جديدة
     */
    public function createCompany(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
        ]);

        $owner_id = Auth::user()->owner_id;
        $year_date = Carbon::now()->format('Y');

        try {
            // التحقق من عدم وجود شركة بنفس الاسم
            $existingCompany = User::where('owner_id', $owner_id)
                ->where('name', $validated['name'])
                ->first();

            if ($existingCompany) {
                return Response::json([
                    'success' => false,
                    'message' => 'يوجد بالفعل شركة بهذا الاسم',
                    'company' => [
                        'id' => $existingCompany->id,
                        'name' => $existingCompany->name,
                        'phone' => $existingCompany->phone,
                    ],
                ], 400);
            }

            // إنشاء شركة جديدة (نستخدم shipping_company type_id)
            $shippingCompanyTypeId = $this->accounting->userShippingCompany();
            
            $company = User::create([
                'name' => $validated['name'],
                'type_id' => $shippingCompanyTypeId, // استخدام نوع الشركات الجديد
                'phone' => $validated['phone'] ?? null,
                'owner_id' => $owner_id,
                'year_date' => $year_date,
                'created' => Carbon::now()->format('Y-m-d'),
            ]);

            // إنشاء Wallet للشركة
            Wallet::firstOrCreate(
                ['user_id' => $company->id],
                ['balance' => 0]
            );

            return Response::json([
                'success' => true,
                'message' => 'تم إنشاء الشركة بنجاح',
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'phone' => $company->phone,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating company: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الشركة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب الشركات المرتبطة بالرحلة
     */
    public function getCompanies($tripId)
    {
        $owner_id = Auth::user()->owner_id;
        $trip = Trip::where('id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $companies = $trip->companies()
            ->with('company')
            ->withCount('cars')
            ->get();

        return Response::json($companies);
    }

    /**
     * إضافة مصاريف للرحلة
     */
    public function addExpense(Request $request, $tripId)
    {
        $validated = $request->validate([
            'expense_type' => 'required|in:shipping,fuel,port,customs,other',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:dinar,dollar',
            'note' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $owner_id = Auth::user()->owner_id;
        $trip = Trip::where('id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            $expense = TripExpense::create([
                'trip_id' => $trip->id,
                'expense_type' => $validated['expense_type'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'note' => $validated['note'] ?? null,
                'date' => $validated['date'] ?? now(),
                'owner_id' => $owner_id,
            ]);

            // تحديث مجموع المصاريف (يتم تلقائياً في Model Event)
            $trip->refresh();

            return Response::json([
                'success' => true,
                'expense' => $expense,
                'trip' => $trip,
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding trip expense: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة المصاريف: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تحديث مصاريف
     */
    public function updateExpense(Request $request, $expenseId)
    {
        $validated = $request->validate([
            'expense_type' => 'sometimes|in:shipping,fuel,port,customs,other',
            'amount' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|in:dinar,dollar',
            'note' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $owner_id = Auth::user()->owner_id;
        $expense = TripExpense::where('id', $expenseId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            $expense->update($validated);
            $expense->trip->refresh();

            return Response::json([
                'success' => true,
                'expense' => $expense,
                'trip' => $expense->trip,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating trip expense: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث المصاريف: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * حذف مصاريف
     */
    public function deleteExpense($expenseId)
    {
        $owner_id = Auth::user()->owner_id;
        $expense = TripExpense::where('id', $expenseId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            $trip = $expense->trip;
            $expense->delete();
            $trip->refresh();

            return Response::json([
                'success' => true,
                'trip' => $trip,
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting trip expense: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المصاريف: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب جميع مصاريف الرحلة
     */
    public function getExpenses($tripId)
    {
        $owner_id = Auth::user()->owner_id;
        $trip = Trip::where('id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $expenses = $trip->expenses()
            ->orderBy('date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get();

        return Response::json($expenses);
    }

    /**
     * استيراد ملف Excel لشركة موجودة (إضافة سيارات جديدة)
     */
    public function importExcelForCompany(Request $request, $tripId)
    {
        $validated = $request->validate([
            'trip_company_id' => 'required|exists:trip_companies,id',
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        $owner_id = Auth::user()->owner_id;
        $trip = Trip::where('id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $tripCompany = TripCompany::where('id', $validated['trip_company_id'])
            ->where('trip_id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // حفظ الملف
            $file = $request->file('file');
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = 'trip_' . $tripId . '_company_' . $tripCompany->company_id . '_' . time() . '.' . $fileExtension;
            $filePath = $file->storeAs('trips/excel', $fileName, 'public');

            // تحديث مسار الملف في TripCompany
            $tripCompany->update([
                'excel_file_path' => $filePath,
                'uploaded_at' => now(),
            ]);

            // استيراد البيانات مباشرة من الملف (نفس منطق المعاينة)
            $fileRealPath = $file->getRealPath();
            $importer = new TripCarImport($trip->id, $tripCompany->id, $owner_id, $fileRealPath);
            
            // استيراد مباشر - يقرأ من Excel مباشرة بدون Laravel Excel package
            $result = $importer->importDirectly();

            DB::commit();

            // بناء رسالة النجاح مع عدد السيارات المتخطاة
            $message = 'تم استيراد الملف بنجاح';
            $skippedDuplicates = $importer->skippedDuplicates ?? 0;
            
            if ($skippedDuplicates > 0) {
                $message .= ". تم تخطي {$skippedDuplicates} سيارة موجودة مسبقاً في هذه الرحلة";
            }

            return Response::json([
                'success' => true,
                'message' => $message,
                'skipped_duplicates' => $skippedDuplicates,
                'trip_company' => $tripCompany->load('company'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing trip Excel for company: ' . $e->getMessage(), [
                'trip_id' => $tripId,
                'trip_company_id' => $validated['trip_company_id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء استيراد الملف: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تصدير Excel للرحلة
     */
    public function exportExcel(Request $request, $tripId)
    {
        $validated = $request->validate([
            'trip_company_id' => 'required|exists:trip_companies,id',
        ]);

        $owner_id = Auth::user()->owner_id;
        $trip = Trip::where('id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $tripCompany = TripCompany::where('id', $validated['trip_company_id'])
            ->where('trip_id', $trip->id)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            $fileName = 'trip_' . $tripId . '_company_' . $tripCompany->company->name . '_' . date('Y-m-d') . '.xlsx';
            $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileName); // تنظيف اسم الملف
            
            return Excel::download(
                new \App\Exports\TripCarsExport($tripCompany->id),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Error exporting trip cars: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تصدير الملف: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تصدير PDF للرحلة
     */
    public function exportPdf($tripId)
    {
        // سيتم إضافة PDF export لاحقاً
        return Response::json(['message' => 'قريباً']);
    }

    /**
     * ملخص السيارات حسب CONSIGNEE
     */
    public function getCarsSummary($tripId)
    {
        $owner_id = Auth::user()->owner_id;
        $trip = Trip::where('id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $summary = TripCar::where('trip_id', $trip->id)
            ->whereNotNull('consignee_id')
            ->with('consignee')
            ->get()
            ->groupBy('consignee_id')
            ->map(function ($cars, $consigneeId) {
                $consignee = $cars->first()->consignee;
                return [
                    'consignee_id' => $consigneeId,
                    'consignee_name' => $consignee->name,
                    'cars_count' => $cars->count(),
                    'total_weight' => $cars->sum('weight'),
                ];
            })
            ->values();

        return Response::json($summary);
    }

    /**
     * معاينة محتوى Excel قبل الاستيراد
     */
    public function previewExcel(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $filePath = $file->getRealPath();
            $fileExtension = $file->getClientOriginalExtension();
            
            // قراءة الملف مع تحديد النوع بشكل صريح
            $readerType = strtoupper($fileExtension) === 'XLS' ? 'Xls' : 'Xlsx';
            $reader = IOFactory::createReader($readerType);
            
            // تعطيل التحقق من صحة XML لتجنب أخطاء empty document
            if (method_exists($reader, 'setReadDataOnly')) {
                $reader->setReadDataOnly(true);
            }
            
            // قمع أخطاء XML
            libxml_use_internal_errors(true);
            
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // مسح أخطاء XML
            libxml_clear_errors();
            
            // البحث عن صف S.NO
            $snoRow = $this->findSnoRowInFile($filePath, $fileExtension);
            
            // قراءة أول 20 صف بعد S.NO
            $previewRows = [];
            $maxRows = min(20, $worksheet->getHighestRow());
            $startRow = $snoRow + 1; // نبدأ من الصف بعد S.NO
            
            // قراءة صف الأعمدة
            $headers = [];
            $maxCol = min(10, $worksheet->getHighestColumn());
            for ($col = 'A'; $col <= $maxCol; $col++) {
                $headerValue = $worksheet->getCell($col . $snoRow)->getValue();
                $headers[] = $headerValue !== null ? trim((string) $headerValue) : '';
            }
            
            // قراءة البيانات
            for ($row = $startRow; $row <= min($startRow + 19, $worksheet->getHighestRow()); $row++) {
                $rowData = [];
                $isEmpty = true;
                
                for ($col = 'A'; $col <= $maxCol; $col++) {
                    $cellValue = $worksheet->getCell($col . $row)->getValue();
                    $rowData[] = $cellValue !== null ? trim((string) $cellValue) : '';
                    if (!empty($cellValue)) {
                        $isEmpty = false;
                    }
                }
                
                if (!$isEmpty) {
                    $previewRows[] = $rowData;
                }
            }
            
            return Response::json([
                'success' => true,
                'sno_row' => $snoRow,
                'headers' => $headers,
                'preview' => $previewRows,
                'total_preview_rows' => count($previewRows),
            ]);
        } catch (\Exception $e) {
            Log::error('Error previewing Excel: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معاينة الملف: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * البحث عن صف S.NO في الملف
     */
    protected function findSnoRowInFile($filePath, $fileExtension = 'xlsx')
    {
        try {
            // تحديد نوع القارئ بشكل صريح
            $readerType = strtoupper($fileExtension) === 'XLS' ? 'Xls' : 'Xlsx';
            $reader = IOFactory::createReader($readerType);
            
            // تعطيل التحقق من صحة XML
            if (method_exists($reader, 'setReadDataOnly')) {
                $reader->setReadDataOnly(true);
            }
            
            // قمع أخطاء XML
            libxml_use_internal_errors(true);
            
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // مسح أخطاء XML
            libxml_clear_errors();
            
            // البحث في أول 30 صف
            $maxRows = min(30, $worksheet->getHighestRow());
            
            for ($row = 1; $row <= $maxRows; $row++) {
                $maxCol = min(10, $worksheet->getHighestColumn());
                
                for ($col = 'A'; $col <= $maxCol; $col++) {
                    $cellValue = $worksheet->getCell($col . $row)->getValue();
                    
                    if ($cellValue !== null) {
                        $cellValueStr = strtoupper(trim((string) $cellValue));
                        
                        // البحث عن S.NO أو S NO أو S/NO أو S.NO. أو S.NO:
                        if (preg_match('/^S[.\s\/]*NO[.\s:]*$/i', $cellValueStr) || 
                            $cellValueStr === 'S.NO' || 
                            $cellValueStr === 'S NO' ||
                            $cellValueStr === 'S/NO' ||
                            $cellValueStr === 'S.NO.' ||
                            $cellValueStr === 'S.NO:') {
                            return $row;
                        }
                    }
                }
            }
            
            return 10; // القيمة الافتراضية
        } catch (\Exception $e) {
            Log::error('Error finding S.NO row: ' . $e->getMessage());
            return 10;
        }
    }

    /**
     * جلب سيارات شركة معينة
     */
    /**
     * تحديث سعر الشحن لكل سيارة
     */
    public function updateShippingPrice(Request $request, $tripId, $tripCompanyId)
    {
        $validated = $request->validate([
            'shipping_price_per_car' => 'required|numeric|min:0',
            'shipping_price_aed' => 'nullable|numeric|min:0',
        ]);

        $owner_id = Auth::user()->owner_id;
        $trip = Trip::where('id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $tripCompany = TripCompany::where('id', $tripCompanyId)
            ->where('trip_id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            $tripCompany->update([
                'shipping_price_per_car' => $validated['shipping_price_per_car'],
                'shipping_price_aed' => $validated['shipping_price_aed'] ?? null,
            ]);

            return Response::json([
                'success' => true,
                'message' => 'تم تحديث سعر الشحن بنجاح',
                'trip_company' => $tripCompany->load('company'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating shipping price: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث سعر الشحن: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب سيارات شركة معينة
     */
    public function getCompanyCars($tripId, $tripCompanyId)
    {
        $owner_id = Auth::user()->owner_id;
        $trip = Trip::where('id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $tripCompany = TripCompany::where('id', $tripCompanyId)
            ->where('trip_id', $trip->id)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $cars = TripCar::where('trip_id', $trip->id)
            ->where('trip_company_id', $tripCompany->id)
            ->with(['car', 'consignee'])
            ->orderBy('id', 'desc')
            ->get();

        return Response::json($cars);
    }

    /**
     * إضافة سيارة جديدة لشركة مرتبطة بالرحلة
     */
    public function addCar(Request $request, $tripId)
    {
        $validated = $request->validate([
            'trip_company_id' => 'required|exists:trip_companies,id',
            'weight' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'chassis_no' => 'nullable|string|max:255',
            'consignee_name' => 'required|string|max:255',
            'consignee_id' => 'nullable|exists:users,id',
            'code' => 'nullable|string|max:255',
        ]);

        $owner_id = Auth::user()->owner_id;
        $trip = Trip::where('id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $tripCompany = TripCompany::where('id', $validated['trip_company_id'])
            ->where('trip_id', $trip->id)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $accounting = app(AccountingCacheService::class);
            $userClientTypeId = $accounting->userClient();

            // البحث عن أو إنشاء Car
            $car = null;
            if (!empty($validated['chassis_no'])) {
                $car = Car::where('owner_id', $owner_id)
                    ->where('vin', $validated['chassis_no'])
                    ->first();

                if (!$car) {
                    $maxNo = Car::max('no') ?? 0;
                    $no = $maxNo + 1;

                    $car = Car::create([
                        'vin' => $validated['chassis_no'],
                        'car_type' => $validated['description'] ?? '',
                        'no' => $no,
                        'owner_id' => $owner_id,
                        'year_date' => Carbon::now()->format('Y'),
                        'date' => Carbon::now()->format('Y-m-d'),
                    ]);
                }
            }

            // البحث عن أو إنشاء CONSIGNEE
            $consignee = null;
            if (!empty($validated['consignee_id'])) {
                $consignee = User::where('id', $validated['consignee_id'])
                    ->where('owner_id', $owner_id)
                    ->first();
            }

            if (!$consignee && !empty($validated['consignee_name'])) {
                $consignee = User::where('owner_id', $owner_id)
                    ->where('name', 'LIKE', "%{$validated['consignee_name']}%")
                    ->where('type_id', $userClientTypeId)
                    ->first();

                if (!$consignee) {
                    $consignee = User::create([
                        'name' => $validated['consignee_name'],
                        'type_id' => $userClientTypeId,
                        'owner_id' => $owner_id,
                        'created' => Carbon::now()->format('Y-m-d'),
                        'year_date' => Carbon::now()->format('Y'),
                    ]);

                    Wallet::firstOrCreate(
                        ['user_id' => $consignee->id],
                        ['balance' => 0]
                    );
                }
            }

            // إنشاء TripCar
            $tripCar = TripCar::create([
                'trip_id' => $trip->id,
                'trip_company_id' => $tripCompany->id,
                'car_id' => $car ? $car->id : null,
                'weight' => $validated['weight'] ?? null,
                'shipper_name' => $tripCompany->company->name ?? '',
                'description' => $validated['description'] ?? null,
                'chassis_no' => $validated['chassis_no'] ?? null,
                'consignee_name' => $validated['consignee_name'],
                'consignee_id' => $consignee ? $consignee->id : null,
                'code' => $validated['code'] ?? null,
                'owner_id' => $owner_id,
            ]);

            DB::commit();

            return Response::json([
                'success' => true,
                'message' => 'تم إضافة السيارة بنجاح',
                'car' => $tripCar->load(['car', 'consignee', 'tripCompany']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding trip car: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة السيارة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تحديث سيارة
     */
    public function updateCar(Request $request, $carId)
    {
        $validated = $request->validate([
            'weight' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'chassis_no' => 'nullable|string|max:255',
            'consignee_name' => 'sometimes|required|string|max:255',
            'consignee_id' => 'nullable|exists:users,id',
            'code' => 'nullable|string|max:255',
        ]);

        $owner_id = Auth::user()->owner_id;
        $tripCar = TripCar::where('id', $carId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $accounting = app(AccountingCacheService::class);
            $userClientTypeId = $accounting->userClient();

            // تحديث Car إذا تم تغيير chassis_no
            if (isset($validated['chassis_no']) && $validated['chassis_no'] !== $tripCar->chassis_no) {
                $car = null;
                if (!empty($validated['chassis_no'])) {
                    $car = Car::where('owner_id', $owner_id)
                        ->where('vin', $validated['chassis_no'])
                        ->first();

                    if (!$car) {
                        $maxNo = Car::max('no') ?? 0;
                        $no = $maxNo + 1;

                        $car = Car::create([
                            'vin' => $validated['chassis_no'],
                            'car_type' => $validated['description'] ?? $tripCar->description ?? '',
                            'no' => $no,
                            'owner_id' => $owner_id,
                            'year_date' => Carbon::now()->format('Y'),
                            'date' => Carbon::now()->format('Y-m-d'),
                        ]);
                    }
                }
                $validated['car_id'] = $car ? $car->id : null;
            }

            // تحديث CONSIGNEE إذا تم تغيير الاسم
            if (isset($validated['consignee_name']) && $validated['consignee_name'] !== $tripCar->consignee_name) {
                $consignee = null;
                if (!empty($validated['consignee_id'])) {
                    $consignee = User::where('id', $validated['consignee_id'])
                        ->where('owner_id', $owner_id)
                        ->first();
                }

                if (!$consignee && !empty($validated['consignee_name'])) {
                    $consignee = User::where('owner_id', $owner_id)
                        ->where('name', 'LIKE', "%{$validated['consignee_name']}%")
                        ->where('type_id', $userClientTypeId)
                        ->first();

                    if (!$consignee) {
                        $consignee = User::create([
                            'name' => $validated['consignee_name'],
                            'type_id' => $userClientTypeId,
                            'owner_id' => $owner_id,
                            'created' => Carbon::now()->format('Y-m-d'),
                            'year_date' => Carbon::now()->format('Y'),
                        ]);

                        Wallet::firstOrCreate(
                            ['user_id' => $consignee->id],
                            ['balance' => 0]
                        );
                    }
                }
                $validated['consignee_id'] = $consignee ? $consignee->id : null;
            }

            $tripCar->update($validated);
            $tripCar->refresh();

            DB::commit();

            return Response::json([
                'success' => true,
                'message' => 'تم تحديث السيارة بنجاح',
                'car' => $tripCar->load(['car', 'consignee', 'tripCompany']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating trip car: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث السيارة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * حذف سيارة
     */
    public function deleteCar($carId)
    {
        $owner_id = Auth::user()->owner_id;
        $tripCar = TripCar::where('id', $carId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            $tripCar->delete();

            return Response::json([
                'success' => true,
                'message' => 'تم حذف السيارة بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting trip car: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف السيارة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تحديث إعدادات الكلفة للرحلة
     */
    public function updateCostConfiguration(Request $request, $tripId)
    {
        $validated = $request->validate([
            'cost_per_car_aed' => 'required|numeric|min:0',
            'captain_commission_aed' => 'required|numeric|min:0',
            'purchase_price_aed' => 'required|numeric|min:0',
        ]);

        $owner_id = Auth::user()->owner_id;
        $trip = Trip::where('id', $tripId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        try {
            $trip->update([
                'cost_per_car_aed' => $validated['cost_per_car_aed'],
                'captain_commission_aed' => $validated['captain_commission_aed'],
                'purchase_price_aed' => $validated['purchase_price_aed'],
            ]);

            return Response::json([
                'success' => true,
                'message' => 'تم تحديث إعدادات الكلفة بنجاح',
                'trip' => $trip,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating cost configuration: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث إعدادات الكلفة: ' . $e->getMessage(),
            ], 500);
        }
    }
}
