<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WishlistController extends Controller
{
    public function toggle($productId)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $product = Product::findOrFail($productId);

        if ($user->favorites()->where('product_id', $product->id)->exists()) {
            $user->favorites()->detach($product->id);
            $status = 'removed';
        } else {
            $user->favorites()->attach($product->id);
            $status = 'added';
        }


        return back()->with([
            'favoriteToggled' => [
                'status' => $status,
                'product_id' => $product->id
            ]
        ]);
    }
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    $user = auth()->user();

    // Récupérer tous les produits favoris
    $favorites = $user->favorites()
        ->with('category')
        ->get()
        ->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'old_price' => $product->old_price,
                'image_url' => asset('images/produits/' . $product->image),
            ];
        });

    return Inertia::render('Wishlist/Index', [
        'favorites' => $favorites,
    ]);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Wishlist $wishlist)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Wishlist $wishlist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Wishlist $wishlist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wishlist $wishlist)
    {
        //
    }
}
