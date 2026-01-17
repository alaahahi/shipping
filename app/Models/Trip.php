<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $table = 'trips';

    protected $fillable = [
        'sailing_date',
        'captain',
        'pol',
        'pod',
        'flag',
        'ship_name',
        'voy_no',
        'total_expenses',
        'expenses_currency',
        'note',
        'owner_id',
    ];

    protected $casts = [
        'sailing_date' => 'date',
        'total_expenses' => 'decimal:2',
        'expenses_currency' => 'string',
    ];

    // العلاقات
    public function companies()
    {
        return $this->hasMany(TripCompany::class);
    }

    public function cars()
    {
        return $this->hasMany(TripCar::class);
    }

    public function expenses()
    {
        return $this->hasMany(TripExpense::class);
    }

    // Accessors للحصول على مجموع المصاريف
    public function getTotalExpensesDollarAttribute()
    {
        return $this->expenses()
            ->where('currency', 'dollar')
            ->sum('amount');
    }

    public function getTotalExpensesDinarAttribute()
    {
        return $this->expenses()
            ->where('currency', 'dinar')
            ->sum('amount');
    }

    // حساب عدد السيارات
    public function getTotalCarsAttribute()
    {
        return $this->cars()->count();
    }

    // حساب عدد الشركات
    public function getTotalCompaniesAttribute()
    {
        return $this->companies()->count();
    }

    // حساب عدد العملاء (CONSIGNEE)
    public function getTotalConsigneesAttribute()
    {
        return $this->cars()
            ->whereNotNull('consignee_id')
            ->distinct('consignee_id')
            ->count('consignee_id');
    }

    // حساب إجمالي الوزن
    public function getTotalWeightAttribute()
    {
        return $this->cars()->sum('weight');
    }
}
