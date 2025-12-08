<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UploadController;



use Illuminate\Foundation\Application;
use Inertia\Inertia;
use App\Http\Controllers\FormRegistrationController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\TransfersController;
use App\Http\Controllers\CarConfigController;
use App\Http\Controllers\OnlineContractsController;
use App\Http\Controllers\HunterController;
use App\Http\Controllers\AnnualController;
use App\Http\Controllers\CarExpensesController;
use App\Http\Controllers\CarContractController;
use App\Http\Controllers\CarDamageReportController;
use App\Http\Controllers\SyncMonitorController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\AdminLicenseController;
use App\Http\Controllers\DatabaseStatusController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Cache\FileStore;
use Illuminate\Filesystem\Filesystem;
Route::get('/sync-monitor/tables', [SyncMonitorController::class, 'tables'])->name('sync.monitor.tables');
Route::get('/sync-monitor/table/{tableName}', [SyncMonitorController::class, 'tableDetails'])->name('sync.monitor.table.details');
Route::post('/sync-monitor/sync', [SyncMonitorController::class, 'sync'])->name('sync.monitor.sync');
Route::get('/sync-monitor/sync-progress', [SyncMonitorController::class, 'syncProgress'])->name('sync.monitor.sync.progress');
Route::get('/sync-monitor/metadata', [SyncMonitorController::class, 'syncMetadata'])->name('sync.monitor.metadata');
Route::get('/sync-monitor/test/{tableName}', [SyncMonitorController::class, 'testSync'])->name('sync.monitor.test');
Route::post('/sync-monitor/table/{tableName}/truncate', [SyncMonitorController::class, 'truncateTable'])->name('sync.monitor.table.truncate');
Route::delete('/sync-monitor/table/{tableName}/delete', [SyncMonitorController::class, 'deleteTable'])->name('sync.monitor.table.delete');
Route::get('/sync-monitor/backups', [SyncMonitorController::class, 'backups'])->name('sync.monitor.backups');
Route::post('/sync-monitor/restore-backup', [SyncMonitorController::class, 'restoreBackup'])->name('sync.monitor.restore.backup');
Route::get('/sync-monitor/backup-content', [SyncMonitorController::class, 'getBackupContent'])->name('sync.monitor.backup.content');
Route::post('/sync-monitor/restore-selected', [SyncMonitorController::class, 'restoreSelectedTables'])->name('sync.monitor.restore.selected');
Route::get('/sync-monitor/download-backup', [SyncMonitorController::class, 'downloadBackup'])->name('sync.monitor.download.backup');
Route::delete('/sync-monitor/backup/delete', [SyncMonitorController::class, 'deleteBackup'])->name('sync.monitor.backup.delete');

Route::get('/debug-owner-cache/{ownerId}', function ($ownerId) {
    $keys = [
        'main_account', 'in_account', 'out_account', 'debt_account',
        'transfers_account', 'out_supplier', 'debt_supplier',
        'howler', 'shipping_coc', 'border', 'iran', 'dubai', 'main_box',
        'online_contracts', 'online_contracts_dinar',
        'debt_online_contracts', 'debt_online_contracts_dinar'
    ];

    $results = [];

    foreach ($keys as $key) {
        $fullKey = "account_{$ownerId}_{$key}";
        $results[$fullKey] = Cache::get($fullKey, 'غير موجود');
    }

    return response()->json($results);
});


use App\Models\SystemConfig;
Route::get('/clear-config-cache', function () {

    
    //return ;

    Artisan::call('config:cache');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    //Artisan::call('command:cache_most_visited');
    //$content_controller = new ContentEntityRepository();
    //$content_controller->log_visit_cache_job([]);
    return "Configuration cache file removed";
});
Route::get('refreshCache',[DashboardController::class, 'refreshCache'])->name('refreshCache');
Route::get('loadAccounts',[DashboardController::class, 'loadAccounts'])->name('loadAccounts');

