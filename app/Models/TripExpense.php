<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripExpense extends Model
{
    use HasFactory;

    protected $table = 'trip_expenses';

    protected $fillable = [
        'trip_id',
        'expense_type',
        'amount',
        'currency',
        'note',
        'date',
        'owner_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    // العلاقات
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    // Event Listeners لتحديث مجموع المصاريف تلقائياً
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($expense) {
            $expense->updateTripTotalExpenses();
        });

        static::deleted(function ($expense) {
            $expense->updateTripTotalExpenses();
        });
    }

    // تحديث مجموع المصاريف في جدول trips
    public function updateTripTotalExpenses()
    {
        $trip = $this->trip;
        if ($trip) {
            $totalDollar = $trip->expenses()
                ->where('currency', 'dollar')
                ->sum('amount');
            
            $totalDinar = $trip->expenses()
                ->where('currency', 'dinar')
                ->sum('amount');

            // حفظ المجموع بالعملة الأساسية للرحلة
            $trip->update([
                'total_expenses' => $trip->expenses_currency === 'dollar' ? $totalDollar : $totalDinar,
            ]);
        }
    }
}
