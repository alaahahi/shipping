<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class ConvertTimezoneMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Convert timestamps in JSON responses to the desired timezone
        $content = $response->getContent();
        $decodedContent = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decodedContent['data'])) {
            array_walk_recursive($decodedContent['data'], function (&$value, $key) {
                if (strtotime($value)) {
                    $value = Carbon::parse($value)->timezone('Asia/Baghdad')->toIso8601String();
                }
            });
            $response->setContent(json_encode($decodedContent));
        }

        return $response;
    }
}
