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
        'expenses_posted' => false,
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
        'expenses_posted',
        'expenses_posted_at',
        'note',
        'date',
    ];

    protected $casts = [
        'year' => 'integer',
        'paid_dollar' => 'integer',
        'paid_dinar' => 'integer',
        'expenses_posted' => 'boolean',
        'expenses_posted_at' => 'datetime',
        'date' => 'date',
    ];

    public function payments()
    {
        return $this->hasMany(ExternalCarPayment::class, 'external_car_id');
    }

    public function syncPaidTotals(): void
    {
        $this->paid_dollar = (int) $this->payments()->sum('amount_dollar');
        $this->paid_dinar = (int) $this->payments()->sum('amount_dinar');
        $this->save();
    }
}
