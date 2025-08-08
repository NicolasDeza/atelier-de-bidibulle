<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            $user = auth()->user();
            $cart = $user->cart;

            if (!$cart) {
                $cartItems = collect();
                $total = 0;
            } else {
                $cartItems = $cart->cartItems()->with('product')->get()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'quantity' => $item->quantity,
                        'customization' => $item->customization,
                        'image_url' => asset('images/produits/' . $item->product->image),
                        'subtotal' => $item->product->price * $item->quantity,
                    ];
                });

                $total = $cartItems->sum('subtotal');
            }
        } else {
            $sessionCart = session()->get('cart', []);

            $cartItems = collect($sessionCart)->map(function ($item, $key) {
                return [
                    'key' => $key,
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'customization' => $item['customization'] ?? null,
                    'image_url' => asset('images/produits/' . $item['image']),
                    'subtotal' => $item['price'] * $item['quantity'],
                ];
            });

            $total = $cartItems->sum('subtotal');
        }

        return Inertia::render('Cart/Index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'isAuthenticated' => auth()->check(),
        ]);
    }

    public function add(Request $request)
{
    $request->validate([
        'product_id'    => 'required|exists:products,id',
        'quantity'      => 'required|integer|min:1',
        'customization' => 'nullable|string|max:255',
    ]);

    $product = Product::findOrFail($request->product_id);

    // Vérification du stock
    if ($product->stock < $request->quantity) {
        return back()->withErrors(['message' => 'Stock insuffisant pour ce produit.']);
    }

    $customization = $request->customization ?? null;

    //  Panier utilisateur connecté
    if (auth()->check()) {
        $user = auth()->user();

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['created_at' => now()]
        );

        $item = $cart->cartItems()
            ->where('product_id', $product->id)
            ->where('customization', $customization)
            ->first();

        if ($item) {
            $newQty = $item->quantity + $request->quantity;

            if ($newQty > $product->stock) {
                return back()->withErrors(['message' => 'Stock insuffisant pour ajouter cette quantité.']);
            }

            $item->quantity = $newQty;
            $item->save();
        } else {
            $cart->cartItems()->create([
                'product_id'    => $product->id,
                'quantity'      => $request->quantity,
                'customization' => $customization,
            ]);
        }

        return back()->with('success', 'Produit ajouté au panier.');
    }

    // Panier utilisateur invité -> panier en session
    $cart = session()->get('cart', []);

    $key = $product->id . '-' . md5($customization ?? '');

    if (isset($cart[$key])) {
        $newQty = $cart[$key]['quantity'] + $request->quantity;

        if ($newQty > $product->stock) {
            return back()->withErrors(['message' => 'Stock insuffisant pour ajouter cette quantité.']);
        }

        $cart[$key]['quantity'] = $newQty;
    } else {
        $cart[$key] = [
            'product_id'    => $product->id,
            'name'          => $product->name,
            'price'         => $product->price,
            'quantity'      => $request->quantity,
            'image'         => $product->image,
            'customization' => $customization,
        ];
    }

    session()->put('cart', $cart);

    return back()->with('success', 'Produit ajouté au panier.');
}


    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($cartItem->cart->user_id !== auth()->id()) {
            return back()->withErrors(['message' => 'Action non autorisée']);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Quantité mise à jour');
    }

    public function destroy(CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== auth()->id()) {
            return back()->withErrors(['message' => 'Action non autorisée']);
        }

        $cartItem->delete();

        return back()->with('success', 'Produit retiré du panier');
    }

    public function updateSession(Request $request, $key)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session('cart', []);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = $request->quantity;
            session(['cart' => $cart]);
        }

        return back()->with('success', 'Quantité mise à jour');
    }

    public function removeSession($key)
    {
        $cart = session('cart', []);

        if (isset($cart[$key])) {
            unset($cart[$key]);
            session(['cart' => $cart]);
        }

        return back()->with('success', 'Produit retiré du panier');
    }

    public function clear()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->cart) {
                $user->cart->cartItems()->delete();
            }
        } else {
            session()->forget('cart');
        }

        return back()->with('success', 'Panier vidé');
    }
}
