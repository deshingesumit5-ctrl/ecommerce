<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'branch_id',
        'customer_id',
        'delivery_boy_id',
        'coupon_id',
        'subtotal',
        'discount_amount',
        'delivery_charge',
        'tax_amount',
        'total_amount',
        'payment_mode',
        'payment_status',
        'order_status',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'special_notes',
        'placed_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'delivery_lat' => 'float',
        'delivery_lng' => 'float',
        'placed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(DeliveryBoy::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class)->latest();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
