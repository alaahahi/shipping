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

        return $next($request);
    }
}
