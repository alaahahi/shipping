<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarTag extends Model
{
    use HasFactory;

    protected $table = 'car_tags';

    protected $fillable = [
        'owner_id',
        'name',
    ];

    public function cars()
    {
        return $this->belongsToMany(Car::class, 'car_tag_pivot', 'tag_id', 'car_id');
    }
}
