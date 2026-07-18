<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;
use App\Services\ConnectionService;
use App\Services\PagePermissionService;
use App\Models\SystemConfig;
use Illuminate\Support\Facades\Schema;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed[]
     */
    public function share(Request $request)
    {
        $user = $request->user();
        $permissions = app(PagePermissionService::class);
        $hasPagesTable = Schema::hasTable('app_pages');

        return array_merge(parent::share($request), [
            'auth' => fn () => [
                'user' => $user,
                'allowedRoutes' => ($user && $hasPagesTable) ? $permissions->getAllowedRouteNamesForUser($user) : [],
                'navPages' => ($user && $hasPagesTable) ? $permissions->getNavPagesForUser($user) : [],
                'canManagePermissions' => $user ? $permissions->isPermissionManager($user) : false,
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'flash' => [
                'message' => session('message'),
            ],
            'connection' => ConnectionService::getConnectionInfo(),
            'company_name' => config('app.company_name'),
            'systemLogo' => function () {
                try {
                    if (! Schema::hasTable('system_config')) {
                        return '/img/logo.png';
                    }

                    SystemConfig::ensureMediaColumns();

                    return SystemConfig::resolveLogoUrl();
                } catch (\Throwable $e) {
                    return '/img/logo.png';
                }
            },
            'loginBackground' => function () {
                try {
                    if (! Schema::hasTable('system_config')) {
                        return null;
                    }

                    SystemConfig::ensureMediaColumns();

                    return SystemConfig::resolveLoginBackgroundUrl();
                } catch (\Throwable $e) {
                    return null;
                }
            },
        ]);
    }
}
