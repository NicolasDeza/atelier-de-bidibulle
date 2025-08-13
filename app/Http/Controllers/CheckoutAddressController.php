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
    public function edit(Order $order)
    {
        abort_if(in_array($order->payment_status, ['processing','paid','failed','refunded'], true), 403);

        // plus de country dans les relations
        $order->load(['shippingAddress.city', 'shippingMethod', 'orderProducts.product']);

        // Prefill simple (sans country)
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

        // sous-total (euros) à l’affichage
        $subtotal = $order->orderProducts->reduce(
            fn($sum, $op) => $sum + ((float)$op->price * (int)$op->quantity),
            0.0
        );

        // Méthodes avec prix effectif selon free_from (euros pour l’UI)
        $methods = $methodsRaw->map(function ($m) use ($subtotal) {
            $m->effective_price = (is_null($m->free_from) || $subtotal < (float)$m->free_from)
                ? (float)$m->price
                : 0.0;
            return $m;
        });

        // Adresse effective
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
                'shipping_total'     => $order->shipping_total, // cts
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
            'methods'   => $methods,      // euros pour l’UI
            'guest'     => auth()->guest(),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        abort_if(in_array($order->payment_status, ['processing','paid','failed','refunded'], true), 403);

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

        // Ville par nom uniquement
        $city = City::firstOrCreate(['name' => $data['city_name']]);

        // Adresse
        $addr = $order->shippingAddress ?? new ShippingAddress(['order_id' => $order->id]);
        $addr->fill([
            'full_name'      => $data['full_name'],
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'postal_code'    => $data['postal_code'],
            'city_id'        => $city->id,
            'phone_number'   => $data['phone_number'],
        ])->save();

        // Sous-total (euros) pour calcul free_from
        $order->load('orderProducts');
        $subtotal = $order->orderProducts->reduce(
            fn($sum, $op) => $sum + ((float)$op->price * (int)$op->quantity),
            0.0
        );

        // Méthode + règle free_from (euros) → snapshot brouillon
        $method = ShippingMethod::findOrFail($data['shipping_method_id']);
        $shippingEuro = (float)$method->price;
        if ($method->free_from !== null && $subtotal >= (float)$method->free_from) {
            $shippingEuro = 0.0;
        }

        // ⚠️ CONSERVER LES UNITÉS DU MODÈLE: shipping_total est en CENTIMES
        $order->shipping_method_id = $method->id;
        $order->shipping_total     = (int) round($shippingEuro * 100); // cts (draft)
        $order->save();

        $request->session()->put('current_order_uuid', $order->uuid);

        return back()->with('success', 'Adresse et livraison enregistrées.');
    }

    public function finalizeAddressAndShipping(Request $request, Order $order)
    {
        abort_if(in_array($order->payment_status, ['processing','paid','failed','refunded'], true), 403);

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

        // Sous-total en CENTIMES
        $order->load('orderProducts');
        $itemsSubtotalCents = $order->orderProducts->reduce(function ($sum, $op) {
            $unitCents = (int) round(((float)$op->price) * 100);
            return $sum + ($unitCents * (int) $op->quantity);
        }, 0);

        // Shipping (euros → cts) avec free_from
        $method = ShippingMethod::findOrFail($data['shipping_method_id']);
        $shippingEuro = (float) $method->price;
        if ($method->free_from !== null && ($itemsSubtotalCents / 100) >= (float) $method->free_from) {
            $shippingEuro = 0.0;
        }
        $shippingCents = (int) round($shippingEuro * 100);

        // Total figé (cts)
        $grandTotalCents = $itemsSubtotalCents + $shippingCents;

        $order->shipping_method_id = $method->id;
        $order->shipping_total     = $shippingCents;     // cts
        $order->total_price        = $grandTotalCents;   // cts (figé)
        $order->currency           = $order->currency ?: 'EUR';
        $order->payment_method     = 'stripe';
        $order->save();

        $request->session()->put('current_order_uuid', $order->uuid);

        return redirect()
            ->route('checkout.payment.show', $order)
            ->with('success', 'Adresse finalisée. Montants figés. Procédez au paiement.');
    }
}
