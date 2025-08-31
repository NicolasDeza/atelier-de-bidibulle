<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * Affiche le panier (utilisateur connecté ou invité via session)
     */
    public function index()
    {
        $cartItems = [];
        $total = 0.0;

        if (auth()->check()) {
            // Panier stocké en BDD (utilisateur connecté)
            $user = auth()->user();
            $cart = $user->cart;

            if ($cart) {
                $items = $cart->cartItems()
                    ->with('product')
                    ->get()
                    ->map(function ($item) {
                        $product = $item->product;
                        $stock   = (int) ($product->stock ?? 0);

                        return [
                            'id'            => $item->id,
                            'product_id'    => $product->id,
                            'name'          => $product->name,
                            'price'         => (float) $product->price,
                            'quantity'      => (int) $item->quantity,
                            'customization' => $item->customization,
                            'image_url'     => asset('images/produits/' . $product->image),
                            'subtotal'      => (float) $product->price * (int) $item->quantity,

                            // Vérif stock disponible
                            'stock'         => $stock,
                            'is_available'  => $stock >= (int) $item->quantity,
                            'max_quantity'  => $stock,
                        ];
                    });

                $total = (float) $items->sum('subtotal');
                $cartItems = $items->values()->all();
            }
        } else {
            // Panier en session (invité)
            $sessionCart = session()->get('cart', []);

            $items = collect($sessionCart)
                ->map(function ($item, $key) use (&$sessionCart) {
                    $product = Product::find($item['product_id'] ?? null);

                    if (!$product) {
                        unset($sessionCart[$key]);
                        return null;
                    }

                    $stock = (int) ($product->stock ?? 0);
                    $price = (float) $product->price;
                    $qty   = (int) ($item['quantity'] ?? 1);

                    // Ajuste la quantité si elle dépasse le stock réel
                    if ($stock >= 0 && $qty > $stock) {
                        $qty = $stock;
                        $sessionCart[$key]['quantity'] = $qty;
                    }

                    return [
                        'key'           => $key,
                        'product_id'    => $product->id,
                        'name'          => $product->name,
                        'price'         => $price,
                        'quantity'      => $qty,
                        'customization' => $item['customization'] ?? null,
                        'image_url'     => asset('images/produits/' . $product->image),
                        'subtotal'      => $price * $qty,

                        'stock'         => $stock,
                        'is_available'  => $stock >= $qty,
                        'max_quantity'  => $stock,
                    ];
                })
                ->filter();

            session()->put('cart', $sessionCart);

            $total = (float) $items->sum('subtotal');
            $cartItems = $items->values()->all();
        }

        return Inertia::render('Cart/Index', [
            'cartItems'       => $cartItems,
            'total'           => $total,
            'isAuthenticated' => auth()->check(),
        ]);
    }

    /**
     * Ajoute un produit au panier (BDD si connecté, session sinon)
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id'    => 'required|exists:products,id',
            'quantity'      => 'required|integer|min:1',
            'customization' => 'nullable|string|max:100',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Vérification du stock avant ajout
        if ($product->stock < $request->quantity) {
            return back()->withErrors(['message' => 'Stock insuffisant pour ce produit.']);
        }

        $customization = $request->filled('customization')
           ? trim(strip_tags($request->customization))
           : null;

        // Utilisateur connecté → stockage en BDD
        if (auth()->check()) {
            $cart = Cart::firstOrCreate(
                ['user_id' => auth()->id()],
                ['created_at' => now()]
            );

            $item = $cart->cartItems()
                ->where('product_id', $product->id)
                ->where('customization', $customization)
                ->first();

            if ($item) {
                // Incrémente quantité si l'article existe déjà
                $newQty = $item->quantity + $request->quantity;
                if ($newQty > $product->stock) {
                    return back()->withErrors(['message' => 'Stock insuffisant pour ajouter cette quantité.']);
                }
                $item->update(['quantity' => $newQty]);
            } else {
                // Sinon, ajoute un nouvel item
                $cart->cartItems()->create([
                    'product_id'    => $product->id,
                    'quantity'      => $request->quantity,
                    'customization' => $customization,
                ]);
            }

            return back()->with('success', 'Produit ajouté au panier.');
        }

        // Invité → stockage en session
        $cart = session()->get('cart', []);
        $key  = $product->id . '-' . md5($customization ?? '');

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

    /**
     * Met à jour la quantité d'un article (BDD)
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($cartItem->cart->user_id !== auth()->id()) {
            return back()->withErrors(['message' => 'Action non autorisée']);
        }

        // Vérif du stock au moment de la mise à jour
        $stock = (int) ($cartItem->product->stock ?? 0);
        if ($request->quantity > $stock) {
            return back()->withErrors(['message' => "Stock insuffisant (max {$stock})."]);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Quantité mise à jour');
    }

    /**
     * Supprime un article du panier (BDD)
     */
    public function destroy(CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== auth()->id()) {
            return back()->withErrors(['message' => 'Action non autorisée']);
        }

        $cartItem->delete();

        return back()->with('success', 'Produit retiré du panier');
    }

    /**
     * Met à jour la quantité d'un article (Session invité)
     */
    public function updateSession(Request $request, $key)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session('cart', []);
        if (!isset($cart[$key])) return back();

        // Vérif stock réel
        $product = Product::find($cart[$key]['product_id']);
        $stock   = (int) ($product->stock ?? 0);

        if ($request->quantity > $stock) {
            return back()->withErrors(['message' => "Stock insuffisant (max {$stock})."]);
        }

        $cart[$key]['quantity'] = $request->quantity;
        session(['cart' => $cart]);

        return back()->with('success', 'Quantité mise à jour');
    }

    /**
     * Supprime un article du panier (Session invité)
     */
    public function removeSession($key)
    {
        $cart = session('cart', []);

        if (isset($cart[$key])) {
            unset($cart[$key]);
            session(['cart' => $cart]);
        }

        return back()->with('success', 'Produit retiré du panier');
    }

    /**
     * Vide complètement le panier (BDD ou Session)
     */
    public function clear()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->cart) {
                $user->cart->cartItems()->delete();
            }
        } else {
            // Invité → suppression directe de la session
            session()->forget('cart');
        }

        return back()->with('success', 'Panier vidé');
    }
}
