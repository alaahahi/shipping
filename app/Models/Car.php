<?php

namespace App\Models;
use App\Models\User;
use App\Traits\TracksHistory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Car extends Model
{
    use HasFactory, TracksHistory;
    protected $table = 'car';

    /**
     * Defaults for NOT NULL columns (MySQL has DEFAULT 0; SQLite export often drops DEFAULT).
     */
    protected $attributes = [
        'discount' => 0,
        'paid' => 0,
        'profit' => 0,
        'results' => 0,
        'expenses' => 0,
        'expenses_s' => 0,
        'total' => 0,
        'total_s' => 0,
        'dinar' => 0,
        'dolar_price' => 0,
        'dolar_custom' => 0,
        'checkout' => 0,
        'shipping_dolar' => 0,
        'coc_dolar' => 0,
        'container_open' => 0,
        'dinar_s' => 0,
        'dolar_price_s' => 0,
        'dolar_custom_s' => 0,
        'checkout_s' => 0,
        'shipping_dolar_s' => 0,
        'coc_dolar_s' => 0,
        'container_open_s' => 0,
        'is_exit' => 0,
        'contract_id' => 0,
        'car_have_expenses' => 0,
        'expenses_posted' => false,
        'land_shipping' => 0,
        'land_shipping_s' => 0,
        'land_shipping_dinar' => 0,
        'land_shipping_dinar_s' => 0,
        'registration_sync_sales' => 0,
        'car_price' => 0,
        'registration_exchange_rate' => 0,
        'registration_pre_expenses' => 0,
        'registration_pre_expenses_s' => 0,
        'registration_linked_usd' => 0,
    ];

    protected $fillable = [
        'id',
        'no',
        'user_id',
        'created_at',
        'updated_at',
        'note',
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
        'container_open',
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
        'container_open_s',
        'total_s',
        'discount',
        'expenses_s',
        'expenses_breakdown',
        'is_exit',
        'owner_id',
        'contract_id',
        'year_date',
        'car_have_expenses',
        'expenses_posted',
        'expenses_posted_at',
        'registration_exchange_rate',
        'registration_pre_expenses',
        'registration_pre_expenses_s',
        'registration_sync_sales',
        'registration_linked_usd',
        'land_shipping',
        'land_shipping_s',
        'land_shipping_dinar',
        'land_shipping_dinar_s',
        'car_price',
        'direct_discount_base',
    ];
    
    // منع حفظ oldAttributes كعمود في قاعدة البيانات
    protected $guarded = ['oldAttributes'];
    
    protected $casts = [
        'car_number' => 'string',
        'is_exit' => 'boolean',
        'expenses_posted' => 'boolean',
        'expenses_posted_at' => 'datetime',
        'registration_exchange_rate' => 'integer',
        'registration_pre_expenses' => 'integer',
        'registration_pre_expenses_s' => 'integer',
        'registration_sync_sales' => 'boolean',
        'registration_linked_usd' => 'integer',
        'total_s' => 'integer',
        'amount' => 'integer',
        'paid' => 'integer',
        'profit' => 'integer',
        'year_date' => 'integer',
        'discount' => 'integer',
        'expenses' => 'integer',
        'expenses_s' => 'integer',
        'expenses_breakdown' => 'array',
        'land_shipping' => 'integer',
        'land_shipping_s' => 'integer',
        'land_shipping_dinar' => 'integer',
        'land_shipping_dinar_s' => 'integer',
        'car_price' => 'integer',
        'direct_discount_base' => 'integer',
        'car_color' => 'string',
        'year' => 'integer',
        'car_type' => 'string',
        'vin' => 'string',
        'car_number' => 'string',
        'dinar' => 'integer',
        'dolar_price' => 'integer',
        'dolar_custom' => 'integer',
        'checkout' => 'integer',
        'shipping_dolar' => 'integer',
        'coc_dolar' => 'integer',
        'container_open' => 'integer',
        'container_open_s' => 'integer',
        'total' => 'integer',
        'paid' => 'integer',
        'profit' => 'integer',
    ];
    protected static function booted(): void
    {
        static::creating(function (self $car) {
            self::ensureDirectDiscountBaseColumn();

            $current = (int) ($car->direct_discount_base ?? 0);
            if ($current > 0) {
                return;
            }

            $car->direct_discount_base = SystemConfig::directDiscountBase();
        });
    }

    /**
     * Ensure the per-car snapshot column exists (covers SQLite when migrate was skipped).
     */
    public static function ensureDirectDiscountBaseColumn(): void
    {
        try {
            if (! Schema::hasTable('car')) {
                return;
            }

            if (! Schema::hasColumn('car', 'direct_discount_base')) {
                Schema::table('car', function (Blueprint $table) {
                    $table->unsignedInteger('direct_discount_base')->default(SystemConfig::DEFAULT_DIRECT_DISCOUNT_BASE);
                });
            }
        } catch (\Throwable $e) {
            // Callers fall back to DEFAULT_DIRECT_DISCOUNT_BASE.
        }
    }

    /**
     * Snapshot rate used for «جمرك منفست» / «خصم مباشر».
     * Existing cars without a value stay on the historical 130000 base.
     */
    public function resolveDirectDiscountBase(): int
    {
        $value = (int) ($this->direct_discount_base ?? 0);

        return $value > 0 ? $value : SystemConfig::DEFAULT_DIRECT_DISCOUNT_BASE;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function Client()
    {
        return $this->belongsTo(User::class);
    }
    public function transactions()
    {
        return $this->morphMany(Transactions::class, 'morphed');
    }
    public function contract()
    {
        // Define a one-to-one relationship with the Car model
        return $this->hasOne(Contract::class, 'car_id', 'id');
    }
    public function exitcar()
    {
        // Define a one-to-one relationship with the Car model
        return $this->hasOne(ExitCar::class, 'car_id', 'id');
    }
    public function CarImages()
    {
        return $this->hasMany(ContractImg::class, 'car_id');
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

    public function carexpenses()
    {
        return $this->hasMany(CarExpenses::class, 'car_id', 'id');
    }

    public function getSumAmountDollarAttribute()
    {
        // Calculate and return the sum of amount_dollar for all related expenses
        return $this->carexpenses->sum('amount_dollar');
    }

    public function getSumAmountDinarAttribute()
    {
        // Calculate and return the sum of amount_dinar for all related expenses
        return $this->carexpenses->sum('amount_dinar');
    }

    public function internalSale()
    {
        return $this->hasOne(InternalSale::class, 'car_id');
    }

    public function carSale()
    {
        return $this->hasOne(CarSale::class, 'car_id');
    }

    public function tags()
    {
        return $this->belongsToMany(CarTag::class, 'car_tag_pivot', 'car_id', 'tag_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(CarHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get recent history for this car
     */
    public function getRecentHistory(int $limit = 10)
    {
        return $this->history()->limit($limit)->get();
    }
  }