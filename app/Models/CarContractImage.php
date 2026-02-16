<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarContractImage extends Model
{
    use HasFactory;

    protected $table = 'car_contract_images';

    protected $fillable = [
        'id',
        'name',
        'car_contract_id',
        'created_at',
        'updated_at'
    ];

    public function carContract()
    {
        return $this->belongsTo(CarContract::class, 'car_contract_id');
    }
}
