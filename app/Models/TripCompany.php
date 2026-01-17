<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripCompany extends Model
{
    use HasFactory;

    protected $table = 'trip_companies';

    protected $fillable = [
        'trip_id',
        'company_id',
        'excel_file_path',
        'uploaded_at',
        'owner_id',
        'shipping_price_per_car',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'shipping_price_per_car' => 'decimal:2',
    ];

    // العلاقات
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function cars()
    {
        return $this->hasMany(TripCar::class);
    }

    // حساب عدد السيارات للشركة
    public function getTotalCarsAttribute()
    {
        return $this->cars()->count();
    }
}
