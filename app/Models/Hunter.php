<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hunter extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'hunter';

    protected $fillable = [
        'car_type',
        'date',
        'car_color',
        'year',
        'note',
        'client_id',
        'vin',
        'status',
        'price_p',
        'price_s',
        'data_hunter'
    ];
    protected $dates = ['deleted_at'];



    public function Client()
    {
        return $this->belongsTo(User::class);
    }
    public function CarImages()
    {
        return $this->hasMany(CarImages::class, 'car_id');
    }

}
