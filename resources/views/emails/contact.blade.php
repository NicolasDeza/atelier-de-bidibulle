@component('mail::message')
# 📩 Nouveau message de contact

Vous avez reçu un nouveau message depuis **L’Atelier de Bidibule**.

**Nom :** {{ $name }}
**Email :** {{ $email }}

---

**Message :**
{{ $messageText }}

@component('mail::button', ['url' => 'mailto:'.$email])
Répondre à {{ $name }}
@endcomponent

Merci,
L’équipe de L’Atelier de Bidibule
@endcomponent
