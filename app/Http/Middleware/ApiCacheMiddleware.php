<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiCacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $ttl Time to live in minutes (default: 10)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $ttl = 10)
    {
        // فقط للطلبات GET
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // تجاهل إذا كان المستخدم يطلب بيانات جديدة
        if ($request->header('X-Force-Refresh')) {
            Cache::forget($this->getCacheKey($request));
            return $next($request);
        }

        // مفتاح الـ Cache
        $cacheKey = $this->getCacheKey($request);

        try {
            // محاولة الحصول على البيانات من الـ Cache
            $cachedResponse = Cache::remember($cacheKey, $ttl * 60, function () use ($request, $next) {
                $response = $next($request);
                
                // احفظ فقط الاستجابات الناجحة
                if ($response->getStatusCode() === 200) {
                    return [
                        'content' => $response->getContent(),
                        'headers' => $response->headers->all(),
                        'status' => $response->getStatusCode()
                    ];
                }
                
                return null;
            });

            // إذا كانت البيانات من الـ Cache
            if ($cachedResponse && is_array($cachedResponse)) {
                $response = response($cachedResponse['content'], $cachedResponse['status']);
                
                // إضافة header للدلالة على أن البيانات من الـ Cache
                $response->header('X-Cache-Hit', 'true');
                $response->header('X-Cache-Key', $cacheKey);
                
                Log::debug("استجابة من الـ Cache: {$cacheKey}");
                
                return $response;
            }

            $response = $next($request);
            $response->header('X-Cache-Hit', 'false');
            
            return $response;
        } catch (\Exception $e) {
            Log::error("خطأ في ApiCacheMiddleware: " . $e->getMessage());
            return $next($request);
        }
    }

    /**
     * إنشاء مفتاح Cache فريد للطلب
     */
    protected function getCacheKey(Request $request): string
    {
        $url = $request->url();
        $queryParams = $request->query();
        $user = $request->user();
        
        // إضافة معرف المستخدم للخصوصية
        $userId = $user ? $user->id : 'guest';
        
        ksort($queryParams);
        $queryString = http_build_query($queryParams);
        
        return 'api_cache:' . md5($url . $queryString . $userId);
    }
}

