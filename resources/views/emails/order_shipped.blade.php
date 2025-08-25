@php
$trackingUrl = $order->tracking_number
  ? 'https://track.bpost.cloud/btr/web/#/search?itemCode='.urlencode($order->tracking_number)
  : null;
@endphp

@component('mail::message')
# Votre commande est en route 🚚

Commande **#{{ $order->uuid }}** expédiée.

@isset($trackingUrl)
@component('mail::button', ['url' => $trackingUrl])
Suivre mon colis
@endcomponent
@else
**Numéro de suivi :** {{ $order->tracking_number ?? '—' }}
@endisset

Merci pour votre commande 💛
**Atelier de Bidibulle**
@endcomponent
