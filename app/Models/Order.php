<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ShippingMethod;
use App\Models\ShippingAddress;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shipping_method_id',
        'customer_email',
        'currency',
        'shipping_total',      // en centimes
        'total_price',         // en centimes (total figé)
        'payment_status',      // unpaid|processing|paid|failed|pending_refund|refunded
        'payment_method',      // card|bancontact|apple_pay...
        'payment_provider',    // stripe
        'stripe_payment_intent_id',
        'stripe_checkout_session_id',
        'ordered_at',
        'paid_at',
        'uuid',                // si tu utilises un UUID public pour l’URL
    ];

    protected $casts = [
        'shipping_total' => 'decimal:2',
        'total_price'    => 'decimal:2',
        'ordered_at'     => 'datetime',
        'paid_at'        => 'datetime',
        'shipping_address_json' => 'array',
    ];

    // On expose un attribut virtuel "items" pour la vue Payment.vue
    protected $appends = ['items'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function shippingAddress()
    {
        return $this->hasOne(ShippingAddress::class);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    // Si tu tiens au belongsToMany:
    public function products()
    {
        // Assure-toi que ta table pivot s’appelle bien order_products
        return $this->belongsToMany(Product::class, 'order_products')
            ->using(OrderProduct::class)
            ->withPivot(['quantity', 'unit_price', 'name']) // adapte selon tes colonnes
            ->withTimestamps();
    }

    /**
     * Attribut virtuel "items" utilisé par la vue Payment.vue
     * Retourne: [{ id, name, quantity, unit_price }, ...]
     */
    public function getItemsAttribute()
    {
        // On se base sur orderProducts pour figer le nom/prix au moment de la commande
        return $this->orderProducts
            ->map(function (OrderProduct $op) {
                return [
                    'id'         => $op->id,
                    // Si ta table order_products stocke 'name', prends-le en priorité,
                    // sinon fallback sur le produit lié.
                    'name'       => $op->name ?? optional($op->product)->name ?? 'Produit',
                    'quantity'   => (int) $op->quantity,
                    // unit_price en centimes (déjà figé dans order_products)
                    'unit_price' => (int) $op->unit_price,
                ];
            })
            ->values();
    }

    /**
     * Si tu utilises un UUID dans les URLs (ex: /checkout/payment/{order}),
     * on résout l'Order par 'uuid' plutôt que par 'id'.
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

     protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->uuid)) {
                $order->uuid = Str::uuid();
            }
        });
    }
}
