<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'address',
        'city',
        'pincode',
        'lat',
        'lng',
        'status',
        'password',
        'plain_password',
        'otp',
    ];

    protected $hidden = [
        'password',
        'plain_password',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
