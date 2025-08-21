<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsletterSubscriberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'email' => ['required','email:rfc,dns','max:255'],
        'website' => ['nullable','string','max:255'], // honeypot
    ]);

    // 🕷️ Bot: on "réussit" sans rien faire
    if (!empty($validated['website'])) {
        return back()->with('success', 'Merci ! Vous êtes bien inscrit(e).');
    }

    $email = mb_strtolower($validated['email']);

    $subscriber = NewsletterSubscriber::firstOrNew(['email' => $email]);
    $subscriber->is_active = true;
    if (!$subscriber->unsubscribe_token) {
        $subscriber->unsubscribe_token = Str::uuid()->toString();
    }
    $subscriber->save();

    // 🔥 Appel direct à l’API Brevo
    $resp = Http::withHeaders([
        'accept' => 'application/json',
        'api-key' => config('services.brevo.key'),
        'content-type' => 'application/json',
    ])->post('https://api.brevo.com/v3/contacts', [
        'email' => $email,
        'listIds' => [(int) config('services.brevo.list_id')],
        'updateEnabled' => true,
    ]);

    Log::info('Brevo response', ['status' => $resp->status(), 'body' => $resp->body()]);

    if ($resp->successful()) {
        return back()->with('success', 'Merci ! Vous êtes bien inscrit(e).');
    }

    // On signale l’échec mais on garde l’inscription locale
    return back()->with('success', 'Inscription enregistrée. (Sync Brevo à réessayer)');
}

public function unsubscribe(Request $request)
{
    $request->validate(['token' => ['required','string']]);

    $subscriber = NewsletterSubscriber::where('unsubscribe_token', $request->string('token'))->first();

    if (!$subscriber) {
        return back()->with('error', 'Lien de désinscription invalide.');
    }

    $subscriber->is_active = false;
    $subscriber->save();

    // Désinscription Brevo → blackliste l’email
    Http::withHeaders([
        'accept' => 'application/json',
        'api-key' => config('services.brevo.key'),
        'content-type' => 'application/json',
    ])->put('https://api.brevo.com/v3/contacts/'.urlencode($subscriber->email), [
        'emailBlacklisted' => true,
    ]);

    return back()->with('success', 'Vous êtes désinscrit(e).');
}

    /**
     * Display the specified resource.
     */
    public function show(NewsletterSubscriber $newsletterSubscriber)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NewsletterSubscriber $newsletterSubscriber)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NewsletterSubscriber $newsletterSubscriber)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NewsletterSubscriber $newsletterSubscriber)
    {
        //
    }
}
