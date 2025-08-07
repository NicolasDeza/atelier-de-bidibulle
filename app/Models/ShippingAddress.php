<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\City;

class ShippingAddress extends Model
{
    /** @use HasFactory<\Database\Factories\ShippingAddressFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'full_name',
        'address_line_1',
        'address_line_2',
        'postal_code',
        'phone_number',
        'city_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
