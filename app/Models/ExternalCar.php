<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalCar extends Model
{
    use HasFactory;

    protected $table = 'external_cars';

    protected $attributes = [
        'paid_dollar' => 0,
        'paid_dinar' => 0,
    ];

    protected $fillable = [
        'owner_id',
        'user_id',
        'dealer_name',
        'vin',
        'car_type',
        'year',
        'car_color',
        'car_number',
        'paid_dollar',
        'paid_dinar',
        'note',
        'date',
    ];

    protected $casts = [
        'year' => 'integer',
        'paid_dollar' => 'integer',
        'paid_dinar' => 'integer',
        'date' => 'date',
    ];
}
