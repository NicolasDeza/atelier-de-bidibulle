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

        // 1) Récupération des items du panier (user DB ou session invité)
        if ($userId) {
            $cart = Cart::with('cartItems.product')
                ->where('user_id', $userId)
                ->first();

            if (!$cart || $cart->cartItems->isEmpty()) {
                return back()->with('error', 'Votre panier est vide.');
            }

            $items = $cart->cartItems->map(function ($ci) {
                return (object)[
                    'product'       => $ci->product,
                    'quantity'      => (int) $ci->quantity,
                    'customization' => $ci->customization, // 🔥 REMETTRE LA PERSONNALISATION
                ];
            });
        } else {
            // Invité : lire depuis la session (tolérant à plusieurs formats)
            $raw = session('cart.items', session('cart', []));
            $raw = is_array($raw) ? $raw : (array) $raw;

            $normalized = collect($raw)->flatMap(function ($value, $key) {
                if (is_array($value) || is_object($value)) {
                    $a = (array) $value;
                    $pid = $a['product_id'] ?? $a['id'] ?? $a['productId'] ?? null;
                    $qty = $a['quantity'] ?? $a['qty'] ?? 1;
                    $customization = $a['customization'] ?? null; // 🔥 REMETTRE LA PERSONNALISATION
                    return $pid ? [[
                        'product_id' => (int) $pid,
                        'quantity' => (int) $qty,
                        'customization' => $customization
                    ]] : [];
                }
                if (!is_numeric($key)) {
                    return [[ 'product_id' => (int) $key, 'quantity' => (int) $value, 'customization' => null ]];
                }
                return [];
            })->filter(fn($it) => $it['product_id'] > 0 && $it['quantity'] > 0)
              ->values();

            if ($normalized->isEmpty()) {
                return back()->with('error', 'Votre panier est vide (invité).');
            }

            $items = $normalized->map(function ($it) {
                return (object)[
                    'product'       => Product::find($it['product_id']),
                    'quantity'      => (int) $it['quantity'],
                    'customization' => $it['customization'], // 🔥 REMETTRE LA PERSONNALISATION
                ];
            });
        }

        // 2) Vérifs produits existants
        foreach ($items as $item) {
            if (!$item->product) {
                return back()->with('error', 'Un produit du panier n’existe plus.');
            }
        }

        // 3) Création de la commande + snapshots produits
        $order = DB::transaction(function () use ($items, $userId) {
            $order = new Order();
            if ($userId) {
                $order->user_id = $userId;
                $order->customer_email = optional(Auth::user())->email;
            }

            $order->status           = 'pending';
            $order->shipping_status  = 'pending';
            $order->payment_status   = 'unpaid';
            $order->payment_method   = 'stripe';
            $order->payment_provider = 'stripe';
            $order->currency         = 'EUR';
            // Ces champs sont en CENTIMES (casts int) → on laisse à 0, Stripe/webhook fixeront le final
            $order->total_price      = 0;
            $order->shipping_total   = 0;
            // $order->ordered_at       = null;
            $order->ordered_at = now();
            $order->save();

            foreach ($items as $item) {
                OrderProduct::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item->product->id,
                    'quantity'      => (int) $item->quantity,
                    'price'         => $item->product->price,
                    'customization' => $item->customization, // 🔥 REMETTRE LA PERSONNALISATION
                ]);
            }

            return $order;
        });

        // 4) Mémoriser l’UUID pour autoriser l’invité à accéder au paiement
        $request->session()->put('current_order_uuid', $order->uuid);

        // 5) Démarrer le paiement (la méthode show créera la session Stripe si unpaid)
        return redirect()->route('checkout.payment.show', $order);

        // Alternative si tu préfères la route "start":
        // return redirect()->route('checkout.payment.start', $order);
    }
}
