<?php

use Illuminate\Support\Facades\Route;
use IntellijApp\License\Http\Controllers\LicenseController;
use IntellijApp\License\Http\Controllers\AdminLicenseController;

// License APIs - بدون middleware للسماح بالتفعيل قبل تسجيل الدخول
Route::prefix('license')->group(function () {
    Route::get('/status', [LicenseController::class, 'status'])->name('api.license.status');
    Route::post('/activate', [LicenseController::class, 'activate'])->name('api.license.activate');
    Route::post('/verify', [LicenseController::class, 'verify'])->name('api.license.verify');
    Route::get('/server-info', [LicenseController::class, 'getServerInfo'])->name('api.license.server-info');
    
    // Routes المزامنة
    Route::post('/sync', [LicenseController::class, 'sync'])->name('api.license.sync');
    Route::post('/pull', [LicenseController::class, 'pull'])->name('api.license.pull');
    Route::post('/push', [LicenseController::class, 'push'])->name('api.license.push');
    
    // Routes محمية (تحتاج auth)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/deactivate', [LicenseController::class, 'deactivate'])->name('api.license.deactivate');
    });
});

// Admin License Management APIs - الوصول عبر كلمة المرور في الرابط
Route::prefix('admin/licenses')->group(function () {
    Route::get('/', [AdminLicenseController::class, 'list'])->name('api.admin.licenses.list');
    Route::get('/statistics', [AdminLicenseController::class, 'statistics'])->name('api.admin.licenses.statistics');
    Route::post('/', [AdminLicenseController::class, 'create'])->name('api.admin.licenses.create');
    Route::get('/{id}', [AdminLicenseController::class, 'show'])->name('api.admin.licenses.show');
    Route::put('/{id}', [AdminLicenseController::class, 'update'])->name('api.admin.licenses.update');
    Route::post('/{id}/toggle', [AdminLicenseController::class, 'toggle'])->name('api.admin.licenses.toggle');
    Route::delete('/{id}', [AdminLicenseController::class, 'destroy'])->name('api.admin.licenses.destroy');
});

