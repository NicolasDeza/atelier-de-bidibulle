<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CheckoutPaymentController extends Controller
{
    /**
     * Démarre la session Stripe et redirige
     */
    public function startAndRedirect(Request $request, Order $order)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $order->load(['orderProducts.product']);
        $toCents = fn (float $eur) => (int) round($eur * 100);

        $lineItems = [];
        $itemsSubtotalCents = 0;

        foreach ($order->orderProducts as $op) {
            $name = optional($op->product)->name ?? 'Produit';

            // 🔥 AJOUTER LA PERSONNALISATION AU NOM DU PRODUIT
            if ($op->customization) {
                $name .= ' - Personnalisation: ' . $op->customization;
            }

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
                // ✅ CORRECTION : Utiliser checkout.payment.return
                'success_url'    => route('checkout.payment.return', $order) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'     => route('cart.index'),
                'metadata'       => [
                    'order_id'   => (string) $order->id,
                    'order_uuid' => (string) $order->uuid,
                    // 🔥 AJOUTER LES PERSONNALISATIONS EN MÉTADONNÉES
                    'customizations' => json_encode($order->orderProducts->pluck('customization', 'product_id')->filter()),
                ],
            ],
            ['idempotency_key' => 'order-'.$order->uuid.'-create-session']
        );

        $order->stripe_checkout_session_id = $session->id;
        $order->payment_status             = 'processing';
        $order->save();

        return $request->header('X-Inertia')
            ? Inertia::location($session->url)
            : redirect()->away($session->url);
    }

    /**
     * Page de retour après paiement Stripe
     */
    public function return(Request $request, Order $order)
    {
        \Log::info('[Checkout] Page retour appelée', [
            'order_id' => $order->id,
            'query' => $request->all(),
        ]);

        $sessionId = $request->get('session_id');
        if (!$sessionId) {
            // Pas de session_id, on affiche quand même la page succès
            \Log::warning('[Checkout] Pas de session_id');
        }

        $shipping = null;
        $address = null;

        if ($sessionId) {
            Stripe::setApiKey(config('services.stripe.secret'));

            try {
                $session = StripeSession::retrieve([
                    'id' => $sessionId,
                    'expand' => ['shipping_cost.shipping_rate'], // 🔥 RETIRER shipping_details
                ]);

                // Mode de livraison
                $shipping = [
                    'label' => $session->shipping_cost->shipping_rate->display_name ?? '—',
                    'amount_total' => $session->shipping_cost->amount_total ?? 0,
                ];

                // Adresse - accès direct sans expand
                $address = null;
                if (isset($session->shipping_details->address)) {
                    $address = [
                        'name'        => $session->shipping_details->name ?? '',
                        'line1'       => $session->shipping_details->address->line1 ?? '',
                        'line2'       => $session->shipping_details->address->line2 ?? '',
                        'postal_code' => $session->shipping_details->address->postal_code ?? '',
                        'city'        => $session->shipping_details->address->city ?? '',
                        'country'     => $session->shipping_details->address->country ?? '',
                    ];
                }
            } catch (\Throwable $e) {
                \Log::error('[Checkout] Erreur récupération session Stripe', [
                    'err' => $e->getMessage(),
                ]);
            }
        }

        // Marquer comme payé (backup si webhook pas encore passé)
        if ($order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
            $order->paid_at        = $order->paid_at ?: now();
            $order->ordered_at     = $order->ordered_at ?: now();
            $order->save();
        }

        // Vider le panier
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

    /**
     * Page succès après paiement Stripe
     */
    public function success(Request $request, $orderUuid)
{
    \Log::info('[Checkout] Page succès appelée', [
        'order_uuid' => $orderUuid,
        'query' => $request->all(),
    ]);

    $sessionId = $request->get('session_id');
    if (!$sessionId) {
        abort(404, 'Session Stripe manquante');
    }

    Stripe::setApiKey(config('services.stripe.secret'));

    try {
        $session = StripeSession::retrieve([
            'id' => $sessionId,
            'expand' => ['shipping_cost.shipping_rate', 'shipping_details'],
        ]);
    } catch (\Throwable $e) {
        \Log::error('[Checkout] Erreur récupération session Stripe', [
            'err' => $e->getMessage(),
        ]);
        abort(500, 'Impossible de récupérer la session Stripe');
    }

    $order = Order::where('uuid', $orderUuid)->firstOrFail();

    // 📦 Mode de livraison (fallback si vide)
    $shipping = [
        'label' => $session->shipping_cost->shipping_rate->display_name
            ?? $session->shipping_cost->shipping_rate
            ?? '—',
        'amount_total' => $session->shipping_cost->amount_total ?? 0,
    ];

    // 🏠 Adresse (fallback si vide)
    if (isset($session->shipping_details->address)) {
        $address = [
            'name'        => $session->shipping_details->name ?? '',
            'line1'       => $session->shipping_details->address->line1 ?? '',
            'line2'       => $session->shipping_details->address->line2 ?? '',
            'postal_code' => $session->shipping_details->address->postal_code ?? '',
            'city'        => $session->shipping_details->address->city ?? '',
            'country'     => $session->shipping_details->address->country ?? '',
        ];
    } else {
        $address = null;
    }

    // ✅ Marquer comme payé immédiatement
    if ($order->payment_status !== 'paid') {
        $order->payment_status = 'paid';
        $order->paid_at        = $order->paid_at ?: now();
        $order->ordered_at     = $order->ordered_at ?: now();
        $order->save();
    }

    // ✅ Vider le panier
    $this->clearCartSession();
    $this->clearDbCart($order->user_id, $order->cart_token ?? $request->session()->get('cart_token'));

    return inertia('Checkout/Success', [
        'order'    => $order,
        'shipping' => $shipping,
        'address'  => $address,
    ]);
}

    /**
     * Vide le panier en session
     */
    private function clearCartSession(): void
    {
        session()->forget(['cart', 'cart_token', 'current_order_uuid']);
    }

    /**
     * Vide le panier en DB
     */
    private function clearDbCart(?int $userId, ?string $cartToken): void
    {
        // Vider les cart_items via le cart_id
        if ($userId) {
            $cart = \App\Models\Cart::where('user_id', $userId)->where('status', 'open')->first();
            if ($cart) {
                \App\Models\CartItem::where('cart_id', $cart->id)->delete();
                $cart->update(['status' => 'closed']);
            }
        }
        
        // Pour les invités avec session_token
        if ($cartToken) {
            \App\Models\CartItem::where('session_token', $cartToken)->delete();
        }
    }
}
