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
        // 1) Récupérer le panier
        if (Auth::check()) {
            // Panier utilisateur en BDD
            $cart = Cart::with('cartItems.product')
                ->where('user_id', Auth::id())
                ->first();

            if (!$cart || $cart->cartItems->isEmpty()) {
                return back()->with('error', 'Votre panier est vide.');
            }

            $items = $cart->cartItems; // collection d’items avec ->product
        } else {
            // Panier invité en session: [{product_id, quantity}]
            $sessionItems = (array) session('cart.items', []);
            if (empty($sessionItems)) {
                return back()->with('error', 'Votre panier est vide.');
            }

            // On hydrate des objets simples pour uniformiser la suite
            $items = collect($sessionItems)->map(function ($it) {
                return (object)[
                    'product'  => Product::find($it['product_id']),
                    'quantity' => (int) $it['quantity'],
                ];
            });
        }

        // 2) Calcul du total produits (TTC)
        $total = 0;
        foreach ($items as $item) {
            if (!$item->product) {
                return back()->with('error', 'Un produit du panier n’existe plus.');
            }
            $total += (float) $item->product->price * (int) $item->quantity;
        }

        // 3) Création de la commande + lignes (snapshot) en transaction
        $order = DB::transaction(function () use ($items, $total) {
            $order = new Order();
            if (Auth::check()) {
                $order->user_id = Auth::id();
            }
            $order->status          = 'pending';
            $order->shipping_status = 'pending';
            $order->payment_status  = 'unpaid';
            $order->payment_method  = 'stripe';
            $order->currency        = 'EUR';
            $order->total_price     = $total; // total produits TTC
            $order->shipping_total  = 0;      // fixé ensuite sur la page adresse/livraison
            $order->ordered_at = now();
            $order->save();

            foreach ($items as $item) {
                OrderProduct::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product->id,
                    'quantity'   => (int) $item->quantity,
                    'price'      => $item->product->price, // snapshot du PU (decimal)
                ]);
            }

            return $order;
        });

        // 4) (Optionnel) vider le panier
        if (Auth::check()) {
            // ex: $cart->cartItems()->delete(); // si tu veux vider
        } else {
            // session()->forget('cart'); // si tu veux vider le panier invité
        }

        // 5) Redirection vers Adresse/Livraison
        return redirect()->route('checkout.address.edit', $order);
    }
}
