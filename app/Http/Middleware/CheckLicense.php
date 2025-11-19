<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Route;

class CheckLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // إذا كان نظام الترخيص معطلاً، السماح بالمرور
        if (!config('license.enabled')) {
            return $next($request);
        }

        // التحقق من Routes المستثناة
        $routeName = Route::currentRouteName();
        $excludedRoutes = config('license.excluded_routes', []);

        if ($routeName && in_array($routeName, $excludedRoutes)) {
            return $next($request);
        }

        // التحقق من Controllers المستثناة
        $controller = class_basename($request->route()->getController());
        $excludedControllers = config('license.excluded_controllers', []);

        if (in_array($controller, $excludedControllers)) {
            return $next($request);
        }

        // التحقق من الترخيص
        if (!LicenseService::isActivated()) {
            // إذا كان الطلب API، إرجاع JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'الترخيص غير مفعل أو منتهي الصلاحية',
                    'error' => 'License not activated or expired'
                ], 403);
            }

            // توجيه لصفحة التفعيل
            return redirect()->route('license.activate')
                ->with('error', 'يجب تفعيل الترخيص أولاً');
        }

        // التحقق من صلاحية الترخيص (إذا كان مفعلاً)
        if (!LicenseService::verify()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'الترخيص منتهي الصلاحية',
                    'error' => 'License expired'
                ], 403);
            }

            return redirect()->route('license.activate')
                ->with('error', 'الترخيص منتهي الصلاحية');
        }

        return $next($request);
    }
}
