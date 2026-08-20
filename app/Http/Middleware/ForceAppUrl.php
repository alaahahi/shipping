<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ForceAppUrl
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getSchemeAndHttpHost();

        if ($request->isSecure()) {
            URL::forceScheme('https');
        }

        URL::forceRootUrl($host);
        config(['app.url' => $host]);

        // Keep Sanctum stateful CSRF in sync with the live host (fixes 419 on api/* from browser)
        $hostOnly = $request->getHost();
        $stateful = config('sanctum.stateful', []);
        if (! in_array($hostOnly, $stateful, true)) {
            $stateful[] = $hostOnly;
            config(['sanctum.stateful' => $stateful]);
        }

        return $next($request);
    }
}
