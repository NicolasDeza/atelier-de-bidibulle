<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:255'],
            'email'   => ['required','email','max:255'],
            'message' => ['required','string','max:5000'],
            // honeypot (doit rester vide)
            'website' => ['nullable','string','max:0'],
        ], [
            'website.max' => 'Spam détecté.',
        ]);

        // Bot détecté → on répond "ok" sans envoyer
        if ($request->filled('website')) {
            return back()->with('success', 'Message envoyé.');
        }

        Mail::to(config('mail.from.address'))
            ->send(new ContactMail(
                $data['name'],
                $data['email'],
                $data['message']   // <- passe une string, pas le tableau
            ));

        return back()->with('success', 'Message envoyé.');
    }
}