// Test route to clear route cache
Route::get('clear-route-cache', function () {
    \Artisan::call('route:clear');
    \Artisan::call('config:clear');
    return response()->json(['message' => 'Route cache cleared successfully']);
});

Route::middleware('auth:sanctum')->group(function () {
Route::apiResource('upload', UploadController::class);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {return $request->user();});
Route::get('/user/{id}', function (Request $request) { return  User::find($request->id)->massage;});
Route::get('/user/{id}',[UserController::class, 'getMassages']);
Route::get('/getUserMassages/{id}/{user}',[UserController::class, 'getUserMassages']);
Route::middleware('auth:api')->get('/user', function (Request $request) { return $request->user();});
Route::post('login',[UserController::class, 'login']);
Route::get('getcontact/{id}',[UserController::class, 'getcontact']);


Route::get('receiveCard',[UserController::class, 'receiveCard']);


Route::get('ackUserMassages/{sender}/{receiver}/{date}',[UserController::class, 'ackUserMassages']);

Route::get('getIndexClients',[UserController::class, 'getIndexClients'])->name("getIndexClients");
Route::get('getIndexClientsSearch',[UserController::class, 'getIndexClients'])->name("getIndexClientsSearch");


Route::get('getSaler',[UserController::class, 'getSaler']);

Route::post('clientsStore',[UserController::class, 'clientsStore'])->name('clientsStore');
Route::post('clientsEdit',[UserController::class, 'clientsEdit'])->name('clientsEdit');
Route::post('delClient',[UserController::class, 'delClient'])->name('delClient');

Route::post('updateClientPhone',[UserController::class, 'updateClientPhone'])->name('updateClientPhone');

// Internal Sales Routes
Route::post('toggleInternalSales',[UserController::class, 'toggleInternalSales'])->name('toggleInternalSales');
Route::get('getInternalSales',[UserController::class, 'getInternalSales'])->name('getInternalSales');
Route::get('getUnsoldCars',[UserController::class, 'getUnsoldCars'])->name('getUnsoldCars');
Route::get('getAllClients',[UserController::class, 'getAllClients'])->name('getAllClients');
Route::post('addInternalSale',[UserController::class, 'addInternalSale'])->name('addInternalSale');
Route::post('addBulkInternalSale',[UserController::class, 'addBulkInternalSale'])->name('addBulkInternalSale');
Route::post('updateInternalSale',[UserController::class, 'updateInternalSale'])->name('updateInternalSale');
Route::post('bulkUpdateInternalSales',[UserController::class, 'bulkUpdateInternalSales'])->name('bulkUpdateInternalSales');
Route::post('addPaymentToBuyer',[UserController::class, 'addPaymentToBuyer'])->name('addPaymentToBuyer');
Route::get('getBuyerPaymentDetails',[UserController::class, 'getBuyerPaymentDetails'])->name('getBuyerPaymentDetails');
Route::post('deletePayment',[UserController::class, 'deletePayment'])->name('deletePayment');
Route::post('deleteInternalSale',[UserController::class, 'deleteInternalSale'])->name('deleteInternalSale');
Route::get('getInternalSalesBuyers',[UserController::class, 'getInternalSalesBuyers'])->name('getInternalSalesBuyers');

// Car Sales Routes
Route::post('sellCar',[UserController::class, 'sellCar'])->name('sellCar');
Route::get('getCarSales',[UserController::class, 'getCarSales'])->name('getCarSales');
Route::get('getBuyers',[UserController::class, 'getBuyers'])->name('getBuyers');
Route::post('addSalePayment',[UserController::class, 'addSalePayment'])->name('addSalePayment');
Route::get('getSalePayments',[UserController::class, 'getSalePayments'])->name('getSalePayments');
Route::get('getBuyerSales',[UserController::class, 'getBuyerSales'])->name('getBuyerSales');


Route::post('addTransfers',[TransfersController::class, 'addTransfers'])->name('addTransfers');
Route::get('transfers',[TransfersController::class, 'index'])->name('transfers');
Route::post('confirmTransfers',[TransfersController::class, 'confirmTransfers'])->name('confirmTransfers');
Route::post('cancelTransfers',[TransfersController::class, 'cancelTransfers'])->name('cancelTransfers');


Route::get('getIndexAccountsSelas',[AccountingController::class, 'getIndexAccountsSelas'])->name('getIndexAccountsSelas');

Route::post('deleteTransactions',[AccountingController::class, 'delTransactions'])->name('deleteTransactions');
Route::post('updateTransactionDescription',[AccountingController::class, 'updateTransactionDescription'])->name('updateTransactionDescription');


Route::get('carConfig',[CarConfigController::class, 'index'])->name('carConfig');
Route::get('addCompany',[CarConfigController::class, 'create'])->name('addCompany');
Route::post('addCompany',[CarConfigController::class, 'store']);
Route::get('addName',[CarConfigController::class, 'storeName'])->name('addName');
Route::get('addCarModel',[CarConfigController::class, 'storeCarModel'])->name('addCarModel');
Route::get('addColor',[CarConfigController::class, 'storeColor'])->name('addColor');
Route::post('addCar',[DashboardController::class, 'addCar'])->name('addCar');
Route::post('addCars',[DashboardController::class, 'addCars'])->name('addCars');

Route::post('addCarsHunter',[HunterController::class, 'addCarsHunter'])->name('addCarsHunter');

Route::post('addCarsAnnual',[AnnualController::class, 'addCarsAnnual'])->name('addCarsAnnual');

Route::post('updateCarsS',[DashboardController::class, 'updateCarsS'])->name('updateCarsS');
Route::post('updateCarsP',[DashboardController::class, 'updateCarsP'])->name('updateCarsP');
Route::post('bulkUpdateCarsP',[DashboardController::class, 'bulkUpdateCarsP'])->name('bulkUpdateCarsP');
Route::post('bulkUpdateCarsS',[DashboardController::class, 'bulkUpdateCarsS'])->name('bulkUpdateCarsS');

Route::get('/getcount', [DashboardController::class, 'getcount'])->name('getcount');

Route::post('DelPayFromBalanceCar',[AccountingController::class, 'DelPayFromBalanceCar'])->name('DelPayFromBalanceCar');
Route::post('AddPayFromBalanceCar',[AccountingController::class, 'AddPayFromBalanceCar'])->name('AddPayFromBalanceCar');

Route::post('payCar',[DashboardController::class, 'payCar'])->name('payCar');
Route::post('DelCar',[DashboardController::class, 'DelCar'])->name('DelCar');

Route::get('client',[DashboardController::class, 'client'])->name('client');
Route::get('getIndexCar',[DashboardController::class, 'getIndexCar'])->name('getIndexCar');
Route::get('getIndexCarSearch',[DashboardController::class, 'getIndexCarSearch'])->name('getIndexCarSearch');
Route::get('totalInfo',[DashboardController::class, 'totalInfo'])->name('totalInfo');


Route::get('getIndexCarAnnual',[AnnualController::class, 'getIndexCarAnnual'])->name('getIndexCarAnnual');

Route::post('carsAnnualUpload',[AnnualController::class, 'carsAnnualUpload'])->name('carsAnnualUpload');
Route::get('carsAnnualImageDel',[AnnualController::class, 'carsAnnualImageDel'])->name('carsAnnualImageDel');
Route::post('updateCarsAnnual',[AnnualController::class, 'updateCarsAnnual'])->name('updateCarsAnnual');
Route::post('delCarsAnnualr',[AnnualController::class, 'delCarsAnnualr'])->name('delCarsAnnualr');

Route::get('getIndexCarHunter',[HunterController::class, 'getIndexCarHunter'])->name('getIndexCarHunter');

Route::post('carsHunterUpload',[HunterController::class, 'carsHunterUpload'])->name('carsHunterUpload');
Route::get('carsHunterImageDel',[HunterController::class, 'carsHunterImageDel'])->name('carsHunterImageDel');
Route::post('updateCarsHunter',[HunterController::class, 'updateCarsHunter'])->name('updateCarsHunter');
Route::post('delCarsHunterr',[HunterController::class, 'delCarsHunterr'])->name('delCarsHunterr');
Route::get('checkCarInCars',[HunterController::class, 'checkCarInCars'])->name('checkCarInCars');
Route::post('updateCarExpenses',[HunterController::class, 'updateCarExpenses'])->name('updateCarExpenses'); 
});

