<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheOptimizationService
{
    /**
     * حفظ البيانات في الـ Cache مع Tags
     */
    public static function remember(string $key, int $ttl, callable $callback, array $tags = [])
    {
        try {
            // إذا كان Redis متاح، استخدم Tags
            if (config('cache.default') === 'redis' && !empty($tags)) {
                return Cache::tags($tags)->remember($key, $ttl, $callback);
            }
            
            // وإلا استخدم Cache عادي
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::error("فشل Cache remember: " . $e->getMessage());
            return $callback();
        }
    }

    /**
     * حفظ البيانات مع Compression
     */
    public static function compressAndStore(string $key, $data, int $ttl = 3600)
    {
        try {
            // ضغط البيانات الكبيرة
            $serialized = serialize($data);
            
            if (strlen($serialized) > 10240) { // أكبر من 10KB
                $compressed = gzcompress($serialized, 6);
                Cache::put($key . ':compressed', $compressed, $ttl);
                return true;
            }
            
            Cache::put($key, $data, $ttl);
            return true;
        } catch (\Exception $e) {
            Log::error("فشل Compress and Store: " . $e->getMessage());
            return false;
        }
    }

    /**
     * قراءة البيانات المضغوطة
     */
    public static function getCompressed(string $key)
    {
        try {
            // محاولة قراءة النسخة العادية أولاً
            $data = Cache::get($key);
            if ($data !== null) {
                return $data;
            }
            
            // محاولة قراءة النسخة المضغوطة
            $compressed = Cache::get($key . ':compressed');
            if ($compressed !== null) {
                $decompressed = gzuncompress($compressed);
                return unserialize($decompressed);
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error("فشل Get Compressed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * مسح Cache حسب Pattern
     */
    public static function forgetByPattern(string $pattern)
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);
                
                if (!empty($keys)) {
                    $redis->del($keys);
                    Log::info("تم مسح " . count($keys) . " مفتاح من الـ Cache");
                    return count($keys);
                }
            }
            
            return 0;
        } catch (\Exception $e) {
            Log::error("فشل Forget by Pattern: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * مسح Cache حسب Tags
     */
    public static function forgetByTags(array $tags)
    {
        try {
            if (config('cache.default') === 'redis') {
                Cache::tags($tags)->flush();
                Log::info("تم مسح Cache للـ Tags: " . implode(', ', $tags));
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("فشل Forget by Tags: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache Query Results
     */
    public static function cacheQuery(string $key, callable $query, int $ttl = null, array $tags = [])
    {
        $ttl = $ttl ?? config('performance.cache.default_ttl', 10) * 60;
        
        return self::remember($key, $ttl, $query, $tags);
    }

    /**
     * Warm Up Cache
     * تحميل البيانات المهمة مسبقاً
     */
    public static function warmUp(array $cacheItems)
    {
        $warmed = 0;
        
        foreach ($cacheItems as $item) {
            try {
                $key = $item['key'];
                $callback = $item['callback'];
                $ttl = $item['ttl'] ?? 3600;
                $tags = $item['tags'] ?? [];
                
                self::remember($key, $ttl, $callback, $tags);
                $warmed++;
            } catch (\Exception $e) {
                Log::error("فشل Warm Up للمفتاح {$item['key']}: " . $e->getMessage());
            }
        }
        
        Log::info("تم Warm Up لـ {$warmed} عنصر في الـ Cache");
        return $warmed;
    }

    /**
     * معلومات الـ Cache
     */
    public static function getInfo(): array
    {
        try {
            $info = [
                'driver' => config('cache.default'),
                'enabled' => true,
            ];
            
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $redisInfo = $redis->info();
                
                $info['redis'] = [
                    'version' => $redisInfo['redis_version'] ?? 'unknown',
                    'used_memory' => $redisInfo['used_memory_human'] ?? 'unknown',
                    'connected_clients' => $redisInfo['connected_clients'] ?? 'unknown',
                    'keys' => $redis->dbSize(),
                ];
            }
            
            return $info;
        } catch (\Exception $e) {
            Log::error("فشل الحصول على معلومات Cache: " . $e->getMessage());
            return ['enabled' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * تنظيف الـ Cache (إزالة المفاتيح المنتهية)
     */
    public static function cleanup(): int
    {
        try {
            if (config('cache.default') === 'file') {
                $path = storage_path('framework/cache/data');
                $files = glob($path . '/*');
                $deleted = 0;
                
                foreach ($files as $file) {
                    if (is_file($file)) {
                        // حذف الملفات المنتهية (قديمة أكثر من 24 ساعة)
                        if (time() - filemtime($file) > 86400) {
                            unlink($file);
                            $deleted++;
                        }
                    }
                }
                
                Log::info("تم حذف {$deleted} ملف Cache منتهي");
                return $deleted;
            }
            
            return 0;
        } catch (\Exception $e) {
            Log::error("فشل Cleanup: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Increment Counter في Cache
     */
    public static function increment(string $key, int $value = 1, int $ttl = 3600): int
    {
        try {
            // إذا لم يكن موجود، ابدأ من 0
            if (!Cache::has($key)) {
                Cache::put($key, 0, $ttl);
            }
            
            return Cache::increment($key, $value);
        } catch (\Exception $e) {
            Log::error("فشل Increment: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Decrement Counter في Cache
     */
    public static function decrement(string $key, int $value = 1): int
    {
        try {
            return Cache::decrement($key, $value);
        } catch (\Exception $e) {
            Log::error("فشل Decrement: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lock للعمليات الحرجة
     */
    public static function lock(string $key, int $seconds = 10): ?\Illuminate\Contracts\Cache\Lock
    {
        try {
            $lock = Cache::lock($key, $seconds);
            
            if ($lock->get()) {
                return $lock;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error("فشل Lock: " . $e->getMessage());
            return null;
        }
    }

    /**
     * اختبار أداء الـ Cache
     */
    public static function benchmark(int $iterations = 1000): array
    {
        $results = [];
        
        // Write Test
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Cache::put("benchmark_$i", ['data' => str_repeat('x', 100)], 60);
        }
        $results['write_time'] = round((microtime(true) - $start) * 1000, 2);
        
        // Read Test
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Cache::get("benchmark_$i");
        }
        $results['read_time'] = round((microtime(true) - $start) * 1000, 2);
        
        // Cleanup
        for ($i = 0; $i < $iterations; $i++) {
            Cache::forget("benchmark_$i");
        }
        
        $results['operations_per_second'] = [
            'write' => round($iterations / ($results['write_time'] / 1000)),
            'read' => round($iterations / ($results['read_time'] / 1000)),
        ];
        
        return $results;
    }
}

