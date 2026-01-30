<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalTransportPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_company_id',
        'amount',
        'driver_name',
        'cmr_number',
        'payment_date',
        'notes',
        'owner_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * العلاقة مع TripCompany
     */
    public function tripCompany()
    {
        return $this->belongsTo(TripCompany::class);
    }
}
