@php
$trackingUrl = $order->tracking_number
  ? 'https://track.bpost.cloud/btr/web/#/search?itemCode='.urlencode($order->tracking_number)
  : null;
@endphp

@component('mail::message')
# Votre commande est expédiée ✅

Commande **#{{ $order->uuid }}** a été déposée chez Bpost.

**Bpost prend maintenant le relais** et vous recevrez prochainement un email séparé avec toutes les informations de suivi de votre colis.

Merci pour votre confiance 💛

**Atelier de Bidibule**
@endcomponent
