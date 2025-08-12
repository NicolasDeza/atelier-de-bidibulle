<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;



class CheckoutPaymentController extends Controller
{
    /**
     * Affiche la page récapitulatif de la commande.
     */
    public function show(Order $order)
    {
        // Ici tu peux ajouter des gardes (ownership user ou token invité)
        // et t'assurer que l'order est unpaid/processing.
        // On envoie juste ce qu’il faut à la vue :
        return Inertia::render('Checkout/Payment', [
            'order' => [
                'uuid'           => $order->uuid,
                'id'             => $order->id,
                'customer_email' => $order->customer_email,
                'currency'       => $order->currency,
                'shipping_total' => (int) $order->shipping_total, // en centimes
                'total_price'    => (int) $order->total_price,    // en centimes
                'items'          => $order->items,                // via accessor du modèle
            ],
        ]);
    }

    /**
     * Crée la session Stripe Checkout.
     */
    public function createSession(Request $request, Order $order)
{
    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

    // Charger les relations nécessaires pour les fallbacks
    $order->load(['orderProducts.product']);

    // Helper: détecte euros vs centimes
    $toCents = fn ($v) => (int) ($v > 100 ? $v : round((float)$v * 100));

    $lineItems = [];

    // 1) Lignes produits
    foreach ($order->orderProducts as $op) {
        // Prix figé dans la ligne > sinon prix actuel du produit > sinon 0
        $unit = $op->unit_price ?? optional($op->product)->price ?? 0;

        $amountCents = $toCents($unit);
        if ($amountCents <= 0) {
            continue; // on saute les lignes à 0
        }

        $lineItems[] = [
            'price_data' => [
                'currency'     => strtolower($order->currency ?? 'EUR'),
                'product_data' => ['name' => $op->name ?? optional($op->product)->name ?? 'Produit'],
                'unit_amount'  => $amountCents, // CENTIMES
            ],
            'quantity' => (int) ($op->quantity ?: 1),
        ];
    }

    // 2) Livraison (si > 0)
    $shippingCents = $toCents($order->shipping_total ?? 0);
    if ($shippingCents > 0) {
        $lineItems[] = [
            'price_data' => [
                'currency'     => strtolower($order->currency ?? 'EUR'),
                'product_data' => ['name' => 'Frais de livraison'],
                'unit_amount'  => $shippingCents, // CENTIMES
            ],
            'quantity' => 1,
        ];
    }

    // 3) Secours: si aucune ligne valide, on envoie un seul item = total commande
    if (empty($lineItems)) {
        $amountCents = $toCents($order->total_price ?? 0);
        $lineItems[] = [
            'price_data' => [
                'currency'     => strtolower($order->currency ?? 'EUR'),
                'product_data' => ['name' => 'Commande #' . $order->id],
                'unit_amount'  => max($amountCents, 0),
            ],
            'quantity' => 1,
        ];
    }

    // 4) Création de la session Checkout
    $session = \Stripe\Checkout\Session::create([
        'mode'           => 'payment',
        'line_items'     => $lineItems,
        'customer_email' => $order->customer_email,
        'locale'         => 'fr', // retire-le si tu veux l'auto-détection
        'success_url'    => route('checkout.payment.return', $order) . '?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'     => route('checkout.payment.show', $order),
        'metadata'       => [
            'order_id'   => (string) $order->id,
            'order_uuid' => (string) $order->uuid,
        ],
    ]);

    // 5) Sauvegarde des références + statut
    $order->stripe_checkout_session_id = $session->id;
    $order->stripe_payment_intent_id   = $session->payment_intent ?? null;
    $order->payment_provider           = 'stripe';
    $order->payment_status             = 'processing';
    $order->save();

    return response()->json(['url' => $session->url]);
}

    /**
     * Gère le retour après paiement.
     */
     public function return(Request $request, Order $order)
    {
        // MVP : on marque payée ici (en prod, ce sera le webhook qui fera foi)
        if ($order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
            $order->paid_at = now();
            $order->save();
        }

        return Inertia::render('Checkout/Success', [
            'order' => [
                'uuid'         => $order->uuid,
                'id'           => $order->id,
                'paid_at'      => optional($order->paid_at)->toDateTimeString(),
                'total_price'  => (int) $order->total_price,
                'currency'     => $order->currency,
                'payment_method' => $order->payment_method,
            ],
        ]);
    }
}
