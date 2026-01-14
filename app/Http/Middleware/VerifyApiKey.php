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
        // التحقق من API-Key header أو X-API-Key أو api_key parameter
        // Laravel headers are case-insensitive, but we check multiple variations
        $apiKey = $request->header('API-Key') 
                ?? $request->header('api-key')
                ?? $request->header('X-API-Key') 
                ?? $request->header('x-api-key')
                ?? $request->get('api_key');
        $expectedKey = env('API_KEY');

        if (!$expectedKey) {
            return response()->json(['error' => 'API key not configured'], 500);
        }

        if (!$apiKey || trim($apiKey) !== trim($expectedKey)) {
            return response()->json([
                'error' => 'Invalid or missing API key',
                'received' => $apiKey ? 'present' : 'missing',
                'expected_length' => strlen($expectedKey)
            ], 401);
        }

        return $next($request);
    }
}

