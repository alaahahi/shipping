<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driving extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'driving';
    protected $fillable = [
        'id',
        'client_id',
        'name',
        'created_at',
        'updated_at',
        'car_number',
        'year',
        'owner_id',
        'color',
        'vin',
        'year_date',
        'car_type',
        'user_id',
        'created',
        'deleted_at',
        'note'
    ];


    protected $dates = ['deleted_at']; // Define the deleted_at column as a date

  }