<?php

namespace App\Services\Sync;

use App\Models\SyncJob;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncService
{
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = (bool) config('sync.enabled', true);
    }

    public function recordLocalChange(string $modelKey, string $operation, array $data): ?SyncJob
    {
        if (! $this->enabled) {
            return null;
        }

        $modelMap = config('sync.models', []);

        if (! array_key_exists($modelKey, $modelMap)) {
            Log::warning("SyncService: attempted to record unknown model key [{$modelKey}]");
            return null;
        }

        return SyncJob::create([
            'model' => $modelKey,
            'uuid' => Arr::get($data, 'uuid', (string) Str::uuid()),
            'operation' => $operation,
            'payload' => $data,
        ]);
    }

    public function pushLocalChangesToServer(): void
    {
        if (! $this->enabled) {
            return;
        }

        $serverUrl = rtrim(config('sync.server_url'), '/');
        $endpoint = ltrim(config('sync.push_endpoint'), '/');

        if (empty($serverUrl)) {
            Log::warning('SyncService: server_url is not configured');
            return;
        }

        $jobs = SyncJob::whereNull('synced_at')
            ->orderBy('id')
            ->limit(100)
            ->get();

        if ($jobs->isEmpty()) {
            return;
        }

        $response = Http::acceptJson()
            ->withToken(config('sync.api_token'))
            ->post("{$serverUrl}/{$endpoint}", [
                'jobs' => $jobs->map(fn (SyncJob $job) => [
                    'model' => $job->model,
                    'uuid' => $job->uuid,
                    'operation' => $job->operation,
                    'payload' => $job->payload,
                    'created_at' => optional($job->created_at)->toIso8601String(),
                ]),
            ]);

        if (! $response->successful()) {
            Log::warning('SyncService: push failed', ['status' => $response->status(), 'body' => $response->body()]);
            $jobs->each->increment('attempts');
            return;
        }

        $syncedUuids = collect($response->json('synced', []))
            ->pluck('uuid')
            ->filter()
            ->unique()
            ->values();

        if ($syncedUuids->isNotEmpty()) {
            SyncJob::whereIn('uuid', $syncedUuids)->update([
                'synced_at' => now(),
            ]);
        }
    }

    public function pullRemoteChanges(): void
    {
        if (! $this->enabled) {
            return;
        }

        $serverUrl = rtrim(config('sync.server_url'), '/');
        $endpoint = ltrim(config('sync.pull_endpoint'), '/');

        if (empty($serverUrl)) {
            Log::warning('SyncService: server_url is not configured');
            return;
        }

        $lastSync = Cache::get(config('sync.last_sync_cache_key'));

        $response = Http::acceptJson()
            ->withToken(config('sync.api_token'))
            ->get("{$serverUrl}/{$endpoint}", array_filter([
                'last_sync_time' => $lastSync,
            ]));

        if (! $response->successful()) {
            Log::warning('SyncService: pull failed', ['status' => $response->status(), 'body' => $response->body()]);
            return;
        }

        $payload = $response->json();

        $this->applyRemoteChanges(Arr::get($payload, 'changes', []));

        if ($timestamp = Arr::get($payload, 'last_sync_time')) {
            Cache::put(
                config('sync.last_sync_cache_key'),
                $timestamp,
                CarbonInterval::days(7)
            );
        }
    }

    public function applyRemoteChanges(array $changes): void
    {
        if (! $this->enabled) {
            return;
        }

        $models = config('sync.models', []);

        foreach ($changes as $changeSet) {
            $modelKey = Arr::get($changeSet, 'model');
            $records = Arr::get($changeSet, 'records', []);

            if (! $modelKey || ! isset($models[$modelKey])) {
                continue;
            }

            $class = $models[$modelKey];

            foreach ($records as $record) {
                $operation = Arr::get($record, 'operation', 'update');
                $attributes = Arr::get($record, 'payload', []);
                $uuid = Arr::get($attributes, 'uuid');

                if (! $uuid) {
                    continue;
                }

                /** @var Model $model */
                $model = new $class();

                $usesSoftDeletes = in_array(
                    \Illuminate\Database\Eloquent\SoftDeletes::class,
                    class_uses_recursive($model)
                );

                DB::connection($model->getConnectionName())->transaction(function () use ($model, $operation, $attributes, $uuid, $usesSoftDeletes) {
                    $query = $model->newQuery();

                    if ($usesSoftDeletes && method_exists($query, 'withTrashed')) {
                        $query = $query->withTrashed();
                    }

                    $query = $query->where('uuid', $uuid);

                    if ($operation === 'delete') {
                        $instance = $query->first();

                        if ($instance && method_exists($instance, 'delete')) {
                            $instance->delete();
                        }
                        return;
                    }

                    if ($operation === 'update' || $operation === 'create') {
                        $data = Arr::except($attributes, ['id']);

                        $instance = $query->first();

                        if ($instance) {
                            if ($usesSoftDeletes && array_key_exists('deleted_at', $data) && empty($data['deleted_at']) && method_exists($instance, 'restore')) {
                                $instance->restore();
                            }

                            $instance->forceFill($data)->save();
                        } else {
                            $model->forceFill($data)->save();

                            if ($usesSoftDeletes && !empty($data['deleted_at']) && method_exists($model, 'delete')) {
                                $model->delete();
                            }
                        }
                    }
                });
            }
        }
    }
}

