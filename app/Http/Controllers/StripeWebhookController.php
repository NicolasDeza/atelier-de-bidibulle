<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPaidMail;
use App\Models\Order;
use Stripe\Webhook;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\PaymentIntent;
use Stripe\ShippingRate;
use App\Models\StripeEvent;

class StripeWebhookController extends Controller
{
    /**
     * Point d’entrée du webhook Stripe.
     * - Vérifie la signature Stripe (sécurité)
     * - Gère l'idempotence (pas traiter 2x le même event)
     * - Redirige vers la bonne méthode selon le type d’événement
     */
    public function handle(Request $request)
    {
        $payload     = $request->getContent();
        $sigHeader   = $request->header('Stripe-Signature');
        $endpointSec = config('services.stripe.webhook_secret');

        // Vérification signature Stripe
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSec);
        } catch (\UnexpectedValueException $e) {
            Log::error('[Stripe] Payload invalide', ['err' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('[Stripe] Signature invalide', ['err' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Idempotence -> vérif si déjà traité pour pas renvoyer un event déjà traité
        if (StripeEvent::where('event_id', $event->id)->exists()) {
            Log::warning('[Stripe] Event déjà traité', ['event_id' => $event->id]);
            return response()->json(['status' => 'duplicate'], 200);
        }

        // On enregistre l’event pour le marquer comme “déjà vu”
        StripeEvent::create([
            'event_id' => $event->id,
            'type'     => $event->type,
        ]);

        // Dispatcher selon le type d’événement Stripe
        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutCompleted($event->data->object);
        } elseif ($event->type === 'payment_intent.succeeded') {
            Log::info('[Stripe] Paiement réussi', ['pi' => $event->data->object->id]);
        } elseif ($event->type === 'payment_intent.payment_failed') {
            Log::warning('[Stripe] Paiement échoué', [
                'pi'     => $event->data->object->id,
                'reason' => $event->data->object->last_payment_error?->message,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Quand un checkout Stripe est terminé avec succès :
     * - On relit la session Stripe et le PaymentIntent
     * - On met à jour la commande en base
     * - On décrémente le stock des produits
     * - On envoie l’email de confirmation
     */
    protected function handleCheckoutCompleted($session)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // 1) Relire la session Stripe avec shipping_rate
        try {
            $session = StripeSession::retrieve([
                'id'     => $session->id,
                'expand' => ['shipping_cost.shipping_rate'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('[Stripe] Session retrieve failed', [
                'session_id' => $session->id ?? null,
                'err'        => $e->getMessage(),
            ]);
        }

        // 2) Récupérer le PaymentIntent (pour fallback si besoin)
        $pi = null;
        try {
            if (!empty($session->payment_intent)) {
                $pi = PaymentIntent::retrieve([
                    'id'     => $session->payment_intent,
                    'expand' => ['latest_charge'],
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[Stripe] PaymentIntent retrieve failed', [
                'pi'  => $session->payment_intent ?? null,
                'err' => $e->getMessage(),
            ]);
        }

        // 3) Mise à jour commande + stock + email
        try {
            DB::transaction(function () use ($session, $pi) {
                $refId   = $session->metadata->order_id   ?? null;
                $refUuid = $session->metadata->order_uuid ?? null;

                if (!$refId && !$refUuid) {
                    Log::error('[Stripe] Pas de metadata order_id|order_uuid', ['session' => $session->id ?? null]);
                    return;
                }

                // Recherche de la commande
                $order = Order::when($refId, fn($q) => $q->where('id', $refId))
                    ->when($refUuid, fn($q) => $q->orWhere('uuid', $refUuid))
                    ->lockForUpdate()
                    ->first();

                if (!$order) {
                    Log::error('[Stripe] Order introuvable', ['order_id' => $refId, 'order_uuid' => $refUuid]);
                    return;
                }

                // Savoir si déjà payé (important pour stock/email)
                $wasPaidBefore = $order->payment_status === 'paid';

                // Montants (total + shipping) venant de Stripe
                $amountTotalCts =
                    $session->amount_total
                    ?? ($pi->amount_received ?? null)
                    ?? ($pi->amount ?? null)
                    ?? 0;

                $shippingCts =
                    ($session->shipping_cost->amount_total ?? null)
                    ?? ($session->total_details->amount_shipping ?? null)
                    ?? 0;

                $totalEuro    = $this->centsToEuro($amountTotalCts);
                $shippingEuro = $this->centsToEuro($shippingCts);

                // Nom du mode de livraison
                $shippingLabel = null;
                if (isset($session->shipping_cost->shipping_rate)) {
                    $rate = $session->shipping_cost->shipping_rate;
                    if (is_object($rate) && isset($rate->display_name)) {
                        $shippingLabel = $rate->display_name;
                    } elseif (is_string($rate)) {
                        try {
                            $rateObj = ShippingRate::retrieve($rate);
                            $shippingLabel = $rateObj->display_name ?? null;
                        } catch (\Throwable $e) {
                            Log::warning('[Stripe] ShippingRate retrieve failed', [
                                'rate_id' => $rate,
                                'err'     => $e->getMessage(),
                            ]);
                        }
                    }
                }

                // Adresse de livraison (fallback si besoin)
                $address = $session->shipping_details->address
                    ?? ($pi->shipping->address ?? null)
                    ?? ($pi->latest_charge->shipping->address ?? null)
                    ?? ($session->customer_details->address ?? null);

                // Email client (si pas déjà présent en DB)
                if (empty($order->customer_email)) {
                    $order->customer_email =
                        $session->customer_details->email
                        ?? ($pi->charges->data[0]->billing_details->email ?? null);
                }

                // Mise à jour de la commande
                $order->payment_status             = 'paid';
                $order->paid_at                    = $order->paid_at ?: now();
                $order->ordered_at                 = $order->ordered_at ?: now();
                $order->stripe_payment_intent_id   = $session->payment_intent ?? $order->stripe_payment_intent_id;
                $order->stripe_checkout_session_id = $session->id ?? $order->stripe_checkout_session_id;
                if ($shippingLabel !== null) {
                    $order->shipping_method_label = $shippingLabel;
                }
                $order->shipping_total = $shippingEuro;
                if ($address) {
                    $order->shipping_address_json = json_encode($address, JSON_UNESCAPED_UNICODE);
                }

                // Écraser le total payé avec la valeur Stripe
                if ($amountTotalCts > 0) {
                    $order->total_price = $totalEuro;
                }

                $order->save();

                // Décrément stock uniquement la 1ère fois
                if (!$wasPaidBefore) {
                    $order->loadMissing('orderProducts.product');

                    foreach ($order->orderProducts as $op) {
                        $product = $op->product()->lockForUpdate()->first();
                        if ($product) {
                            $newStock = max(0, (int) $product->stock - (int) $op->quantity);
                            if ($newStock !== (int) $product->stock) {
                                $product->stock = $newStock;
                                $product->save();
                            }
                        }
                    }
                }

                // Email confirmation et envoyé seulement 1 fois
                if (!$wasPaidBefore && $order->customer_email) {
                    DB::afterCommit(function () use ($order) {
                        Mail::to($order->customer_email)->queue(new OrderPaidMail($order));
                    });
                }

                Log::info('[Stripe] Commande mise à jour', [
                    'order_id' => $order->id,
                    'uuid'     => $order->uuid,
                    'label'    => $shippingLabel,
                    'ship€'    => $shippingEuro,
                    'total€'   => $totalEuro,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('[Stripe] Erreur handleCheckoutCompleted', [
                'err'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Convertit des centimes (int) en euros (float)
     */
    private function centsToEuro($cents): float
    {
        return round(((int) $cents) / 100, 2);
    }
}
