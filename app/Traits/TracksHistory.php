<?php

namespace App\Traits;

use App\Models\CarHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait TracksHistory
{
    // متغير static لتخزين البيانات القديمة مؤقتاً
    protected static $oldAttributesCache = [];

    protected static function bootTracksHistory()
    {
        static::created(function (Model $model) {
            if ($model instanceof \App\Models\Car) {
                CarHistory::logCreate($model, Auth::user());
            }
        });

        static::updating(function (Model $model) {
            if ($model instanceof \App\Models\Car) {
                // حفظ البيانات القديمة في cache بدلاً من خاصية على الـ model
                static::$oldAttributesCache[$model->getKey()] = $model->getOriginal();
            }
        });

        static::updated(function (Model $model) {
            if ($model instanceof \App\Models\Car) {
                $oldAttributes = static::$oldAttributesCache[$model->getKey()] ?? [];
                $changes = static::getChangesArray($oldAttributes, $model->getAttributes());
                if (!empty($changes)) {
                    CarHistory::logUpdate($model, $oldAttributes, $changes, Auth::user());
                }
                // تنظيف cache بعد الاستخدام
                unset(static::$oldAttributesCache[$model->getKey()]);
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


