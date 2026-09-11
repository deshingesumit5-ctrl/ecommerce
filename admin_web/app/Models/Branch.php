<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'contact_phone',
        'address',
        'latitude',
        'longitude',
        'radius_km',
        'status',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'radius_km' => 'float',
    ];

    public function deliveryBoys()
    {
        return $this->hasMany(DeliveryBoy::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function dailyPrices()
    {
        return $this->hasMany(DailyPrice::class);
    }

    public function deliveryChargeConfig()
    {
        return $this->hasOne(DeliveryChargeConfig::class);
    }
}
