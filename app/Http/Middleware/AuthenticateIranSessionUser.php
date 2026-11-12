<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Iran API routes sit outside auth:sanctum but still need the logged-in
 * SPA session user so created_by scoping works for non-admins.
 */
class AuthenticateIranSessionUser
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()) {
            $user = Auth::guard('web')->user();
            if ($user) {
                Auth::setUser($user);
                $request->setUserResolver(static fn () => $user);
            }
        }

        return $next($request);
    }
}
