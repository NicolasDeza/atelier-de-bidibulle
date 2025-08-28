@php
$trackingUrl = $order->tracking_number
  ? 'https://track.bpost.cloud/btr/web/#/search?itemCode='.urlencode($order->tracking_number)
  : null;
@endphp

@component('mail::message')
# Votre commande est expédiée ✅

Commande **#{{ $order->uuid }}**

@if($order->shipping_method_label === 'Remise en main propre')
📍 Votre commande est prête et vous attend à l’atelier.
Vous serez contacté(e) pour convenir d’un moment de remise en main propre.
@else
Elle a été déposée chez **Bpost**.

**Bpost prend maintenant le relais** et vous recevrez prochainement un email séparé avec toutes les informations de suivi de votre colis.

@isset($trackingUrl)
@component('mail::button', ['url' => $trackingUrl])
Suivre mon colis
@endcomponent
@endisset
@endif

Merci pour votre confiance 💛

**Atelier de Bidibule**
@endcomponent