// External APIs (without auth, but with API key)
Route::middleware('api.key')->prefix('external')->group(function () {
    Route::get('checkCar', [HunterController::class, 'externalCheckCar'])->name('external.checkCar');
    Route::get('getSales', [HunterController::class, 'externalGetSales'])->name('external.getSales');
    Route::get('getPayments', [HunterController::class, 'externalGetPayments'])->name('external.getPayments');
});

// Database Status - متاح بدون auth للاختبار
Route::get('/database/status', [DatabaseStatusController::class, 'status'])->name('api.database.status');

// Sync Monitor APIs - استخدام auth بدلاً من auth:sanctum لأن الصفحة تستخدم session
Route::middleware('auth')->group(function () {
        
      
});

Route::post('TransactionsUpload',[AccountingController::class, 'TransactionsUpload'])->name('TransactionsUpload');
Route::get('TransactionsImageDel',[AccountingController::class, 'TransactionsImageDel'])->name('TransactionsImageDel');

Route::get('getIndexExpenses',[DashboardController::class, 'getIndexExpenses'])->name('getIndexExpenses');
Route::get('showCar',[CarConfigController::class, 'showCar']);

Route::get('addExpenses',[DashboardController::class, 'addExpenses'])->name('addExpenses');
Route::get('addPaymentCar',[AccountingController::class, 'addPaymentCar'])->name('addPaymentCar');
Route::get('addCarContracts',[OnlineContractsController::class, 'addCarContracts'])->name('addCarContracts');
Route::get('editCarContracts',[OnlineContractsController::class, 'editCarContracts'])->name('editCarContracts');
Route::get('makeCarExit',[OnlineContractsController::class, 'makeCarExit'])->name('makeCarExit');
Route::get('unMakeCarExit',[OnlineContractsController::class, 'unMakeCarExit'])->name('unMakeCarExit');
Route::post('removeContract',[OnlineContractsController::class, 'removeContract'])->name('removeContract');
Route::get('onlineContractsTotalInfo',[OnlineContractsController::class, 'totalInfo'])->name('onlineContractsTotalInfo');
Route::get('getCarsOverYear',[OnlineContractsController::class, 'getCarsOverYear'])->name('getCarsOverYear');
Route::get('getCarsNextMonth',[OnlineContractsController::class, 'getCarsNextMonth'])->name('getCarsNextMonth');


