<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ConnectionService;

class CheckConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // السماح لصفحات API و AJAX بدون إعادة توجيه
        if ($request->expectsJson() || $request->is('api/*')) {
            return $next($request);
        }

        // السماح لصفحات معينة بدون إعادة توجيه
        $excludedPaths = [
            '/license/activate',
            '/license/status',
            '/login',
            '/register',
        ];

        $currentPath = $request->getPathInfo();
        
        foreach ($excludedPaths as $path) {
            if (strpos($currentPath, $path) === 0) {
                return $next($request);
            }
        }

        // التحقق من الاتصال وتحديد URL المناسب
        $connectionInfo = ConnectionService::getConnectionInfo();
        
        // إضافة معلومات الاتصال للـ JavaScript دائماً
        view()->share('connection', $connectionInfo);

        return $next($request);
    }
}

