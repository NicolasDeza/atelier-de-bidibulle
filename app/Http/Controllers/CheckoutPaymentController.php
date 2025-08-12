<?php

namespace App\Http\Controllers;

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
        return inertia('Checkout/Payment', [
            'order' => $order
        ]);
    }

    /**
     * Crée la session Stripe Checkout.
     */
     public function createSession(Request $request, Order $order)
    {
        // Clé secrète Stripe
        Stripe::setApiKey(config('services.stripe.secret'));

        // Construire les lignes d'articles
        $lineItems = [];
        foreach ($order->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($order->currency ?? 'EUR'),
                    'product_data' => [
                        'name' => $item->name,
                    ],
                    // Stripe attend le montant en CENTIMES
                    'unit_amount' => $item->unit_price,
                ],
                'quantity' => $item->quantity,
            ];
        }

        // Ajouter la livraison si > 0
        if ($order->shipping_total > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($order->currency ?? 'EUR'),
                    'product_data' => [
                        'name' => 'Frais de livraison',
                    ],
                    'unit_amount' => $order->shipping_total,
                ],
                'quantity' => 1,
            ];
        }

        // Créer la session Checkout Stripe
        $session = StripeSession::create([
            'payment_method_types' => ['card', 'bancontact'], // tu peux en ajouter d'autres
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('checkout.payment.return', $order) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.payment.show', $order),
            'customer_email' => $order->customer_email, // email client
        ]);

        // Sauvegarder l'ID Stripe dans la commande
        $order->stripe_payment_intent_id = $session->payment_intent ?? null;
        $order->payment_method = 'stripe';
        $order->save();

        // Retourner l'URL Stripe
        return response()->json([
            'url' => $session->url,
        ]);
    }

    /**
     * Gère le retour après paiement.
     */
    public function return(Request $request, Order $order)
    {
        // Ici, on pourra vérifier le paiement via Webhook ou en récupérant la session Stripe
        return inertia('Checkout/Success', [
            'order' => $order
        ]);
    }
}
