<?php

namespace App\Http\Middleware;

use App\Services\IranPageAccessService;
use Closure;
use Illuminate\Http\Request;

class EnsureIranPageAccess
{
    public function __construct(protected IranPageAccessService $access)
    {
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $mode  entry|api — entry allows page without PIN; api requires unlock
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $mode = 'api')
    {
        if (!$this->access->canEnter($request)) {
            return $this->deny($request, 401, 'يجب تسجيل الدخول أو إدخال التوكن');
        }

        if ($mode === 'entry') {
            return $next($request);
        }

        // API / print: PIN unlock (admins skip)
        if (!$this->access->isUnlocked($request)) {
            return $this->deny($request, 403, 'يجب إدخال رمز PIN لصفحة إيران');
        }

        return $next($request);
    }

    protected function deny(Request $request, int $status, string $message)
    {
        if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json(['message' => $message], $status);
        }

        if ($status === 401 && !$request->user()) {
            return redirect()->guest(route('login'));
        }

        abort($status, $message);
    }
}
