<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Illuminate\Support\Facades\Schema; // ✅ pour tester la présence de colonnes

class CheckoutPaymentController extends Controller
{
    public function show(Request $request, Order $order)
    {
        abort_if(in_array($order->payment_status, ['paid','failed','refunded'], true), 403);

        if ($redirect = $this->guardPaymentAccess($request, $order)) {
            return $redirect;
        }

        if ($order->payment_status === 'unpaid') {
            return $this->createSession($request, $order);
        }

        return Inertia::render('Checkout/Payment', [
            'order' => [
                'uuid'     => $order->uuid,
                'currency' => $order->currency,
                'items'    => $order->items,
            ],
        ]);
    }

    public function startAndRedirect(Request $request, Order $order)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $order->load(['orderProducts.product']);
        $toCents = fn (float $eur) => (int) round($eur * 100);

        $lineItems = [];
        $itemsSubtotalCents = 0;

        foreach ($order->orderProducts as $op) {
            $name = optional($op->product)->name ?? 'Produit';
            $unitCents = $toCents((float) $op->price);
            $qty = (int) ($op->quantity ?: 1);
            if ($unitCents <= 0 || $qty <= 0) continue;

            $itemsSubtotalCents += $unitCents * $qty;

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => ['name' => $name],
                    'unit_amount'  => $unitCents,
                ],
                'quantity' => $qty,
            ];
        }

        if (empty($lineItems)) {
            return redirect()->route('cart.index')->with('error', 'Aucun produit valide.');
        }

        $bpostCents = $itemsSubtotalCents >= 5000 ? 0 : 590;

        $session = \Stripe\Checkout\Session::create(
            [
                'mode'       => 'payment',
                'locale'     => 'fr',
                'line_items' => $lineItems,
                'shipping_address_collection' => ['allowed_countries' => ['FR','BE']],
                'phone_number_collection'     => ['enabled' => true],
                'shipping_options' => [
                    [
                        'shipping_rate_data' => [
                            'type'         => 'fixed_amount',
                            'fixed_amount' => ['amount' => 0, 'currency' => 'eur'],
                            'display_name' => 'Remise en main propre',
                        ],
                    ],
                    [
                        'shipping_rate_data' => [
                            'type'         => 'fixed_amount',
                            'fixed_amount' => ['amount' => $bpostCents, 'currency' => 'eur'],
                            'display_name' => 'Bpost (2–4 jours)',
                            'delivery_estimate' => [
                                'minimum' => ['unit' => 'business_day', 'value' => 2],
                                'maximum' => ['unit' => 'business_day', 'value' => 4],
                            ],
                        ],
                    ],
                ],
                'customer_email' => $order->customer_email,
                'success_url'    => route('checkout.payment.return', $order).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'     => route('cart.index'),
                'metadata'       => [
                    'order_id'   => (string) $order->id,
                    'order_uuid' => (string) $order->uuid,
                ],
            ],
            ['idempotency_key' => 'order-'.$order->uuid.'-create-session']
        );

        $order->stripe_checkout_session_id = $session->id;
        $order->payment_status             = 'processing';
        $order->save();

        $url = $session->url;
        return $request->header('X-Inertia') ? \Inertia\Inertia::location($url) : redirect()->away($url);
    }

    public function createSession(Request $request, Order $order)
    {
        if ($redirect = $this->guardPaymentAccess($request, $order)) {
            return $redirect;
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $order->load(['orderProducts.product']);

        $toCents = fn (float $eur) => (int) round($eur * 100);

        $lineItems = [];
        $itemsSubtotalCents = 0;

        foreach ($order->orderProducts as $op) {
            $name = optional($op->product)->name ?? 'Produit';
            $unitCents = $toCents((float) $op->price);
            $qty = (int) ($op->quantity ?: 1);
            if ($unitCents <= 0 || $qty <= 0) continue;

            $itemsSubtotalCents += $unitCents * $qty;

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => ['name' => $name],
                    'unit_amount'  => $unitCents,
                ],
                'quantity' => $qty,
            ];
        }

        if (empty($lineItems)) {
            return redirect()->route('cart.index')->with('error', 'Aucun produit valide.');
        }

        $bpostCents = $itemsSubtotalCents >= 5000 ? 0 : 590;

        try {
            $session = StripeSession::create(
                [
                    'mode'       => 'payment',
                    'locale'     => 'fr',
                    'line_items' => $lineItems,
                    'shipping_address_collection' => ['allowed_countries' => ['FR','BE']],
                    'phone_number_collection'     => ['enabled' => true],
                    'shipping_options' => [
                        [
                            'shipping_rate_data' => [
                                'type'         => 'fixed_amount',
                                'fixed_amount' => ['amount' => 0, 'currency' => 'eur'],
                                'display_name' => 'Remise en main propre',
                            ],
                        ],
                        [
                            'shipping_rate_data' => [
                                'type'         => 'fixed_amount',
                                'fixed_amount' => ['amount' => $bpostCents, 'currency' => 'eur'],
                                'display_name' => 'Bpost (2–4 jours)',
                                'delivery_estimate' => [
                                    'minimum' => ['unit' => 'business_day', 'value' => 2],
                                    'maximum' => ['unit' => 'business_day', 'value' => 4],
                                ],
                            ],
                        ],
                    ],
                    'customer_email' => $order->customer_email,
                    'success_url'    => route('checkout.payment.return', $order).'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'     => route('cart.index'),
                    'metadata'       => [
                        'order_id'   => (string) $order->id,
                        'order_uuid' => (string) $order->uuid,
                    ],
                ],
                ['idempotency_key' => 'order-'.$order->uuid.'-create-session']
            );

            $order->stripe_checkout_session_id = $session->id;
            $order->payment_status             = 'processing';
            $order->save();

            $url = $session->url;
            return $request->header('X-Inertia') ? Inertia::location($url) : redirect()->away($url);

        } catch (\Exception $e) {
            \Log::error('Stripe session creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Erreur lors de la création du paiement.');
        }
    }

    public function return(Request $request, Order $order)
    {
        if ($redirect = $this->guardPaymentAccess($request, $order)) {
            return $redirect;
        }

        $shipping = null;
        $address  = null;

        $sessionId = $request->query('session_id');
        if ($sessionId) {
            Stripe::setApiKey(config('services.stripe.secret'));

            try {
                $session = StripeSession::retrieve([
                    'id'     => $sessionId,
                    'expand' => ['shipping_cost.shipping_rate', 'shipping_details', 'customer_details'],
                ]);

                // Mode de livraison
                $shipping = [
                    'label'        => optional($session->shipping_cost->shipping_rate)->display_name,
                    'amount_total' => $session->shipping_cost->amount_total ?? 0, // cts
                ];

                // Adresse de livraison
                if ($session->shipping_details && $session->shipping_details->address) {
                    $address = [
                        'name'        => $session->shipping_details->name,
                        'line1'       => $session->shipping_details->address->line1,
                        'line2'       => $session->shipping_details->address->line2,
                        'postal_code' => $session->shipping_details->address->postal_code,
                        'city'        => $session->shipping_details->address->city,
                        'country'     => $session->shipping_details->address->country,
                        'phone'       => $session->shipping_details->phone,
                    ];
                }

                // ✅ petites améliorations « sans casser »
                if (empty($order->customer_email) && !empty($session->customer_details?->email)) {
                    $order->customer_email = $session->customer_details->email;
                }

                if ($order->payment_status !== 'paid') {
                    $order->payment_status           = 'paid';
                    $order->paid_at                  = now();
                    $order->ordered_at               = $order->ordered_at ?: now(); // si pas encore posé
                    $order->stripe_payment_intent_id = $session->payment_intent ?? $order->stripe_payment_intent_id;
                    // Montants en € (BDD decimal(8,2))
                    if (isset($shipping['amount_total'])) {
                        $order->shipping_total = $shipping['amount_total'] / 100;
                    }
                    if (isset($session->amount_total)) {
                        $order->total_price = $session->amount_total / 100;
                    }

                    // 🔖 Sauvegarde optionnelle (ne plante pas si colonnes absentes)
                    if (Schema::hasColumn('orders', 'shipping_method_label') && !empty($shipping['label'])) {
                        $order->shipping_method_label = $shipping['label'];
                    }
                    if (Schema::hasColumn('orders', 'shipping_address_json') && $session->shipping_details) {
                        $order->shipping_address_json = json_encode($session->shipping_details);
                    }

                    $order->save();
                }
            } catch (\Throwable $e) {
                \Log::warning('Stripe return: unable to retrieve session', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        } else {
            if ($order->payment_status !== 'paid') {
                $order->payment_status = 'paid';
                $order->paid_at        = now();
                $order->ordered_at     = $order->ordered_at ?: now();
                $order->save();
            }
        }

        $this->clearCartSession();
        $this->clearDbCart($order->user_id, $order->cart_token ?? $request->session()->get('cart_token'));

        return Inertia::render('Checkout/Success', [
            'order'    => [
                'id'          => $order->id,
                'uuid'        => $order->uuid,
                'total_price' => (float) $order->total_price,
                'currency'    => $order->currency,
                'paid_at'     => optional($order->paid_at)->toDateTimeString(),
            ],
            'shipping' => $shipping,
            'address'  => $address,
        ]);
    }

    private function guardPaymentAccess(Request $request, Order $order): ?RedirectResponse
    {
        if (auth()->check() && $order->user_id && $order->user_id !== auth()->id()) {
            return redirect()->route('cart.index')->with('error', 'Accès refusé.');
        }

        $sessionOrderUuid = $request->session()->get('current_order_uuid');
        if (!auth()->check() && (!$sessionOrderUuid || $sessionOrderUuid !== $order->uuid)) {
            return redirect()->route('cart.index')->with('error', 'Commande introuvable.');
        }

        if (!in_array($order->payment_status, ['unpaid', 'processing'], true)) {
            return redirect()->route('cart.index')->with('error', 'Commande non payable.');
        }

        return null;
    }

    private function clearCartSession(): void
    {
        session()->forget(['cart', 'cart_token', 'current_order_uuid']);
    }

    private function clearDbCart(?int $userId, ?string $cartToken): void
    {
        // \App\Models\CartItem::when($userId, fn($q) => $q->where('user_id', $userId))
        //     ->when($cartToken, fn($q) => $q->orWhere('session_token', $cartToken))
        //     ->delete();
    }
}
