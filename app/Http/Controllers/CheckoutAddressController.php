<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CheckoutAddressController extends Controller
{
    /**
     * Affiche la page de saisie ou modification de l'adresse de livraison
     */
    public function edit(Order $order)
    {
        // On bloque si la commande est déjà en traitement ou terminée
        abort_if(in_array($order->payment_status, ['processing','paid','failed','refunded'], true), 403);

        // Charger les relations utiles (produits, méthode, adresse + ville)
        $order->load(['shippingAddress.city', 'shippingMethod', 'orderProducts.product']);

        // Préremplir avec la dernière adresse utilisée si dispo (pour les clients connectés)
        $prefill = null;
        if (!auth()->guest() && !$order->shippingAddress) {
            $lastOrder = Order::with('shippingAddress.city')
                ->where('user_id', auth()->id())
                ->whereNotNull('ordered_at')
                ->whereHas('shippingAddress')
                ->latest('ordered_at')
                ->first();

            if ($lastOrder?->shippingAddress) {
                $a = $lastOrder->shippingAddress;
                $prefill = [
                    'full_name'      => $a->full_name,
                    'address_line_1' => $a->address_line_1,
                    'address_line_2' => $a->address_line_2,
                    'postal_code'    => $a->postal_code,
                    'city_id'        => $a->city_id,
                    'city_name'      => $a->city?->name,
                    'phone_number'   => $a->phone_number,
                ];
            }
        }

        $methodsRaw = ShippingMethod::orderBy('price')->get(['id','name','code','price','free_from']);

        // Calcul du sous-total en euros (affichage UI)
        $subtotal = $order->orderProducts->reduce(
            fn($sum, $op) => $sum + ((float)$op->price * (int)$op->quantity),
            0.0
        );

        // Préparer les méthodes avec le prix effectif (prise en compte du free_from)
        $methods = $methodsRaw->map(function ($m) use ($subtotal) {
            $m->effective_price = (is_null($m->free_from) || $subtotal < (float)$m->free_from)
                ? (float)$m->price
                : 0.0;
            return $m;
        });

        // Adresse déjà enregistrée ou préremplie
        $address = $order->shippingAddress
            ? [
                'full_name'      => $order->shippingAddress->full_name,
                'address_line_1' => $order->shippingAddress->address_line_1,
                'address_line_2' => $order->shippingAddress->address_line_2,
                'postal_code'    => $order->shippingAddress->postal_code,
                'city_id'        => $order->shippingAddress->city_id,
                'city_name'      => $order->shippingAddress->city?->name,
                'phone_number'   => $order->shippingAddress->phone_number,
              ]
            : $prefill;

        return Inertia::render('Checkout/Address', [
            'order' => [
                'id'                 => $order->id,
                'uuid'               => $order->uuid,
                'customer_email'     => $order->customer_email,
                'shipping_method_id' => $order->shipping_method_id,
                'shipping_total'     => $order->shipping_total, // en centimes
                'currency'           => $order->currency ?? 'EUR',
            ],
            'address'   => $address,
            'items'     => $order->orderProducts->map(fn($op) => [
                'id'   => $op->id,
                'name' => $op->product?->name ?? 'Produit supprimé',
                'qty'  => (int)$op->quantity,
                'unit' => (float)$op->price,
                'line' => (float)$op->price * (int)$op->quantity,
                'image'=> $op->product?->image,
            ]),
            'subtotal'  => $subtotal,     // euros pour l’UI
            'methods'   => $methods,      // prix calculés pour l’UI
            'guest'     => auth()->guest(),
        ]);
    }

    /**
     * Sauvegarde provisoire de l'adresse + méthode choisie (draft)
     */
    public function update(Request $request, Order $order)
    {
        abort_if(in_array($order->payment_status, ['processing','paid','failed','refunded'], true), 403);

        // Règles de validation (email obligatoire si invité)
        $rules = [
            'shipping_method_id' => ['required','exists:shipping_methods,id'],
            'full_name'          => ['required','string','max:255'],
            'address_line_1'     => ['required','string','max:255'],
            'address_line_2'     => ['nullable','string','max:255'],
            'postal_code'        => ['required','string','max:20'],
            'city_name'          => ['required','string','max:190'],
            'phone_number'       => ['required','string','max:30'],
        ];
        if (!Auth::check()) $rules['customer_email'] = ['required','email','max:255'];

        $data = $request->validate($rules);

        if (!Auth::check()) $order->customer_email = $data['customer_email'];

        // Ville par nom (créée si inexistante)
        $city = City::firstOrCreate(['name' => $data['city_name']]);

        // Création ou mise à jour de l'adresse liée à la commande
        $addr = $order->shippingAddress ?? new ShippingAddress(['order_id' => $order->id]);
        $addr->fill([
            'full_name'      => $data['full_name'],
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'postal_code'    => $data['postal_code'],
            'city_id'        => $city->id,
            'phone_number'   => $data['phone_number'],
        ])->save();

        // Calcul du sous-total en euros
        $order->load('orderProducts');
        $subtotal = $order->orderProducts->reduce(
            fn($sum, $op) => $sum + ((float)$op->price * (int)$op->quantity),
            0.0
        );

        // Calcul du prix de livraison en euros (free_from inclus)
        $method = ShippingMethod::findOrFail($data['shipping_method_id']);
        $shippingEuro = (float)$method->price;
        if ($method->free_from !== null && $subtotal >= (float)$method->free_from) {
            $shippingEuro = 0.0;
        }

        // Sauvegarde en centimes (draft)
        $order->shipping_method_id = $method->id;
        $order->shipping_total     = (int) round($shippingEuro * 100);
        $order->save();

        $request->session()->put('current_order_uuid', $order->uuid);

        return back()->with('success', 'Adresse et livraison enregistrées.');
    }

    /**
     * Finalise l'adresse et la méthode de livraison,
     * fige les montants et prépare la commande pour le paiement
     */
    public function finalizeAddressAndShipping(Request $request, Order $order)
    {
        abort_if(in_array($order->payment_status, ['processing','paid','failed','refunded'], true), 403);

        // Règles de validation (mêmes que update)
        $rules = [
            'shipping_method_id' => ['required','exists:shipping_methods,id'],
            'full_name'          => ['required','string','max:255'],
            'address_line_1'     => ['required','string','max:255'],
            'address_line_2'     => ['nullable','string','max:255'],
            'postal_code'        => ['required','string','max:20'],
            'city_name'          => ['required','string','max:190'],
            'phone_number'       => ['required','string','max:30'],
        ];
        if (!Auth::check()) $rules['customer_email'] = ['required','email','max:255'];

        $data = $request->validate($rules);

        if (!Auth::check()) $order->customer_email = $data['customer_email'];

        $city = City::firstOrCreate(['name' => $data['city_name']]);

        $addr = $order->shippingAddress ?? new ShippingAddress(['order_id' => $order->id]);
        $addr->fill([
            'full_name'      => $data['full_name'],
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'postal_code'    => $data['postal_code'],
            'city_id'        => $city->id,
            'phone_number'   => $data['phone_number'],
        ])->save();

        // Sous-total en centimes (plus précis que update)
        $order->load('orderProducts');
        $itemsSubtotalCents = $order->orderProducts->reduce(function ($sum, $op) {
            $unitCents = (int) round(((float)$op->price) * 100);
            return $sum + ($unitCents * (int) $op->quantity);
        }, 0);

        // Calcul du prix de livraison en centimes (free_from inclus)
        $method = ShippingMethod::findOrFail($data['shipping_method_id']);
        $shippingEuro = (float) $method->price;
        if ($method->free_from !== null && ($itemsSubtotalCents / 100) >= (float) $method->free_from) {
            $shippingEuro = 0.0;
        }
        $shippingCents = (int) round($shippingEuro * 100);

        // Calcul du total final en centimes
        $grandTotalCents = $itemsSubtotalCents + $shippingCents;

        // Sauvegarde finale de la commande (montants figés)
        $order->shipping_method_id = $method->id;
        $order->shipping_total     = $shippingCents;
        $order->total_price        = $grandTotalCents;
        $order->currency           = $order->currency ?: 'EUR';
        $order->payment_method     = 'stripe';
        $order->save();

        $request->session()->put('current_order_uuid', $order->uuid);

        return redirect()
            ->route('checkout.payment.show', $order)
            ->with('success', 'Adresse finalisée. Montants figés. Procédez au paiement.');
    }
}
