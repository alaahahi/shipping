<?php

namespace App\Traits;

use App\Models\CarHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait TracksHistory
{
    protected static function bootTracksHistory()
    {
        static::created(function (Model $model) {
            if ($model instanceof \App\Models\Car) {
                CarHistory::logCreate($model, Auth::user());
            }
        });

        static::updating(function (Model $model) {
            if ($model instanceof \App\Models\Car) {
                $model->oldAttributes = $model->getOriginal();
            }
        });

        static::updated(function (Model $model) {
            if ($model instanceof \App\Models\Car) {
                $changes = static::getChangesArray($model->oldAttributes, $model->getAttributes());
                if (!empty($changes)) {
                    CarHistory::logUpdate($model, $model->oldAttributes, $changes, Auth::user());
                }
            }
        });

        static::deleted(function (Model $model) {
            if ($model instanceof \App\Models\Car) {
                CarHistory::logDelete($model, Auth::user());
            }
        });
    }

    /**
     * Get array of changes between old and new data
     */
    protected static function getChangesArray(array $oldData, array $newData): array
    {
        $changes = [];

        foreach ($newData as $key => $value) {
            if (array_key_exists($key, $oldData)) {
                if ($oldData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value
                    ];
                }
            } else {
                // New field added
                $changes[$key] = [
                    'old' => null,
                    'new' => $value
                ];
            }
        }

        // Check for removed fields
        foreach ($oldData as $key => $value) {
            if (!array_key_exists($key, $newData)) {
                $changes[$key] = [
                    'old' => $value,
                    'new' => null
                ];
            }
        }

        return $changes;
    }
}


