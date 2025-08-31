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
     * Crée une session de paiement Stripe et redirige l'utilisateur
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

            // Si le produit est personnalisé, on ajoute la personnalisation dans le nom
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

        // Si aucun produit valide, retour au panier
        if (empty($lineItems)) {
            return redirect()->route('cart.index')->with('error', 'Aucun produit valide.');
        }

        // Frais de livraison Bpost gratuits si total >= 50€
        $bpostCents = $itemsSubtotalCents >= 5000 ? 0 : 590;

        // Création de la session de paiement Stripe
        $session = StripeSession::create(
            [
                'mode'       => 'payment',
                'locale'     => 'fr',
                'line_items' => $lineItems,

                // Collecte adresse + téléphone
                'shipping_address_collection' => ['allowed_countries' => ['FR', 'BE']],
                'phone_number_collection'     => ['enabled' => true],

                // Modes de livraison : retrait ou Bpost
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

                // Redirection après paiement
                'success_url'    => route('checkout.payment.return', $order) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'     => route('cart.index'),

                // Ajout de métadonnées utiles pour le suivi
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

        // Sauvegarde de l'ID Stripe de la session dans la commande
        $order->stripe_checkout_session_id = $session->id;
        $order->payment_status             = 'processing';
        $order->save();

        // Redirection vers Stripe (Inertia ou redirection normale)
        return $request->header('X-Inertia')
            ? Inertia::location($session->url)
            : redirect()->away($session->url);
    }

    /**
     * Page de retour après paiement Stripe (fallback si le webhook est en retard)
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
                    'expand' => ['shipping_cost.shipping_rate', 'payment_intent'],
                ]);

                // Normalisation des infos livraison
                $shipping = $this->buildShippingArray($session);

                // Extraction de l'adresse (plusieurs sources possibles)
                $address = $this->extractAddressFromSessionOrPI($session);
            } catch (\Throwable $e) {
                \Log::error('[Checkout] Erreur récupération session Stripe', ['err' => $e->getMessage()]);
            }
        }

        // Mise à jour commande en "payée" si le webhook n'est pas encore passé
        if ($order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
            $order->paid_at        = $order->paid_at ?: now();
            $order->ordered_at     = $order->ordered_at ?: now();
            $order->save();
        }

        // Nettoyage du panier (session + BDD)
        $this->clearCartSession();
        $this->clearDbCart($order->user_id, $order->cart_token ?? $request->session()->get('cart_token'));

        // Affichage de la page succès
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
     * Page succès alternative (mêmes infos mais autre route)
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

        // Vérifie le montant payé côté Stripe et met à jour si besoin
        $amountTotalCts = $session->amount_total
            ?? ($session->payment_intent->amount_received ?? null)
            ?? ($session->payment_intent->amount ?? null);

        $totalEuro = $amountTotalCts ? round($amountTotalCts / 100, 2) : null;

        if ($totalEuro !== null && (float) $order->total_price !== (float) $totalEuro) {
            $order->total_price = $totalEuro;
            $order->save();
        }

        // Infos livraison et adresse
        $shipping = $this->buildShippingArray($session);

        $address = $session->shipping_details->address
            ?? ($session->payment_intent->shipping->address ?? null)
            ?? ($session->payment_intent->latest_charge->shipping->address ?? null)
            ?? ($session->customer_details->address ?? null);

        $total = $session->amount_total ? $session->amount_total / 100 : (float) $order->total_price;

        return inertia('Checkout/Success', [
            'order'    => $order->fresh(),
            'shipping' => $shipping,
            'address'  => $address,
            'total'    => $total
        ]);
    }

    /**
     * Helper pour normaliser les infos livraison
     */
    private function buildShippingArray($session): array
    {
        return [
            'label' => $session->shipping_cost->shipping_rate->display_name
                ?? $session->shipping_cost->shipping_rate
                ?? 'Remise en main propre',
            // montant en centimes (pas de division par 100 ici)
            'amount_total' => (int) ($session->shipping_cost->amount_total ?? 0),
        ];
    }

    /**
     * Extraction robuste de l'adresse depuis session ou PaymentIntent
     */
    private function extractAddressFromSessionOrPI($session): ?array
    {
        // Essaye d'abord via session.shipping_details
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

        // Sinon customer_details
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

        // Fallback via PaymentIntent (objet ou ID à recharger)
        try {
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
     * Supprime le panier en base (lié à l'user ou au token invité)
     */
    private function clearDbCart(?int $userId, ?string $cartToken): void
    {
        if ($userId) {
            $cart = \App\Models\Cart::where('user_id', $userId)->where('status', 'open')->first();
            if ($cart) {
                \App\Models\CartItem::where('cart_id', $cart->id)->delete();
                $cart->update(['status' => 'closed']);
            }
        }

        if ($cartToken) {
            \App\Models\CartItem::where('session_token', $cartToken)->delete();
        }
    }
}

