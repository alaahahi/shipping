<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'jobs' => ['required', 'array'],
            'jobs.*.model' => ['required', 'string'],
            'jobs.*.uuid' => ['nullable', 'string'],
            'jobs.*.operation' => ['required', 'in:create,update,delete'],
            'jobs.*.payload' => ['required', 'array'],
        ]);

        $models = config('sync.models', []);
        $synced = [];

        foreach ($validated['jobs'] as $job) {
            $modelKey = Arr::get($job, 'model');

            if (! isset($models[$modelKey])) {
                continue;
            }

            $modelClass = $models[$modelKey];
            $payload = Arr::get($job, 'payload', []);
            $uuid = Arr::get($job, 'uuid') ?: Arr::get($payload, 'uuid');

            if (! $uuid) {
                $uuid = (string) Str::uuid();
                $payload['uuid'] = $uuid;
            }

            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = new $modelClass();

            $usesSoftDeletes = in_array(
                \Illuminate\Database\Eloquent\SoftDeletes::class,
                class_uses_recursive($model)
            );

            DB::connection($model->getConnectionName())->transaction(function () use ($model, $uuid, $job, $payload, $usesSoftDeletes) {
                $query = $model->newQuery();

                if ($usesSoftDeletes && method_exists($query, 'withTrashed')) {
                    $query = $query->withTrashed();
                }

                $operation = Arr::get($job, 'operation');

                if ($operation === 'delete') {
                    $instance = $query->where('uuid', $uuid)->first();

                    if ($instance) {
                        $instance->delete();
                    }
                    return;
                }

                $data = Arr::except($payload, ['id']);

                $instance = $query->where('uuid', $uuid)->first();

                if ($instance) {
                    $instance->forceFill($data)->save();
                } else {
                    $model->forceFill($data)->save();
                }
            });

            $synced[] = [
                'uuid' => $uuid,
                'model' => $modelKey,
            ];
        }

        return response()->json([
            'status' => 'ok',
            'synced' => $synced,
        ], Response::HTTP_CREATED);
    }

    public function changes(Request $request)
    {
        $models = config('sync.models', []);
        $lastSync = $request->query('last_sync_time');
        $lastSyncTime = $lastSync ? Carbon::parse($lastSync) : null;

        $changes = [];

        foreach ($models as $key => $class) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = new $class();
            $usesSoftDeletes = in_array(
                \Illuminate\Database\Eloquent\SoftDeletes::class,
                class_uses_recursive($model)
            );

            $query = $model->newQuery();

            if ($usesSoftDeletes && method_exists($query, 'withTrashed')) {
                $query = $query->withTrashed();
            }

            if ($lastSyncTime) {
                $query->where(function ($q) use ($lastSyncTime, $usesSoftDeletes) {
                    $q->where('updated_at', '>', $lastSyncTime);

                    if ($usesSoftDeletes) {
                        $q->orWhere('deleted_at', '>', $lastSyncTime);
                    }
                });
            }

            $records = $query->get()->map(function ($item) use ($usesSoftDeletes, $lastSyncTime) {
                $payload = $item->toArray();
                $operation = 'update';

                if ($usesSoftDeletes && method_exists($item, 'trashed') && $item->trashed()) {
                    $operation = 'delete';
                } elseif ($lastSyncTime && isset($item->created_at) && $item->created_at instanceof Carbon) {
                    $operation = $item->created_at->greaterThan($lastSyncTime) ? 'create' : 'update';
                }

                return [
                    'operation' => $operation,
                    'payload' => $payload,
                ];
            })->values();

            if ($records->isNotEmpty()) {
                $changes[] = [
                    'model' => $key,
                    'records' => $records,
                ];
            }
        }

        $timestamp = now()->toIso8601String();
        Cache::put(config('sync.last_sync_cache_key'), $timestamp, now()->addDays(7));

        return response()->json([
            'last_sync_time' => $timestamp,
            'changes' => $changes,
        ]);
    }
}

