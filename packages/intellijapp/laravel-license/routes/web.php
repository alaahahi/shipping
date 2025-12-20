<?php

use Illuminate\Support\Facades\Route;
use IntellijApp\License\Http\Controllers\LicenseController;
use IntellijApp\License\Http\Controllers\AdminLicenseController;

$prefix = config('license.route_prefix', 'license');
$adminPrefix = config('license.admin_route_prefix', 'admin/licenses');

// Routes الترخيص (بدون middleware للسماح بالوصول قبل التفعيل)
Route::prefix($prefix)->group(function () {
    Route::get('/activate', [LicenseController::class, 'showActivate'])->name('license.activate');
    Route::get('/status', [LicenseController::class, 'showStatus'])->name('license.status');
    Route::post('/activate', [LicenseController::class, 'activate'])->name('license.activate.post');
});

// Routes إدارة الترخيصات (الوصول عبر كلمة المرور في الرابط)
Route::prefix($adminPrefix)->group(function () {
    Route::get('/', [AdminLicenseController::class, 'index'])->name('admin.licenses.index');
});

