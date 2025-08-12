<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartCheckoutController extends Controller
{
    public function createOrderFromCart(Request $request)
{
    $userId = Auth::id();

    // 1) Récupérer les items
    if ($userId) {
        // Panier utilisateur en BDD
        $cart = Cart::with('cartItems.product')
            ->where('user_id', $userId)
            ->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return back()->with('error', 'Votre panier est vide.');
        }

        $items = $cart->cartItems->map(function ($ci) {
            return (object)[
                'product'  => $ci->product,
                'quantity' => (int) $ci->quantity,
            ];
        });
    } else {
        // ----- Invité : lire le panier depuis la session (tolérant) -----
        // essaie d'abord 'cart.items', sinon 'cart', sinon tableau vide
        $raw = session('cart.items');
        if ($raw === null) $raw = session('cart', []);
        $raw = is_array($raw) ? $raw : (array) $raw;

        // normaliser -> [{product_id, quantity}]
        $normalized = collect($raw)->flatMap(function ($value, $key) {
            // cas liste d'objets/arrays
            if (is_array($value) || is_object($value)) {
                $a   = (array) $value;
                $pid = $a['product_id'] ?? $a['id'] ?? $a['productId'] ?? null;
                $qty = $a['quantity']  ?? $a['qty'] ?? 1;
                return $pid ? [[ 'product_id' => (int) $pid, 'quantity' => (int) $qty ]] : [];
            }
            // cas map { productId: qty }
            if (!is_numeric($key)) {
                return [[ 'product_id' => (int) $key, 'quantity' => (int) $value ]];
            }
            return [];
        })->filter(fn($it) => $it['product_id'] > 0 && $it['quantity'] > 0)
          ->values();

        if ($normalized->isEmpty()) {
            return back()->with('error', 'Votre panier est vide (invité).');
        }

        // hydrate objets uniformes
        $items = $normalized->map(function ($it) {
            return (object)[
                'product'  => Product::find($it['product_id']),
                'quantity' => (int) $it['quantity'],
            ];
        });
    }

    // 2) Calcul du total & vérifs produits
    $total = 0.0;
    foreach ($items as $item) {
        if (!$item->product) {
            return back()->with('error', 'Un produit du panier n’existe plus.');
        }
        $total += (float) $item->product->price * max(1, (int) $item->quantity);
    }

    // 3) Création Order + lignes en transaction
    $order = DB::transaction(function () use ($items, $total, $userId) {
        $order = new Order();
        if ($userId) $order->user_id = $userId;
        $order->status          = 'pending';
        $order->shipping_status = 'pending';
        $order->payment_status  = 'unpaid';
        $order->payment_method  = 'stripe';
        $order->currency        = 'EUR';
        $order->total_price     = $total; // total produits TTC
        $order->shipping_total  = 0;      // fixé sur la page adresse/livraison
        $order->ordered_at      = now();
        $order->save();

        foreach ($items as $item) {
            OrderProduct::create([
                'order_id'   => $order->id,
                'product_id' => $item->product->id,
                'quantity'   => (int) $item->quantity,
                'price'      => $item->product->price, // snapshot PU
            ]);
        }

        return $order;
    });

    // 4) (Optionnel) vider le panier
    if ($userId) {
        // $cart->cartItems()->delete(); // si tu veux vider côté BDD
    } else {
        // Unifie la clé pour la suite si tu veux garder : session(['cart.items' => []]);
        // ou vider complètement :
        // session()->forget('cart'); session()->forget('cart.items');
    }

    // 5) Redirection vers Adresse/Livraison
    return redirect()->route('checkout.address.edit', $order);

   }
}
