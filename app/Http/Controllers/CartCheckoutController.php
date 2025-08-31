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
    /**
     * Crée une commande à partir du panier (BDD si connecté, session si invité)
     */
    public function createOrderFromCart(Request $request)
    {
        $userId = Auth::id();

        // 1) Récupération des items du panier (utilisateur connecté → BDD, invité → session)
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
                    'customization' => $ci->customization,
                ];
            });
        } else {
            // Invité : récupération depuis la session (tolérant à plusieurs formats)
            $raw = session('cart.items', session('cart', []));
            $raw = is_array($raw) ? $raw : (array) $raw;

            $normalized = collect($raw)->flatMap(function ($value, $key) {
                if (is_array($value) || is_object($value)) {
                    $a = (array) $value;
                    $pid = $a['product_id'] ?? $a['id'] ?? $a['productId'] ?? null;
                    $qty = $a['quantity'] ?? $a['qty'] ?? 1;
                    $customization = $a['customization'] ?? null;
                    return $pid ? [[
                        'product_id'    => (int) $pid,
                        'quantity'      => (int) $qty,
                        'customization' => $customization,
                    ]] : [];
                }
                if (!is_numeric($key)) {
                    return [[
                        'product_id'    => (int) $key,
                        'quantity'      => (int) $value,
                        'customization' => null,
                    ]];
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
                    'customization' => $it['customization'],
                ];
            });
        }

        // 2) Vérifier que tous les produits existent encore
        foreach ($items as $item) {
            if (!$item->product) {
                return back()->with('error', 'Un produit du panier n’existe plus.');
            }
        }

        // 3) Création de la commande + enregistrement des produits liés
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
            // Les montants sont en centimes → mis à 0, mis à jour plus tard par Stripe/webhook
            $order->total_price      = 0;
            $order->shipping_total   = 0;
            $order->ordered_at       = now();
            $order->save();

            // Sauvegarde de chaque produit du panier dans la commande
            foreach ($items as $item) {
                OrderProduct::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item->product->id,
                    'quantity'      => (int) $item->quantity,
                    'price'         => $item->product->price,
                    'customization' => $item->customization,
                ]);
            }

            return $order;
        });

        // 4) Sauvegarder l’UUID dans la session (permet aux invités d’accéder au paiement)
        $request->session()->put('current_order_uuid', $order->uuid);

        // 5) Redirection vers la page de paiement (Stripe)
        return redirect()->route('checkout.payment.show', $order);

        // Alternative si besoin : return redirect()->route('checkout.payment.start', $order);
    }
}