Route::get('addPaymentCarTotal',[AccountingController::class, 'addPaymentCarTotal'])->name('addPaymentCarTotal');


Route::get('addToBox',[DashboardController::class, 'addToBox'])->name('addToBox');
Route::get('withDrawFromBox',[DashboardController::class, 'withDrawFromBox'])->name('withDrawFromBox');

Route::get('check_vin',[CarConfigController::class, 'check_vin']);



Route::get('getIndexCompany',[CarConfigController::class, 'getIndex'])->name('getIndexCompany');
Route::get('getIndexName',[CarConfigController::class, 'getIndexName'])->name('getIndexName');
Route::get('getIndexModel',[CarConfigController::class, 'getIndexModel'])->name('getIndexModel');
Route::get('getIndexColor',[CarConfigController::class, 'getIndexColor'])->name('getIndexColor');


Route::get('companyEdit/{id}',[CarConfigController::class, 'companyEdit'])->name('companyEdit');
Route::get('delCompany/{id}',[CarConfigController::class, 'companyDel'])->name('delCompany');
Route::get('delName/{id}',[CarConfigController::class, 'delName'])->name('delName');
Route::get('delModel/{id}',[CarConfigController::class, 'delModel'])->name('delModel');
Route::get('delColor/{id}',[CarConfigController::class, 'delColor'])->name('delColor');
Route::get('companyStoreEdit',[CarConfigController::class, 'index'])->name('api.companyStoreEdit');
Route::post('companyStoreEdit',[CarConfigController::class, 'storeEdit'])->name('api.companyStoreEdit.post');


