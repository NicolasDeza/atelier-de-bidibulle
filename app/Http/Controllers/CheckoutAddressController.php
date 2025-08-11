<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use App\Models\Country;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


class CheckoutAddressController extends Controller
{
 public function edit(Order $order)
{
    if ($order->payment_status === 'paid') abort(403);

    $order->load(['shippingAddress.city.country', 'shippingMethod', 'orderProducts.product']);

    $countries = Country::orderBy('name')->get(['id','name','code']);
    $methods   = ShippingMethod::orderBy('price')->get(['id','name','code','price','free_from']); // ← free_from

    $subtotal = $order->orderProducts->reduce(
        fn($sum, $op) => $sum + ((float)$op->price * (int)$op->quantity),
        0.0
    );

    return Inertia::render('Checkout/Address', [
        'order' => [
            'id' => $order->id,
            'customer_email' => $order->customer_email,
            'shipping_method_id' => $order->shipping_method_id,
            'shipping_total' => $order->shipping_total,
            'currency' => $order->currency ?? 'EUR',
        ],
        'address' => optional($order->shippingAddress, fn($a) => [
            'full_name'      => $a->full_name,
            'address_line_1' => $a->address_line_1,
            'address_line_2' => $a->address_line_2,
            'postal_code'    => $a->postal_code,
            'country_id'     => $a->city?->country_id,
            'city_id'        => $a->city_id,
            'city_name'      => $a->city?->name,
            'phone_number'   => $a->phone_number,
        ]),
        'items'     => $order->orderProducts->map(fn($op) => [
            'id'   => $op->id,
            'name' => $op->product?->name ?? 'Produit supprimé',
            'qty'  => (int)$op->quantity,
            'unit' => (float)$op->price,
            'line' => (float)$op->price * (int)$op->quantity,
            'image'=> $op->product?->image,
        ]),
        'subtotal'  => $subtotal,
        'countries' => $countries,
        'methods'   => $methods,
        'guest'     => !\Auth::check(),
    ]);
}

public function update(Request $request, Order $order)
{
    $rules = [
        'shipping_method_id' => ['required','exists:shipping_methods,id'],
        'full_name'          => ['required','string','max:255'],
        'address_line_1'     => ['required','string','max:255'],
        'address_line_2'     => ['nullable','string','max:255'],
        'postal_code'        => ['required','string','max:20'],
        'country_id'         => ['required','exists:countries,id'],
        'city_name'          => ['required','string','max:190'],
        'phone_number'       => ['required','string','max:30'],
    ];
    if (!\Auth::check()) $rules['customer_email'] = ['required','email','max:255'];

    $data = $request->validate($rules);

    if (!\Auth::check()) $order->customer_email = $data['customer_email'];

    // ville
    $city = City::firstOrCreate(
        ['name' => $data['city_name'], 'country_id' => $data['country_id']]
    );

    // adresse
    $addr = $order->shippingAddress ?? new ShippingAddress(['order_id' => $order->id]);
    $addr->fill([
        'full_name'      => $data['full_name'],
        'address_line_1' => $data['address_line_1'],
        'address_line_2' => $data['address_line_2'] ?? null,
        'postal_code'    => $data['postal_code'],
        'city_id'        => $city->id,
        'phone_number'   => $data['phone_number'],
    ])->save();

    // calcul sous-total
    $order->load('orderProducts');
    $subtotal = $order->orderProducts->reduce(
        fn($sum, $op) => $sum + ((float)$op->price * (int)$op->quantity),
        0.0
    );

    // méthode + règle free_from
    $method = ShippingMethod::findOrFail($data['shipping_method_id']);
    $shipping = (float)$method->price;
    if ($method->free_from !== null && $subtotal >= (float)$method->free_from) {
        $shipping = 0.0;
    }

    // snapshot sur la commande
    $order->shipping_method_id = $method->id;
    $order->shipping_total     = $shipping;
    $order->save();

    return back()->with('success', 'Adresse et livraison enregistrées.');
}


     public function createOrderFromCart(Request $request)
    {
        // 1) Récupération du panier
        if (Auth::check()) {
            $cart = Cart::with('items.product')->where('user_id', Auth::id())->first();
            if (!$cart || $cart->items->isEmpty()) {
                return back()->with('error', 'Votre panier est vide.');
            }
        } else {
            // 📝 SI TU AS UN PANIER EN SESSION POUR INVITÉ :
            $sessionItems = session('cart.items', []); // [{product_id, quantity}, ...]
            if (empty($sessionItems)) return back()->with('error', 'Votre panier est vide.');

            // hydrate un “cart virtual” pour la suite
            $cart = (object)[
                'items' => collect($sessionItems)->map(function($it){
                    $p = Product::find($it['product_id']);
                    return (object)['product' => $p, 'quantity' => (int)$it['quantity']];
                }),
            ];
        }

        // 2) Vérifs & calcul du total
        $total = 0;
        foreach ($cart->items as $item) {
            if (!$item->product) return back()->with('error', 'Un produit du panier n’existe plus.');
            $line = (float)$item->product->price * (int)$item->quantity;
            $total += $line;
        }

        // 3) Création de la commande + items (snapshot)
        $order = DB::transaction(function() use ($cart, $total) {
            $order = new Order();
            if (Auth::check()) $order->user_id = Auth::id();
            $order->status = 'pending';
            $order->shipping_status = 'pending';
            $order->payment_status = 'unpaid';
            $order->payment_method = 'stripe'; // par défaut
            $order->currency = 'EUR';
            $order->total_price = $total;       // total produits TTC
            $order->shipping_total = 0;         // sera fixé sur la page adresse/livraison
            $order->save();

            foreach ($cart->items as $item) {
                OrderProduct::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product->id,
                    'quantity'   => (int)$item->quantity,
                    'price'      => $item->product->price, // snapshot du prix unitaire (decimal)
                    // (optionnel) ajoute des colonnes snapshot si tu les as: product_name, sku…
                ]);
            }

            return $order;
        });

        // 4) (optionnel) vider le panier
        if (Auth::check()) {
            // ex: $cart->items()->delete(); ou status -> 'checked_out'
        } else {
            // session()->forget('cart'); // si tu utilises la session pour invités
        }

        // 5) Redirection vers l’adresse/livraison
        return redirect()->route('checkout.address.edit', $order);
    }
}

