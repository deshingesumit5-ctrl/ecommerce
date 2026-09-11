<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryChargeConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'min_order_free_delivery',
        'standard_charge',
        'express_charge',
        'status',
    ];

    protected $casts = [
        'min_order_free_delivery' => 'decimal:2',
        'standard_charge' => 'decimal:2',
        'express_charge' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
