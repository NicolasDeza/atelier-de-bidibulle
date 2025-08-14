<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\Product;

class OrderProduct extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProductFactory> */
    use HasFactory;

     protected $table = 'order_products'; // nom de la table pivot

     protected $casts = [
    'order_id' => 'integer',
    'product_id' => 'integer',
    'quantity' => 'integer',
    'price' => 'float',
];

    protected $fillable = [
         'order_id',
        'product_id',
        'quantity',
        'price',
        'customization'
    ];

     public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
