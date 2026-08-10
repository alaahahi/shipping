<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalCarPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'external_car_payments';

    protected $attributes = [
        'amount_dollar' => 0,
        'amount_dinar' => 0,
        'is_posted' => false,
    ];

    protected $fillable = [
        'external_car_id',
        'owner_id',
        'user_id',
        'amount_dollar',
        'amount_dinar',
        'is_posted',
        'note',
        'created',
    ];

    protected $casts = [
        'amount_dollar' => 'integer',
        'amount_dinar' => 'integer',
        'is_posted' => 'boolean',
        'created' => 'date',
    ];

    public function externalCar()
    {
        return $this->belongsTo(ExternalCar::class, 'external_car_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
