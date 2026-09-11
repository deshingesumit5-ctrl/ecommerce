<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'payment_mode',
        'amount',
        'status',
        'gateway_response',
        'collected_at',
        'cod_verified',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collected_at' => 'datetime',
        'cod_verified' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
