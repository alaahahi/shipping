<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarImagesHunter extends Model
{
    use HasFactory;
    protected $table = 'car_images_hunter';
    protected $fillable = [
        'id',
        'name',
        'year',
        'car_id',
        'created_at',
        'updated_at'
    ];
  }