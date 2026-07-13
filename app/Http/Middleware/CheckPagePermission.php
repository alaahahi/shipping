<?php

namespace App\Http\Middleware;

use App\Models\AppPage;
use App\Services\PagePermissionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CheckPagePermission
{
    public function __construct(protected PagePermissionService $permissions)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Schema::hasTable('app_pages')) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (!$routeName || in_array($routeName, ['logout'], true)) {
            return $next($request);
        }

        if (!$this->permissions->canAccessRoute($user, $routeName)) {
            if ($request->header('X-Inertia')) {
                return redirect()
                    ->route('dashboard')
                    ->with('error', 'غير مسموح الوصول لهذه الصفحة');
            }

            abort(403, 'غير مسموح الوصول لهذه الصفحة');
        }

        return $next($request);
    }
}
