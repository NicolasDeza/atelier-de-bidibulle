<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

       public function add(Request $request)
    {
        $request->validate([
            'product_id'    => 'required|exists:products,id',
            'quantity'      => 'required|integer|min:1',
            'customization' => 'nullable|string|max:255',
        ]);

        // ✅ Cas utilisateur connecté
        if (auth()->check()) {
            $user = auth()->user();

            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['created_at' => now()]
            );

            $item = $cart->cartItems()->where('product_id', $request->product_id)->first();

            if ($item) {
                $item->quantity += $request->quantity;
                if ($request->customization) {
                    $item->customization = $request->customization;
                }
                $item->save();
            } else {
                $cart->cartItems()->create([
                    'product_id'    => $request->product_id,
                    'quantity'      => $request->quantity,
                    'customization' => $request->customization,
                ]);
            }

            return back()->with('success', 'Produit ajouté au panier.');
        }

        // utilisateur invité → stockage dans la SESSION
        $cart = session()->get('cart', []);
        $id = $request->product_id;

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $request->quantity;
            if ($request->customization) {
                $cart[$id]['customization'] = $request->customization;
            }
        } else {
            $product = Product::find($id);
            $cart[$id] = [
                'product_id'    => $product->id,
                'name'          => $product->name,
                'price'         => $product->price,
                'quantity'      => $request->quantity,
                'image'         => $product->image,
                'customization' => $request->customization,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Produit personnalisé ajouté au panier.');
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
    public function show(Cart $cart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $cart)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart)
    {
        //
    }
}
