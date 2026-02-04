<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FormRegistrationController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\TransfersController;
use App\Http\Controllers\CarConfigController;
use App\Http\Controllers\OnlineContractsController;
use App\Http\Controllers\AnnualController;
use App\Http\Controllers\CarExpensesController;
use App\Http\Controllers\CarContractController;
use App\Http\Controllers\CarDamageReportController;
use App\Http\Controllers\HunterController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\LogViewerController;


use App\Models\SystemConfig;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::resource('/users', UserController::class)->middleware(['auth', 'verified']);

// Test route للنظام
Route::get('/test-system', function () {
    $results = [];
    $results['1_config'] = ['title' => '🔧 سعر الصرف'];
    try {
        $config = \App\Models\SystemConfig::first();
        $results['1_config']['data'] = $config ? [
            'status' => '✅',
            'rate' => $config->usd_to_aed_rate . ' AED'
        ] : ['status' => '❌'];
    } catch (\Exception $e) {
        $results['1_config']['data'] = ['status' => '❌', 'error' => $e->getMessage()];
    }
    
    $results['2_trip'] = ['title' => '🚢 الرحلات'];
    try {
        $trip = \App\Models\Trip::latest()->first();
        $results['2_trip']['data'] = $trip ? [
            'status' => '✅',
            'ship' => $trip->ship_name,
            'cost' => $trip->cost_per_car_aed ?? 'غير محدد',
            'commission' => $trip->captain_commission_aed ?? 'غير محدد',
            'purchase' => $trip->purchase_price_aed ?? 'غير محدد'
        ] : ['status' => '⚠️'];
    } catch (\Exception $e) {
        $results['2_trip']['data'] = ['status' => '❌', 'error' => $e->getMessage()];
    }
    
    $results['3_example'] = [
        'title' => '💰 مثال',
        'data' => ['status' => '✅', 'cost' => 360, 'commission' => 20, 'purchase' => 340, 'sale' => 400, 'profit' => 60]
    ];
    
    return response()->json($results, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'config' => SystemConfig::first(),
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Routes الترخيص (بدون middleware للسماح بالوصول قبل التفعيل)
use App\Http\Controllers\LicenseController;
Route::get('/license/activate', [LicenseController::class, 'showActivate'])->name('license.activate');
Route::get('/license/status', [LicenseController::class, 'showStatus'])->name('license.status');
Route::post('/license/activate', [LicenseController::class, 'activate'])->name('license.activate.post');

// Routes إدارة الترخيصات (الوصول عبر كلمة المرور في الرابط)
use App\Http\Controllers\AdminLicenseController;
Route::get('/admin/licenses', [AdminLicenseController::class, 'index'])->name('admin.licenses.index');

// صفحة مراقبة المزامنة - متاحة بدون تسجيل دخول
Route::get('sync-monitor', function () {
    return Inertia::render('SyncMonitor', [
        'layout' => null, // استخدام layout بسيط بدون auth
    ]);
})->name('sync.monitor');

Route::group(['middleware' => ['auth','verified', 'check.license']], function () {

    Route::get('dashboard',[DashboardController::class,'index'])->middleware(['auth', 'verified'])->name('dashboard');
    Route::get('sales',[DashboardController::class,'sales'])->name('sales');
    Route::get('purchases',[DashboardController::class,'purchases'])->name('purchases');

    Route::get('accounting',[AccountingController::class,'index'])->name('accounting');
    
    Route::get('system-settings', function () {
        return Inertia::render('Admin/SystemSettings');
    })->name('systemSettings');
    
    Route::get('connected-systems', function () {
        return Inertia::render('Admin/ConnectedSystems');
    })->name('connectedSystems');

    Route::get('log-viewer', [LogViewerController::class, 'index'])->name('logViewer');
    Route::get('log-viewer/content', [LogViewerController::class, 'getLog'])->name('logViewer.content');
    Route::post('log-viewer/clear', [LogViewerController::class, 'clearLog'])->name('logViewer.clear');
    
    Route::get('dashboard/statistics',[StatisticsController::class,'index'])->name('dashboard.statistics');
    
    // صفحة تفاصيل الجدول
    Route::get('sync-monitor/table/{tableName}', function (string $tableName, \Illuminate\Http\Request $request) {
        return Inertia::render('SyncMonitor/TableDetails', [
            'tableName' => $tableName,
            'connection' => $request->get('connection', 'auto')
        ]);
    })->name('sync.monitor.table.details');
    
    // صفحة البحث Offline
    Route::get('offline-car-search', function () {
        return Inertia::render('OfflineCarSearch');
    })->name('offline.car.search');

    
    Route::get('getIndex',[UserController::class, 'getIndex'])->name("getIndex");
    Route::get('ban/{id}',[UserController::class, 'ban'])->name("ban");
    Route::get('sentToCourt/{id}',[FormRegistrationController::class, 'sentToCourt'])->name("sentToCourt");
    Route::get('clients',[UserController::class, 'clients'])->name('clients');
    Route::get('showClients/{id}',[UserController::class, 'showClients'])->name('showClients');
    Route::get('internalSales/{id}',[UserController::class, 'internalSales'])->name('internalSales');

    
    Route::get('getIndexClients',[UserController::class, 'getIndexClients'])->name("getIndexClients");
    Route::get('getIndexClientsSearch',[UserController::class, 'getIndexClients'])->name("getIndexClientsSearch");

    

    Route::post('formRegistration',[FormRegistrationController::class, 'store'])->name('formRegistration');
    
    Route::post('formRegistrationstoreEdit/{id}',[FormRegistrationController::class, 'storeEdit'])->name('formRegistrationstoreEdit');
    
    
    Route::get('formRegistration',[FormRegistrationController::class, 'index'])->name('formRegistration');
    
    Route::get('formRegistrationEdit/{id}',[FormRegistrationController::class, 'formRegistrationEdit'])->name('formRegistrationEdit');
    
    
    Route::get('FormRegistrationSaved',[FormRegistrationController::class, 'saved'])->name('FormRegistrationSaved');
    Route::get('FormRegistrationCourt',[FormRegistrationController::class, 'court'])->name('FormRegistrationCourt');
    Route::get('FormRegistrationCompleted',[FormRegistrationController::class, 'completed'])->name('FormRegistrationCompleted');

    
    Route::get('getIndexFormRegistration',[FormRegistrationController::class, 'getIndex'])->name("getIndexFormRegistration");
    Route::get('getIndexFormRegistrationSaved',[FormRegistrationController::class, 'getIndexSaved'])->name("getIndexFormRegistrationSaved");
    Route::get('getIndexFormRegistrationCourt',[FormRegistrationController::class, 'getIndexCourt'])->name("getIndexFormRegistrationCourt");
    Route::get('getIndexFormRegistrationCompleted',[FormRegistrationController::class, 'getIndexCompleted'])->name("getIndexFormRegistrationCompleted");
    
    
    Route::get('labResults/{id}',[FormRegistrationController::class, 'labResults'])->name('labResults');
    Route::get('labResultsEdit/{id}',[FormRegistrationController::class, 'labResultsEdit'])->name('labResultsEdit');
    
    
    
    Route::get('doctorResults/{id}',[FormRegistrationController::class, 'doctorResults'])->name('doctorResults');
    Route::get('doctorResultsEdit/{id}',[FormRegistrationController::class, 'doctorResultsEdit'])->name('doctorResultsEdit');
    
    Route::post('results',[ResultsController::class, 'store'])->name('results');
    Route::post('resultsEdit/{id}',[ResultsController::class, 'storeEdit'])->name('resultsEdit');
    Route::post('resultsDoctor',[ResultsController::class, 'storeDoctor'])->name('resultsDoctor');
    Route::post('resultsDoctorEdit/{id}',[ResultsController::class, 'storeDoctorEdit'])->name('resultsDoctorEdit');
    Route::get('document/{id}', [FormRegistrationController::class, 'document'])->name('document');
    Route::get('show/{id}', [FormRegistrationController::class, 'showfile'])->name('show');
    
    
    Route::get('/livesearch', [FormRegistrationController::class, 'getProfiles'])->name('livesearch');
    Route::get('/livesearchSaved', [FormRegistrationController::class, 'getProfilesSaved'])->name('livesearchSaved');
    Route::get('/livesearchCompleted', [FormRegistrationController::class, 'getProfilesCompleted'])->name('livesearchCompleted');

    
    
    Route::get('/addUserCard/{card_id}/{card}/{user_id}', [UserController::class, 'addUserCard'])->name('addUserCard');
    
    Route::get('/receiveCard', [AccountingController::class, 'receiveCard'])->name('receiveCard');
    Route::get('/paySelse/{id}', [AccountingController::class, 'paySelse'])->name('paySelse');
    Route::get('addTransfers',[TransfersController::class, 'addTransfers'])->name('addTransfers');
    Route::get('transfers',[TransfersController::class, 'index'])->name('transfers');
    Route::get('getIndexAccountsSelas',[TransfersController::class, 'getIndexAccountsSelas'])->name('getIndexAccountsSelas');
    Route::get('carConfig',[CarConfigController::class, 'index'])->name('carConfig');
    Route::get('addCompany',[CarConfigController::class, 'create'])->name('addCompany');
    Route::post('addCompany',[CarConfigController::class, 'store']);
    Route::get('addName',[CarConfigController::class, 'storeName'])->name('addName');
    Route::get('addCarModel',[CarConfigController::class, 'storeCarModel'])->name('addCarModel');
    Route::get('addColor',[CarConfigController::class, 'storeColor'])->name('addColor');
    Route::get('addCar',[DashboardController::class, 'addCar'])->name('addCar');
    Route::get('payCar',[DashboardController::class, 'payCar'])->name('payCar');
    Route::get('getIndexCar',[DashboardController::class, 'getIndexCar'])->name('getIndexCar');
    Route::get('getIndexCarSearch',[DashboardController::class, 'getIndexCarSearch'])->name('getIndexCarSearch');

    Route::get('addExpenses',[DashboardController::class, 'addExpenses'])->name('addExpenses');
    Route::get('addPaymentCar',[DashboardController::class, 'addPaymentCar'])->name('addPaymentCar');

    
    Route::get('addToBox',[DashboardController::class, 'addToBox'])->name('addToBox');
    Route::get('withDrawFromBox',[DashboardController::class, 'withDrawFromBox'])->name('withDrawFromBox');


    
    
    Route::get('getIndexCompany',[CarConfigController::class, 'getIndex'])->name('getIndexCompany');
    Route::get('getIndexName',[CarConfigController::class, 'getIndexName'])->name('getIndexName');
    Route::get('getIndexModel',[CarConfigController::class, 'getIndexModel'])->name('getIndexModel');
    Route::get('getIndexColor',[CarConfigController::class, 'getIndexColor'])->name('getIndexColor');
    
    Route::get('companyEdit/{id}',[CarConfigController::class, 'companyEdit'])->name('companyEdit');
    Route::get('delCompany/{id}',[CarConfigController::class, 'companyDel'])->name('delCompany');
    Route::get('delName/{id}',[CarConfigController::class, 'delName'])->name('delName');
    Route::get('delModel/{id}',[CarConfigController::class, 'delModel'])->name('delModel');
    Route::get('delColor/{id}',[CarConfigController::class, 'delColor'])->name('delColor');
    Route::get('companyStoreEdit',[CarConfigController::class, 'index'])->name('companyStoreEdit');
    Route::post('companyStoreEdit',[CarConfigController::class, 'storeEdit'])->name('companyStoreEdit');


    Route::get('online_contracts',[OnlineContractsController::class, 'online_contracts'])->name('online_contracts');
    Route::get('car_expenses',[CarExpensesController::class, 'index'])->name('car_expenses');
    Route::get('car_check',[CarExpensesController::class, 'car_check'])->name('car_check');

    Route::get('car_contract',[CarContractController::class, 'index'])->name('car_contract');
    Route::get('contract_account',[CarContractController::class, 'contract_account'])->name('contract_account');
    Route::get('contract/{id?}', [CarContractController::class, 'contract'])->name('contract');
    Route::get('contract_print/{id}', [CarContractController::class, 'contract_print'])->name('contract_print');
    
    // 🖨️ طباعة العقود المحفوظة offline
    Route::get('print-offline-contract', function () {
        return view('receiptContractOffline');
    })->name('print.offline.contract');


    
    
    Route::get('dubai',[TransfersController::class, 'dubai'])->name('dubai');
    Route::get('iran',[TransfersController::class, 'iran'])->name('iran');
    Route::get('border',[TransfersController::class, 'border'])->name('border');
    Route::get('coc',[TransfersController::class, 'coc'])->name('coc');
    Route::get('howler',[TransfersController::class, 'howler'])->name('howler');
    Route::get('getIndexAccounting',[AccountingController::class, 'getIndexAccounting'])->name("getIndexAccounting");

    Route::get('annual_information',[AnnualController::class, 'index'])->name('annual_information');
    Route::get('hunter',[HunterController::class, 'index'])->name('hunter');

    Route::get('wallet',[AccountingController::class, 'wallet'])->name("wallet");

    Route::get('damage_report',[CarDamageReportController::class, 'index'])->name('damage_report.index');
    Route::get('damage_report/{id}/edit', [CarDamageReportController::class, 'edit'])->name('damage_report.edit');

    // Car History routes - commented out until CarHistoryController is created
    // Route::get('car/{carId}/history', [CarHistoryController::class, 'index'])->name('car.history');
    // Route::get('car/{carId}/history/{historyId}', [CarHistoryController::class, 'show'])->name('car.history.show');

    // Trip routes
    // يجب وضع routes المحددة قبل routes المعاملات لتجنب التضارب
    Route::get('trips',[TripController::class, 'index'])->name('trips');
    Route::get('trips/create',[TripController::class, 'create'])->name('trips.create');
    Route::post('trips',[TripController::class, 'store'])->name('trips.store');
    Route::get('trips/search-companies',[TripController::class, 'searchCompanies'])->name('trips.searchCompanies');
    Route::post('trips/create-company',[TripController::class, 'createCompany'])->name('trips.createCompany');
    Route::get('getIndexTrips',[TripController::class, 'getIndex'])->name('getIndexTrips');
    
    // Test route للتحقق من البيانات
    Route::get('trips/test-companies', function() {
        $owner_id = Auth::user()->owner_id;
        $users = \App\Models\User::where('owner_id', $owner_id)
            ->select('id', 'name', 'phone')
            ->limit(10)
            ->get();
        return response()->json([
            'owner_id' => $owner_id,
            'user_id' => Auth::id(),
            'count' => $users->count(),
            'users' => $users,
        ]);
    })->name('trips.testCompanies');
    
    // Routes مع المعاملات يجب أن تكون في النهاية
    Route::get('trips/{id}',[TripController::class, 'show'])->name('trips.show');
    Route::post('trips/{tripId}/upload-excel',[TripController::class, 'uploadExcel'])->name('trips.uploadExcel');
    Route::get('trips/{tripId}/companies',[TripController::class, 'getCompanies'])->name('trips.companies');

    // Consignee Balances Routes
    Route::get('consignee-balances',[\App\Http\Controllers\ConsigneeBalanceController::class, 'index'])->name('consigneeBalances.index');
    Route::get('consignee-balances/{consigneeId}',[\App\Http\Controllers\ConsigneeBalanceController::class, 'show'])->name('consigneeBalances.show');
    
    // Company Balances Routes
    Route::get('company-balances',[\App\Http\Controllers\CompanyBalanceController::class, 'index'])->name('companyBalances.index');
    Route::get('company-balances/{companyId}',[\App\Http\Controllers\CompanyBalanceController::class, 'show'])->name('companyBalances.show');

 });

Route::get('contract/verify/{token}', [CarContractController::class, 'verify'])->name('contract.verify');
Route::get('damage_report/verify/{token}', [CarDamageReportController::class, 'verify'])->name('damage_report.verify');
Route::get('makeDrivingDocumentPdf',[CarContractController::class, 'makeDrivingDocumentPdf'])->name('makeDrivingDocumentPdf');


require __DIR__.'/auth.php';
