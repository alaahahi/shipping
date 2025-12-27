<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        static::create([
            'car_id' => $car->id,
            'action' => 'create',
            'new_data' => $car->toArray(),
            'description' => 'تم إضافة سيارة جديدة',
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'ip_address' => request()->ip(),
        ]);
    }

    public static function logUpdate(Car $car, array $oldData, array $changes = [], ?User $user = null): void
    {
        static::create([
            'car_id' => $car->id,
            'action' => 'update',
            'old_data' => $oldData,
            'new_data' => $car->toArray(),
            'changes' => $changes,
            'description' => 'تم تحديث بيانات السيارة',
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'ip_address' => request()->ip(),
        ]);
    }

    public static function logDelete(Car $car, ?User $user = null): void
    {
        static::create([
            'car_id' => $car->id,
            'action' => 'delete',
            'old_data' => $car->toArray(),
            'description' => 'تم حذف السيارة',
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'ip_address' => request()->ip(),
        ]);
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
