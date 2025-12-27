<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyApiKey
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
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        $expectedKey = env('API_KEY');

        if (!$expectedKey) {
            return response()->json(['error' => 'API key not configured'], 500);
        }

        if ($apiKey !== $expectedKey) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        return $next($request);
    }
}

