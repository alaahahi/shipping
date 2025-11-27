<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalSale extends Model
{
    use HasFactory;

    protected $table = 'internal_sales';

    protected $fillable = [
        'client_id',
        'car_id',
        'car_price',
        'shipping',
        'sale_price',
        'paid_amount',
        'expenses',
        'additional_expenses',
        'profit',
        'note',
        'sale_date',
        'payments',
    ];

    protected $casts = [
        'car_price' => 'decimal:2',
        'shipping' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'expenses' => 'decimal:2',
        'additional_expenses' => 'decimal:2',
        'profit' => 'decimal:2',
        'sale_date' => 'date',
        'payments' => 'array',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function payments()
    {
        return $this->hasMany(BuyerPayment::class, 'internal_sale_id');
    }

    // Auto-calculate profit before saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($sale) {
            // الربح = سعر البيع - (سعر السيارة + المصاريف + مصاريف إضافية)
            $sale->profit = ($sale->sale_price ?? 0) - ($sale->car_price ?? 0) - ($sale->expenses ?? 0) - ($sale->additional_expenses ?? 0);
        });
    }
}

