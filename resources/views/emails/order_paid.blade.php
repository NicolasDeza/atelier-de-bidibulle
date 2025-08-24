@component('mail::message')
# Merci pour votre commande 🎉

Bonjour,

Votre paiement a bien été confirmé pour la commande **#{{ $order->uuid }}**.

@component('mail::panel')
**Total :** {{ number_format((float)$order->total_price, 2, ',', ' ') }} {{ $order->currency }}
**Mode de livraison :** {{ $order->shipping_method_label ?? '—' }}
@endcomponent

@if($order->shipping_address_json)
@php $addr = json_decode($order->shipping_address_json, true); @endphp
**Adresse de livraison :**
{{ $addr['name'] ?? '' }}
{{ $addr['line1'] ?? '' }} @if(!empty($addr['line2'])) ({{ $addr['line2'] }}) @endif
{{ $addr['postal_code'] ?? '' }} {{ $addr['city'] ?? '' }} – {{ $addr['country'] ?? '' }}
@endif

@component('mail::button', ['url' => url('/')])
Retourner sur le site
@endcomponent

Nous vous enverrons un e-mail dès que votre colis sera expédié.
Merci de votre confiance 💛

Cordialement,
**Atelier de Bidibulle**
@endcomponent
