<?php

namespace App\Http\Controllers;

use App\Models\SyncJob;
use App\Services\Sync\SyncService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SyncMonitorController extends Controller
{
    public function index(): View
    {
        return view('sync.monitor', [
            'serverUrl' => config('sync.server_url'),
            'pushEndpoint' => config('sync.push_endpoint'),
            'pullEndpoint' => config('sync.pull_endpoint'),
            'models' => array_keys(config('sync.models', [])),
        ]);
    }

    public function status(): JsonResponse
    {
        $connectionName = config('sync.local_connection');
        $pending = SyncJob::whereNull('synced_at')->count();
        $syncedToday = SyncJob::whereNotNull('synced_at')
            ->whereDate('synced_at', today())
            ->count();
        $failed = SyncJob::whereNull('synced_at')
            ->where('attempts', '>=', 3)
            ->count();
        $queue = SyncJob::count();
        $lastSync = Cache::get(config('sync.last_sync_cache_key'));

        $recentJobs = SyncJob::orderByDesc('id')
            ->limit(15)
            ->get(['model', 'operation', 'uuid', 'attempts', 'created_at', 'synced_at']);

        $diagnostics = $this->buildDiagnosticsPayload($connectionName);

        return response()->json([
            'metrics' => [
                'pending' => $pending,
                'synced_today' => $syncedToday,
                'failed' => $failed,
                'queue_size' => $queue,
                'last_sync_time' => $lastSync,
            ],
            'diagnostics' => $diagnostics,
            'models' => $this->buildModelDiagnostics(),
            'jobs' => $recentJobs,
        ]);
    }

    public function run(SyncService $syncService): JsonResponse
    {
        $syncService->pushLocalChangesToServer();
        $syncService->pullRemoteChanges();

        return response()->json([
            'status' => 'ok',
            'message' => 'تم تنفيذ المزامنة اليدوية',
        ]);
    }

    public function flush(Request $request): JsonResponse
    {
        $mode = $request->input('mode', 'pending');

        if ($mode === 'all') {
            SyncJob::truncate();
        } else {
            SyncJob::whereNull('synced_at')->delete();
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'تمت تهيئة قائمة المزامنة',
        ]);
    }

    protected function buildDiagnosticsPayload(string $connectionName): array
    {
        $driver = config("database.connections.{$connectionName}.driver");
        $databasePath = null;

        try {
            $databasePath = DB::connection($connectionName)->getDatabaseName();
        } catch (\Throwable $throwable) {
            Log::warning('SyncMonitor: unable to resolve database path', [
                'connection' => $connectionName,
                'error' => $throwable->getMessage(),
            ]);
        }

        $diagnostics = [
            'connection' => $connectionName,
            'driver' => $driver,
            'database' => $databasePath,
            'database_exists' => null,
            'database_size' => null,
            'sync_jobs_table' => null,
            'sqlite_version' => null,
            'writable' => null,
        ];

        try {
            $diagnostics['sync_jobs_table'] = Schema::connection($connectionName)->hasTable('sync_jobs');
        } catch (\Throwable $throwable) {
            $diagnostics['sync_jobs_table'] = false;
            Log::warning('SyncMonitor: unable to inspect sync_jobs table', [
                'connection' => $connectionName,
                'error' => $throwable->getMessage(),
            ]);
        }

        if ($driver === 'sqlite' && $databasePath) {
            $diagnostics['database_exists'] = file_exists($databasePath);

            if ($diagnostics['database_exists']) {
                $diagnostics['database_size'] = @filesize($databasePath) ?: null;
                $diagnostics['writable'] = is_writable($databasePath);
            } else {
                $directory = dirname($databasePath);
                $diagnostics['writable'] = is_dir($directory) && is_writable($directory);
            }

            try {
                $versionResult = DB::connection($connectionName)->selectOne('select sqlite_version() as version');
                $diagnostics['sqlite_version'] = $versionResult->version ?? null;
            } catch (\Throwable $throwable) {
                Log::debug('SyncMonitor: sqlite version lookup failed', [
                    'connection' => $connectionName,
                    'error' => $throwable->getMessage(),
                ]);
            }
        } else {
            $diagnostics['database_exists'] = ! empty($databasePath);
            $diagnostics['writable'] = true;
        }

        return $diagnostics;
    }

    protected function buildModelDiagnostics(): array
    {
        $models = config('sync.models', []);
        $result = [];

        foreach ($models as $key => $class) {
            if (! class_exists($class)) {
                $result[] = [
                    'key' => $key,
                    'name' => Str::headline($key),
                    'connection' => null,
                    'count' => null,
                    'latest' => null,
                    'error' => 'لم يتم العثور على الكلاس',
                ];
                continue;
            }

            /** @var Model $model */
            $model = new $class();
            $connection = $model->getConnectionName() ?: config('database.default');

            try {
                $query = $model->newQuery();
                $latestQuery = $model->newQuery();

                $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));
                if ($usesSoftDeletes && method_exists($query, 'withTrashed')) {
                    $query = $query->withTrashed();
                    $latestQuery = $latestQuery->withTrashed();
                }

                $count = $query->count();

                $timestampColumn = $model->getUpdatedAtColumn() ?? $model->getCreatedAtColumn();
                $latest = $timestampColumn
                    ? $latestQuery->orderByDesc($timestampColumn)->value($timestampColumn)
                    : null;

                $result[] = [
                    'key' => $key,
                    'name' => Str::headline(class_basename($model)),
                    'connection' => $connection,
                    'count' => $count,
                    'latest' => $latest ? Carbon::parse($latest)->toIso8601String() : null,
                    'error' => null,
                ];
            } catch (\Throwable $throwable) {
                Log::warning('SyncMonitor: unable to build stats for model', [
                    'model' => $class,
                    'error' => $throwable->getMessage(),
                ]);

                $result[] = [
                    'key' => $key,
                    'name' => Str::headline(class_basename($model)),
                    'connection' => $connection,
                    'count' => null,
                    'latest' => null,
                    'error' => $throwable->getMessage(),
                ];
            }
        }

        return $result;
    }
}

