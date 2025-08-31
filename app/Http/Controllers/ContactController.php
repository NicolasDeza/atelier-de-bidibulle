<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;

class ContactController extends Controller

{
     /**
     * Traite l'envoi du formulaire de contact :
     * - valide les champs (nom, email, message)
     * - protège contre le spam via un champ honeypot
     * - envoie un email au propriétaire du site
     */

    public function send(Request $request)
{
    $validated = $request->validate([
        'name'    => ['required','string','max:255'],
        'email'   => ['required','email','max:255'],
        'message' => ['required','string','max:5000'],
        // honeypot (doit rester vide)
        'website' => ['nullable','string','max:0'],
    ], [
        'website.max' => 'Spam détecté.',
    ]);

    // Bot détecté -> on répond "ok" sans envoyer
    if ($request->filled('website')) {
        return back()->with('success', 'Message envoyé.');
    }

    try {
        Mail::to(config('mail.from.address'))
            ->send(new ContactMail(
                $validated['name'],
                $validated['email'],
                $validated['message']
            ));
    } catch (\Exception $e) {
        // Cas erreur SMTP
        return back()->with('error', "Une erreur est survenue. Merci de réessayer plus tard.");
    }

    return back()->with('success', 'Message envoyé.');
}
}
