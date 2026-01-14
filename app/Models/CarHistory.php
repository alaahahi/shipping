<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CarHistory extends Model
{
    use HasFactory;

    protected $table = 'car_history';

    protected $fillable = [
        'car_id',
        'action',
        'old_data',
        'new_data',
        'changes',
        'field_changed',
        'description',
        'user_id',
        'user_name',
        'ip_address',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'changes' => 'array',
    ];

    // العلاقات
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes مفيدة
    public function scopeByCar($query, $carId)
    {
        return $query->where('car_id', $carId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public static function logCreate(Car $car, ?User $user = null): void
    {
        // تنظيف البيانات قبل الحفظ لتجنب مشاكل null في التواريخ
        $carData = static::cleanCarDataForHistory($car->toArray());
        
        static::create([
            'car_id' => $car->id,
            'action' => 'create',
            'new_data' => $carData,
            'description' => 'تم إضافة سيارة جديدة',
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'ip_address' => request()->ip(),
        ]);
    }

    public static function logUpdate(Car $car, array $oldData, array $changes = [], ?User $user = null): void
    {
        // تنظيف البيانات قبل الحفظ
        $carData = static::cleanCarDataForHistory($car->toArray());
        $oldDataCleaned = static::cleanCarDataForHistory($oldData);
        $changesCleaned = static::cleanChangesForHistory($changes);
        
        static::create([
            'car_id' => $car->id,
            'action' => 'update',
            'old_data' => $oldDataCleaned,
            'new_data' => $carData,
            'changes' => $changesCleaned,
            'description' => 'تم تحديث بيانات السيارة',
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'ip_address' => request()->ip(),
        ]);
    }

    public static function logDelete(Car $car, ?User $user = null): void
    {
        // تنظيف البيانات قبل الحفظ
        $carData = static::cleanCarDataForHistory($car->toArray());
        
        static::create([
            'car_id' => $car->id,
            'action' => 'delete',
            'old_data' => $carData,
            'description' => 'تم حذف السيارة',
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'ip_address' => request()->ip(),
        ]);
    }
    
    /**
     * تنظيف بيانات السيارة قبل الحفظ في التاريخ
     * تحويل null dates إلى null بدلاً من محاولة parse
     */
    protected static function cleanCarDataForHistory(array $data): array
    {
        $cleaned = [];
        foreach ($data as $key => $value) {
            // إذا كان الحقل تاريخ وكان null، نتركه null
            if (in_array($key, ['date', 'created_at', 'updated_at', 'deleted_at']) && $value === null) {
                $cleaned[$key] = null;
            }
            // إذا كان الحقل تاريخ وكان Carbon instance، نحوله إلى string
            elseif (in_array($key, ['date', 'created_at', 'updated_at', 'deleted_at']) && $value instanceof \Carbon\Carbon) {
                $cleaned[$key] = $value->format('Y-m-d H:i:s');
            }
            // إذا كان الحقل تاريخ وكان string يحتوي على 'null'، نجعله null
            elseif (in_array($key, ['date', 'created_at', 'updated_at', 'deleted_at']) && is_string($value) && strtolower(trim($value)) === 'null') {
                $cleaned[$key] = null;
            }
            else {
                $cleaned[$key] = $value;
            }
        }
        return $cleaned;
    }
    
    /**
     * تنظيف التغييرات قبل الحفظ
     */
    protected static function cleanChangesForHistory(array $changes): array
    {
        $cleaned = [];
        foreach ($changes as $key => $change) {
            if (is_array($change)) {
                $cleaned[$key] = [
                    'old' => static::cleanValueForHistory($change['old'] ?? null),
                    'new' => static::cleanValueForHistory($change['new'] ?? null),
                ];
            } else {
                $cleaned[$key] = $change;
            }
        }
        return $cleaned;
    }
    
    /**
     * تنظيف قيمة واحدة للتاريخ
     */
    protected static function cleanValueForHistory($value)
    {
        if ($value === null) {
            return null;
        }
        
        // إذا كان string يحتوي على 'null'
        if (is_string($value) && strtolower(trim($value)) === 'null') {
            return null;
        }
        
        // إذا كان Carbon instance
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d H:i:s');
        }
        
        return $value;
    }

    public function getChangesSummaryAttribute(): string
    {
        if (!$this->changes) return '';

        $summary = [];
        foreach ($this->changes as $field => $change) {
            if (is_array($change) && isset($change['old'], $change['new'])) {
                $summary[] = "{$field}: {$change['old']} → {$change['new']}";
            }
        }

        return implode(', ', $summary);
    }
}
