<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    use HasFactory;

    protected $table = 'sale_payments';

    protected $fillable = [
        'car_sale_id',
        'amount',
        'payment_date',
        'note',
        'owner_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relations
    public function carSale()
    {
        return $this->belongsTo(CarSale::class, 'car_sale_id');
    }
}

