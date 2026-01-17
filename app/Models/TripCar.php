<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripCar extends Model
{
    use HasFactory;

    protected $table = 'trip_cars';

    protected $fillable = [
        'trip_id',
        'trip_company_id',
        'car_id',
        'weight',
        'shipper_name',
        'description',
        'chassis_no',
        'consignee_name',
        'consignee_id',
        'code',
        'owner_id',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    // العلاقات
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function tripCompany()
    {
        return $this->belongsTo(TripCompany::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function consignee()
    {
        return $this->belongsTo(User::class, 'consignee_id');
    }
}