Route::post('salesDebt',[AccountingController::class, 'salesDebt'])->name('salesDebt');
Route::post('delTransactions',[AccountingController::class, 'delTransactions'])->name('delTransactions');
Route::post('receiptArrived',[AccountingController::class, 'receiptArrived'])->name('receiptArrived');
Route::post('receiptArrivedUser',[AccountingController::class, 'receiptArrivedUser'])->name('receiptArrivedUser');
Route::post('salesDebtUser',[AccountingController::class, 'salesDebtUser'])->name('salesDebtUser');
Route::post('receiptArrivedUserAmanah',[AccountingController::class, 'receiptArrivedUserAmanah'])->name('receiptArrivedUserAmanah');
Route::post('salesDebtUserAmanah',[AccountingController::class, 'salesDebtUserAmanah'])->name('salesDebtUserAmanah');


Route::post('GenExpenses',[AccountingController::class, 'GenExpenses'])->name('GenExpenses');
Route::get('getGenExpenses',[AccountingController::class, 'getGenExpenses'])->name('getGenExpenses');

Route::post('convertDollarDinar',[AccountingController::class, 'convertDollarDinar'])->name('convertDollarDinar');
Route::post('convertDinarDollar',[AccountingController::class, 'convertDinarDollar'])->name('convertDinarDollar');


Route::post('addCarFavorite',[CarExpensesController::class, 'addCarFavorite'])->name('addCarFavorite');
Route::post('confirmExpensesCar',[CarExpensesController::class, 'confirmExpensesCar'])->name('confirmExpensesCar');
Route::post('delExpensesCar',[CarExpensesController::class, 'delExpensesCar'])->name('delExpensesCar');
Route::post('confirmArchiveCar',[CarExpensesController::class, 'confirmArchiveCar'])->name('confirmArchiveCar');
Route::post('confirmArchiveCarBack',[CarExpensesController::class, 'confirmArchiveCarBack'])->name('confirmArchiveCarBack');
Route::post('confirmDelCarFav',[CarExpensesController::class, 'confirmDelCarFav'])->name('confirmDelCarFav');
Route::get('getIndexExpensesPrint',[CarExpensesController::class, 'getIndexExpensesPrint'])->name('getIndexExpensesPrint');

Route::post('addCarContract',[CarContractController::class, 'addCarContract'])->name('addCarContract');
Route::get('getIndexContractCar',[CarContractController::class, 'getIndexContractCar'])->name('getIndexContractCar');
Route::post('DelCarContract',[CarContractController::class, 'DelCarContract'])->name('DelCarContract');
Route::get('totalInfoContract',[CarContractController::class, 'totalInfoContract'])->name('totalInfoContract');
Route::get('getListTransactionsContract',[CarContractController::class, 'getListTransactionsContract'])->name('getListTransactionsContract');
Route::post('addToBoxContract',[CarContractController::class, 'addToBoxContract'])->name('addToBoxContract');
Route::post('delTransactionsContract',[CarContractController::class, 'delTransactionsContract'])->name('delTransactionsContract');
Route::post('convertDollarDinarContract',[CarContractController::class, 'convertDollarDinarContract'])->name('convertDollarDinarContract');
Route::post('convertDinarDollarContract',[CarContractController::class, 'convertDinarDollarContract'])->name('convertDinarDollarContract');
Route::post('DropFromBoxContract',[CarContractController::class, 'DropFromBoxContract'])->name('DropFromBoxContract');
Route::get('getIndexClientsContract',[CarContractController::class, 'getIndexClientsContract'])->name('getIndexClientsContract');
Route::get('contract_account_report',[CarContractController::class, 'contract_account_report'])->name('contract_account_report');

