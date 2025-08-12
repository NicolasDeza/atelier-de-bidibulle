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
     * Affiche la page Paiement (verrouillée).
     */
    public function show(Request $request, Order $order)
    {
        if ($redirect = $this->guardPaymentAccess($request, $order)) {
            return $redirect;
        }

        // On envoie UNIQUEMENT le nécessaire à la vue
        return Inertia::render('Checkout/Payment', [
            'order' => [
                'uuid'           => $order->uuid,
                'customer_email' => $order->customer_email,
                'currency'       => $order->currency,
                // adapte: si tu stockes en centimes, mets (int); si en euros DECIMAL, (float)
                'shipping_total' => (float) $order->shipping_total,
                'total_price'    => (float) $order->total_price,
                'items'          => $order->items, // accessor du modèle
            ],
        ]);
    }

    /**
     * Crée la session Stripe Checkout (avec re-vérification des gardes).
     */
    public function createSession(Request $request, Order $order)
{
    if ($redirect = $this->guardPaymentAccess($request, $order)) {
        return $redirect;
    }

    Stripe::setApiKey(config('services.stripe.secret'));

    // Relations pour éventuels fallbacks
    $order->load(['orderProducts.product']);

    // Helper: euros -> centimes (si <=100 on considère euros)
    $toCents = fn ($v) => (int) ($v > 100 ? $v : round((float)$v * 100));

    $lineItems = [];

    foreach ($order->orderProducts as $op) {
        $unit = $op->unit_price ?? optional($op->product)->price ?? 0;
        $amountCents = $toCents($unit);
        if ($amountCents <= 0) continue;

        $lineItems[] = [
            'price_data' => [
                'currency'     => strtolower($order->currency ?? 'EUR'),
                'product_data' => ['name' => $op->name ?? (optional($op->product)->name ?? 'Produit')],
                'unit_amount'  => $amountCents,
            ],
            'quantity' => (int) ($op->quantity ?: 1),
        ];
    }

    $shippingCents = $toCents($order->shipping_total ?? 0);
    if ($shippingCents > 0) {
        $lineItems[] = [
            'price_data' => [
                'currency'     => strtolower($order->currency ?? 'EUR'),
                'product_data' => ['name' => 'Frais de livraison'],
                'unit_amount'  => $shippingCents,
            ],
            'quantity' => 1,
        ];
    }

    if (empty($lineItems)) {
        $lineItems[] = [
            'price_data' => [
                'currency'     => strtolower($order->currency ?? 'EUR'),
                'product_data' => ['name' => 'Commande #' . $order->id],
                'unit_amount'  => $toCents($order->total_price ?? 0),
            ],
            'quantity' => 1,
        ];
    }

    try {
        // Idempotency pour éviter les doubles sessions si double-clic (optionnel mais pro)
        $session = StripeSession::create(
            [
                'mode'           => 'payment',
                'line_items'     => $lineItems,
                'customer_email' => $order->customer_email,
                'locale'         => 'fr',
                'success_url'    => route('checkout.payment.return', $order) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'     => route('checkout.payment.show', $order),
                'metadata'       => [
                    'order_id'       => (string) $order->id,
                    'order_uuid'     => (string) $order->uuid,
                    'cart_token'     => $request->session()->get('cart_token'),
                    'customer_email' => (string) $order->customer_email,
                ],
            ],
            ['idempotency_key' => 'order-'.$order->uuid.'-create-session']
        );
    } catch (\Stripe\Exception\ApiErrorException $e) {
        \Log::error('Stripe API error while creating Checkout Session', [
            'order_id' => $order->id,
            'uuid'     => $order->uuid,
            'message'  => $e->getMessage(),
        ]);
        return response()->json([
            'error' => 'Erreur de paiement, veuillez réessayer dans un instant.'
        ], 502);
    } catch (\Throwable $e) {
        \Log::error('Unexpected error while creating Checkout Session', [
            'order_id' => $order->id,
            'uuid'     => $order->uuid,
            'message'  => $e->getMessage(),
        ]);
        return response()->json([
            'error' => 'Une erreur est survenue. Merci de réessayer.'
        ], 500);
    }

    // Références + statut si la session est bien créée
    $order->stripe_checkout_session_id = $session->id;
    $order->stripe_payment_intent_id   = $session->payment_intent ?? null;
    $order->payment_provider           = 'stripe';
    $order->payment_status             = 'processing';
    $order->save();

    return response()->json(['url' => $session->url]);
}
    /**
     * Page de retour succès (MVP sans webhook).
     * En prod: laisser le webhook faire foi et ici afficher l’état.
     */
    public function return(Request $request, Order $order)
    {
        if ($redirect = $this->guardPaymentAccess($request, $order)) {
            return $redirect;
        }

        // MVP: marquer payé ici (remplacé ensuite par le webhook)
        if ($order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
            $order->paid_at = now();
            $order->save();
        }

        // Vider panier (adapte selon ta logique: session vs DB)
        $this->clearCartSession();
        $this->clearDbCart($order->user_id, $order->cart_token ?? $request->session()->get('cart_token'));

        return Inertia::render('Checkout/Success', [
            'order' => [
                'id'           => $order->id,
                'uuid'         => $order->uuid,
                'total_price'  => (float) $order->total_price,
                'currency'     => $order->currency,
                'paid_at'      => optional($order->paid_at)->toDateTimeString(),
            ],
        ]);
    }

    /**
     * ---- Helpers de garde & panier ----
     */

    private function guardPaymentAccess(Request $request, Order $order): ?RedirectResponse
    {
        // Ownership: user connecté OU invité ayant ce current_order_uuid
        if (!$this->ownsOrder($request, $order)) {
            return redirect()->route('cart.index')
                ->with('error', 'Accès refusé à cette commande.');
        }

        // Statut payable
        if (!in_array($order->payment_status, ['unpaid', 'processing'], true)) {
            return redirect()->route('orders.show', $order->uuid)
                ->with('error', 'Cette commande n’est pas payable.');
        }

        // Adresse & méthode de livraison requises
        if (!$order->shippingAddress || !$order->shippingMethod) {
            return redirect()->route('checkout.address.show', $order->uuid)
                ->with('error', 'Veuillez compléter l’adresse et la livraison.');
        }

        // Email requis
        if (empty($order->customer_email)) {
            return redirect()->route('checkout.address.show', $order->uuid)
                ->with('error', 'Veuillez renseigner votre e-mail avant le paiement.');
        }

        // Montant valide
        if (empty($order->total_price) || $toZero = (float)$order->total_price <= 0) {
            return redirect()->route('checkout.address.show', $order->uuid)
                ->with('error', 'Le montant de la commande est invalide.');
        }

        return null;
    }

    private function ownsOrder(Request $request, Order $order): bool
    {
        // Cas user connecté
        if (auth()->check() && $order->user_id && $order->user_id === auth()->id()) {
            return true;
        }

        // Cas invité: on a stocké l’UUID de l’ordre validé à l’étape adresse
        $sessionOrderUuid = $request->session()->get('current_order_uuid');
        if ($sessionOrderUuid && $sessionOrderUuid === $order->uuid) {
            return true;
        }

        // En dernier recours, tu peux autoriser l’UUID "public" (moins strict) :
        // return true;

        return false;
    }

    private function clearCartSession(): void
    {
        session()->forget(['cart', 'cart_token']);
    }

    private function clearDbCart(?int $userId, ?string $cartToken): void
    {
        // Si tu as une table cart_items, décommente et adapte :
        // \App\Models\CartItem::when($userId, fn($q) => $q->where('user_id', $userId))
        //     ->when($cartToken, fn($q) => $q->orWhere('session_token', $cartToken))
        //     ->delete();
    }
}
