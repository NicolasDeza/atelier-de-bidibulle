<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'old_price',
        'stock',
        'image',
        'category_id',
        'discount_type',   // 'fixed' | 'percent' | null (si tu l'utilises)
        'discount_value',  // numeric | null
    ];

    /**
     * Casts (ajuste selon ton schéma).
     */
    protected $casts = [
        'price'          => 'decimal:2',
        'old_price'      => 'decimal:2',
        'stock'          => 'integer',
        'discount_value' => 'decimal:2',
    ];

    /**
     * Ajouter automatiquement l'URL d'image aux réponses JSON/Inertia.
     */
    protected $appends = ['image_url'];

    /**
     * Relations
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->latest();
    }

    // Si tu as une relation "favorites" utilisateur <-> produits
    // public function favoritedBy()
    // {
    //     return $this->belongsToMany(User::class, 'favorites', 'product_id', 'user_id')
    //                 ->withTimestamps();
    // }

    /**
     * Accessor : URL complète vers l'image du produit.
     * On pointe vers public/images/produits (retour "comme avant").
     * Met un fallback si aucune image n'est définie.
     */
    public function getImageUrlAttribute(): string
    {
        if (!empty($this->image)) {
            $path = public_path('images/produits/'.$this->image);
            if (is_file($path)) {
                return asset('images/produits/'.$this->image);
            }
        }

        // Fallback (mets ton image par défaut si besoin)
        return asset('images/default.jpg');
    }

    /**
     * Optionnel : utiliser le slug dans les routes publiques
     * (ex: /products/{product:slug})
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