Route::post('makeDrivingDocument',[CarContractController::class, 'makeDrivingDocument'])->name('makeDrivingDocument');
Route::get('makeDrivingDocumentPdf',[CarContractController::class, 'makeDrivingDocumentPdf'])->name('makeDrivingDocumentPdf');

Route::middleware('auth')->group(function () {
    Route::post('storeDamageReport',[CarDamageReportController::class, 'store'])->name('storeDamageReport');
    Route::post('updateDamageReport',[CarDamageReportController::class, 'update'])->name('updateDamageReport');
    Route::get('getIndexDamageReport',[CarDamageReportController::class, 'getIndex'])->name('getIndexDamageReport');
    Route::get('makeDamageReportPdf',[CarDamageReportController::class, 'makeDamageReportPdf'])->name('makeDamageReportPdf');
    Route::post('deleteDamageReport',[CarDamageReportController::class, 'delete'])->name('deleteDamageReport');

    // Car History APIs
    Route::get('car/{carId}/history', [CarHistoryController::class, 'getHistory'])->name('car.history.api');
    Route::get('car/{carId}/history/{historyId}', [CarHistoryController::class, 'show'])->name('car.history.show.api');
    Route::post('car/{carId}/history/compare', [CarHistoryController::class, 'compare'])->name('car.history.compare');
    Route::get('car-history/statistics', [CarHistoryController::class, 'statistics'])->name('car.history.statistics');
    Route::post('car-history/migrate-transactions', [CarHistoryController::class, 'migrateTransactions'])->name('car.history.migrate');
    Route::post('car-history/cleanup', [CarHistoryController::class, 'cleanup'])->name('car.history.cleanup');

    // System APIs
    Route::post('system/clear-cache', [CarHistoryController::class, 'clearCache'])->name('system.clear.cache');
    Route::post('system/optimize-database', [CarHistoryController::class, 'optimizeDatabase'])->name('system.optimize.database');
    Route::post('system/generate-backup', [CarHistoryController::class, 'generateBackup'])->name('system.generate.backup');
    Route::get('system/health-check', [CarHistoryController::class, 'healthCheck'])->name('system.health.check');
    Route::get('system/database-info', [CarHistoryController::class, 'getDatabaseInfo'])->name('system.database.info');
});

Route::get('checkClientBalance',[AccountingController::class, 'checkClientBalance'])->name('checkClientBalance');

Route::post('search-vins', [CarExpensesController::class, 'searchVINs'])->name('search-vins');

// License APIs - بدون middleware للسماح بالتفعيل قبل تسجيل الدخول
Route::prefix('license')->group(function () {
    Route::get('/status', [LicenseController::class, 'status'])->name('api.license.status');
    Route::post('/activate', [LicenseController::class, 'activate'])->name('api.license.activate');
    Route::post('/verify', [LicenseController::class, 'verify'])->name('api.license.verify');
    Route::get('/server-info', [LicenseController::class, 'getServerInfo'])->name('api.license.server-info');
    
    // Routes محمية (تحتاج auth)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/deactivate', [LicenseController::class, 'deactivate'])->name('api.license.deactivate');
    });
});

// Admin License Management APIs - للأدمن فقط
Route::prefix('admin/licenses')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [AdminLicenseController::class, 'list'])->name('api.admin.licenses.list');
    Route::get('/statistics', [AdminLicenseController::class, 'statistics'])->name('api.admin.licenses.statistics');
    Route::post('/', [AdminLicenseController::class, 'create'])->name('api.admin.licenses.create');
    Route::get('/{id}', [AdminLicenseController::class, 'show'])->name('api.admin.licenses.show');
    Route::put('/{id}', [AdminLicenseController::class, 'update'])->name('api.admin.licenses.update');
    Route::post('/{id}/toggle', [AdminLicenseController::class, 'toggle'])->name('api.admin.licenses.toggle');
    Route::delete('/{id}', [AdminLicenseController::class, 'destroy'])->name('api.admin.licenses.destroy');
});
