<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarContractInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_contract_id',
        'owner_id',
        'user_id',
        'amount',
        'received_by',
        'note',
        'created',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function carContract()
    {
        return $this->belongsTo(CarContract::class, 'car_contract_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
