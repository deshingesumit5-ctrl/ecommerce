<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryBoy extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'mobile',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'username',
        'password',
        'plain_password',
        'is_online',
        'wallet_balance',
        'current_lat',
        'current_lng',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'wallet_balance' => 'decimal:2',
        'current_lat' => 'float',
        'current_lng' => 'float',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
