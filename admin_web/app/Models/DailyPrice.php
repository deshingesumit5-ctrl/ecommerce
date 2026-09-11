<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'price',
        'effective_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'effective_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
