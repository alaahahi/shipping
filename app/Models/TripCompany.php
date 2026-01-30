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
        'shipping_price_aed',
        'clearance_per_car',
        'internal_transport_total',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'shipping_price_per_car' => 'decimal:2',
        'shipping_price_aed' => 'decimal:2',
        'clearance_per_car' => 'decimal:2',
        'internal_transport_total' => 'decimal:2',
    ];
    
    // حساب النقل الداخلي لكل سيارة (من مجموع الدفعات)
    public function getInternalTransportPerCarAttribute()
    {
        $carsCount = $this->cars()->count();
        $totalTransport = $this->transportPayments()->sum('amount');
        
        if ($carsCount > 0 && $totalTransport) {
            return round($totalTransport / $carsCount, 2);
        }
        return 0;
    }
    
    // حساب الإجمالي الكلي (الشحن + التخليص + مجموع النقل)
    public function getTotalAmountAttribute()
    {
        $carsCount = $this->cars()->count();
        $shipping = ($this->shipping_price_per_car ?? 0) * $carsCount;
        $clearance = ($this->clearance_per_car ?? 0) * $carsCount;
        $totalTransport = $this->transportPayments()->sum('amount');
        
        return $shipping + $clearance + $totalTransport;
    }

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

    public function transportPayments()
    {
        return $this->hasMany(InternalTransportPayment::class);
    }

    // حساب عدد السيارات للشركة
    public function getTotalCarsAttribute()
    {
        return $this->cars()->count();
    }
    
    // حساب مجموع دفعات النقل الداخلي
    public function getTransportPaymentsTotalAttribute()
    {
        return $this->transportPayments()->sum('amount');
    }
}
