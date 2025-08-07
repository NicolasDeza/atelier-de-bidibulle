<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Review;
use App\Models\Wishlist;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    public function getRouteKeyName(): string
{
    return 'slug';
}

  public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function favoritedBy()
{
    return $this->belongsToMany(User::class, 'wishlists');
}

    public function orders()
    {
        return $this->belongsToMany(Order::class)->using(OrderProduct::class);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('images/produits/' . $this->image);
   }
protected $appends = ['image_url'];

protected $fillable = [
    'name',
    'slug',
    'description',
    'price',
    'discount_type',
    'discount_value',
    'stock',
    'image',
    'category_id',
];
}
