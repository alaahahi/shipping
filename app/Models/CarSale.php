<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarSale extends Model
{
    use HasFactory;

    protected $table = 'car_sales';

    protected $fillable = [
        'car_id',
        'buyer_id',
        'sale_price',
        'paid_amount',
        'remaining_amount',
        'note',
        'sale_date',
        'owner_id',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'sale_date' => 'date',
    ];

    // Relations
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class, 'car_sale_id');
    }

    // Auto-calculate remaining amount before saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($carSale) {
            $carSale->remaining_amount = ($carSale->sale_price ?? 0) - ($carSale->paid_amount ?? 0);
        });
    }
}

