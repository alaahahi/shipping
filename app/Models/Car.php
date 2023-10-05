<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Car extends Model
{
    use HasFactory;
    protected $table = 'car';
    protected $fillable = [
        'id',
        'no',
        'user_id',
        'user_purchase_id',
        'user_accepted',
        'user_rejected',
        'created_at',
        'updated_at',
        'purchase_price',
        'purchase_data',
        'pay_data',
        'pay_price',
        'note',
        'note_pay',
        'client_id',
        'results',
        'car_type',
        'vin',
        'car_number',
        'dinar',
        'dolar_price',
        'dolar_custom',
        'checkout',
        'shipping_dolar',
        'coc_dolar',
        'total',
        'paid',
        'profit',
        'date',
        'car_color',
        'year',
        'expenses',
        'dinar_s',
        'dolar_price_s',
        'dolar_custom_s',
        'checkout_s',
        'shipping_dolar_s',
        'coc_dolar_s',
        'total_s',
        'discount',
        'expenses_s',
        'is_exit'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function carmodel()
    {
        return $this->belongsTo(CarModel::class,'model_id');
    }
    public function name()
    {
        return $this->belongsTo(Name::class);
    }
    public function color()
    {
        return $this->belongsTo(Color::class);
    }
    public function contract()
    {
        return $this->hasOne(Contract::class);
    }
    public function Client()
    {
        return $this->belongsTo(User::class);
    }
    public function transactions()
    {
        return $this->morphMany(Transactions::class, 'morphed');
    }

    protected $appends = ['image_url'];

    /**
     * Get Added Image Attribute URL.
     *
     * @return string|null
     */
    public function getImageUrlAttribute(): array
    {

        $images = json_decode($this->image);

        if (!is_array($images)) {
            return [];
        }
    
        $imageUrls = [];
    
        foreach ($images as $image) {
            $imageUrl = url('') . "/storage/car/" . $image;
            if (Str::contains($imageUrl, '/car/car/')) {
                $imageUrl = str_replace('/car/car/', '/car/', $imageUrl);
            }
            
            $imageUrls[] = $imageUrl;
        }
    
        return $imageUrls;
    }

  }