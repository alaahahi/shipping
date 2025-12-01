<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarDamageReport extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'car_damage_reports';
    
    protected $fillable = [
        'id',
        'driver_name',
        'cmr_number',
        'cars_count',
        'total_damage',
        'cars_info',
        'created',
        'year_date',
        'owner_id',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'cars_info' => 'array',
        'total_damage' => 'decimal:2',
        'cars_count' => 'integer',
        'created' => 'date',
    ];

    protected $dates = ['deleted_at'];
}
