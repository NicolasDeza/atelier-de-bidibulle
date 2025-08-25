<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\PaymentIntent;

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

            // Personnalisation dans le nom de ligne
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

                // Collecte d’adresse de livraison
                'shipping_address_collection' => ['allowed_countries' => ['FR', 'BE']],
                'phone_number_collection'     => ['enabled' => true],

                // Deux modes: remise en main propre + Bpost
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

                // Retour avec session_id pour relecture
                'success_url'    => route('checkout.payment.return', $order) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'     => route('cart.index'),

                'metadata'       => [
                    'order_id'   => (string) $order->id,
                    'order_uuid' => (string) $order->uuid,
                    'customizations' => json_encode(
                        $order->orderProducts->pluck('customization', 'product_id')->filter()
                    ),
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
        $sessionId = $request->get('session_id');

        $shipping = null;
        $address  = null;

        if ($sessionId) {
            Stripe::setApiKey(config('services.stripe.secret'));

            try {
                $session = StripeSession::retrieve([
                    'id'     => $sessionId,
                    // pas d'expand shipping_details
                    // on veut l'objet payment_intent
                    'expand' => ['shipping_cost.shipping_rate', 'payment_intent'],
                ]);

                // Mode de livraison (label + montant)
                $shipping = [
                    'label'        => $session->shipping_cost->shipping_rate->display_name
                        ?? $session->shipping_cost->shipping_rate
                        ?? '—',
                    'amount_total' => $session->shipping_cost->amount_total ?? 0,
                ];

                // Adresse — 3 sources possibles
                $address = $this->extractAddressFromSessionOrPI($session);
            } catch (\Throwable $e) {
                \Log::error('[Checkout] Erreur récupération session Stripe', ['err' => $e->getMessage()]);
            }
        }

        // Sauvegarde de secours (si le webhook n'a pas encore tourné)
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
     * Page succès (route alternative)
     */
   public function success(Request $request, string $orderUuid)
{
    $sessionId = $request->string('session_id');
    abort_if(!$sessionId, 404, 'Session Stripe manquante');

    Stripe::setApiKey(config('services.stripe.secret'));

    try {
        $session = StripeSession::retrieve([
            'id'     => $sessionId,
            'expand' => ['shipping_cost.shipping_rate', 'payment_intent'],
        ]);
    } catch (\Throwable $e) {
        \Log::error('[Checkout] Session retrieve failed', ['err' => $e->getMessage()]);
        abort(500, 'Impossible de récupérer la session Stripe');
    }

    $order = Order::where('uuid', $orderUuid)->firstOrFail();

    // Montant payé côté Stripe (en centimes)
    $amountTotalCts = $session->amount_total
        ?? ($session->payment_intent->amount_received ?? null)
        ?? ($session->payment_intent->amount ?? null);

    $totalEuro = $amountTotalCts ? round($amountTotalCts / 100, 2) : null;

    // ✅ Fallback sûr : si la DB n’a pas (encore) le bon total, on le met à jour ici
    if ($totalEuro !== null && (float) $order->total_price !== (float) $totalEuro) {
        $order->total_price = $totalEuro;
        // on ne touche PAS au stock / e-mails ici
        $order->save();
    }

    // Livraison / adresse (inchangé)
    $shipping = [
        'label'        => $session->shipping_cost->shipping_rate->display_name
            ?? $session->shipping_cost->shipping_rate
            ?? '—',
        'amount_total' => $session->shipping_cost->amount_total ?? 0,
    ];

    $address = $session->shipping_details->address
        ?? ($session->payment_intent->shipping->address ?? null)
        ?? ($session->payment_intent->latest_charge->shipping->address ?? null)
        ?? ($session->customer_details->address ?? null);

    return inertia('Checkout/Success', [
        'order'    => $order->fresh(),
        'shipping' => $shipping,
        'address'  => $address,
        'total'    => $totalEuro ?? (float) $order->total_price, // valeur sûre pour l’affichage
    ]);
}


    /**
     * Fallback robuste pour extraire l'adresse
     */
    private function extractAddressFromSessionOrPI($session): ?array
    {
        // 1) shipping_details (si Stripe l'a mis)
        if (isset($session->shipping_details->address)) {
            return [
                'name'        => $session->shipping_details->name ?? '',
                'line1'       => $session->shipping_details->address->line1 ?? '',
                'line2'       => $session->shipping_details->address->line2 ?? '',
                'postal_code' => $session->shipping_details->address->postal_code ?? '',
                'city'        => $session->shipping_details->address->city ?? '',
                'country'     => $session->shipping_details->address->country ?? '',
            ];
        }

        // 2) customer_details.address
        if (isset($session->customer_details->address)) {
            return [
                'name'        => $session->customer_details->name ?? '',
                'line1'       => $session->customer_details->address->line1 ?? '',
                'line2'       => $session->customer_details->address->line2 ?? '',
                'postal_code' => $session->customer_details->address->postal_code ?? '',
                'city'        => $session->customer_details->address->city ?? '',
                'country'     => $session->customer_details->address->country ?? '',
            ];
        }

        // 3) payment_intent.shipping.address (on a déjà expand payment_intent)
        try {
            // Si c'est un objet
            if (is_object($session->payment_intent ?? null)) {
                if (isset($session->payment_intent->shipping->address)) {
                    return [
                        'name'        => $session->payment_intent->shipping->name ?? '',
                        'line1'       => $session->payment_intent->shipping->address->line1 ?? '',
                        'line2'       => $session->payment_intent->shipping->address->line2 ?? '',
                        'postal_code' => $session->payment_intent->shipping->address->postal_code ?? '',
                        'city'        => $session->payment_intent->shipping->address->city ?? '',
                        'country'     => $session->payment_intent->shipping->address->country ?? '',
                    ];
                }
            }
            // Si c'est un ID (par prudence)
            if (is_string($session->payment_intent ?? null)) {
                $pi = PaymentIntent::retrieve([
                    'id'     => $session->payment_intent,
                    'expand' => ['latest_charge'],
                ]);
                if (isset($pi->latest_charge->shipping->address)) {
                    return [
                        'name'        => $pi->latest_charge->shipping->name ?? '',
                        'line1'       => $pi->latest_charge->shipping->address->line1 ?? '',
                        'line2'       => $pi->latest_charge->shipping->address->line2 ?? '',
                        'postal_code' => $pi->latest_charge->shipping->address->postal_code ?? '',
                        'city'        => $pi->latest_charge->shipping->address->city ?? '',
                        'country'     => $pi->latest_charge->shipping->address->country ?? '',
                    ];
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('[Checkout] Fallback PI/latest_charge impossible', ['err' => $e->getMessage()]);
        }

        return null;
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
