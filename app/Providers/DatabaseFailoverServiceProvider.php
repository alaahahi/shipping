<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class DatabaseFailoverServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! config('database.failover.enabled')) {
            return;
        }

        $primary = config('database.failover.primary', config('database.default'));
        $fallback = config('database.failover.fallback', config('sync.local_connection', 'sync_sqlite'));

        if (! config("database.connections.{$primary}")) {
            Log::warning('Database failover: primary connection missing from config', ['connection' => $primary]);
            return;
        }

        if (! config("database.connections.{$fallback}")) {
            Log::warning('Database failover: fallback connection missing from config', ['connection' => $fallback]);
            return;
        }

        $shouldUsePrimary = $this->shouldUsePrimary();
        $target = $shouldUsePrimary ? $primary : $fallback;

        $this->switchConnection($target);

        // Touch the connection once to ensure it is ready; if it fails we fallback immediately.
        try {
            DB::connection($target)->getPdo();
        } catch (\Throwable $throwable) {
            Log::warning('Database failover: unable to connect to target connection', [
                'connection' => $target,
                'error' => $throwable->getMessage(),
            ]);

            if ($target !== $fallback) {
                $this->switchConnection($fallback);
            }
        }
    }

    protected function shouldUsePrimary(): bool
    {
        $cacheKey = config('database.failover.cache_key', 'database:failover:online');
        $cacheTtl = config('database.failover.cache_ttl', 60);

        return Cache::remember($cacheKey, $cacheTtl, function () {
            return $this->pingPrimary();
        });
    }

    protected function pingPrimary(): bool
    {
        $url = config('database.failover.check_url');

        if (! $url) {
            return false;
        }

        try {
            $response = Http::timeout(config('database.failover.timeout', 2))
                ->withOptions(['verify' => false])
                ->head($url);

            return $response->successful();
        } catch (\Throwable $throwable) {
            Log::debug('Database failover: ping failed', [
                'url' => $url,
                'error' => $throwable->getMessage(),
            ]);

            return false;
        }
    }

    protected function switchConnection(string $connection): void
    {
        if (config('database.default') === $connection) {
            return;
        }

        Config::set('database.default', $connection);
        DB::setDefaultConnection($connection);
        DB::purge($connection);
    }
}

