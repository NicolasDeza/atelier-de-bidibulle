<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;


    protected $casts = [
    'rating' => 'integer',
    'user_id' => 'integer',
    'product_id' => 'integer',
];

      protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment',
    ];

        public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
