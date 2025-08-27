<?php

namespace App\Http\Controllers;

use App\Mail\OrderShippedMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderShippingController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $orderUuid)
{
    $order = Order::where('uuid', $orderUuid)->firstOrFail();

    // tracking_number optionnel désormais
    $data = $request->validate([
        'tracking_number' => ['nullable','string','max:255'],
    ]);

    $wasShippedBefore = $order->shipping_status === 'shipped';
    $numberChanged = isset($data['tracking_number']) &&
        (string)$data['tracking_number'] !== (string)($order->tracking_number ?? '');

    // Mise à jour expédition
    $order->tracking_number = $data['tracking_number'] ?? null;
    $order->shipping_status = 'shipped';
    $order->status = 'shipped';
    if (is_null($order->shipped_at)) {
        $order->shipped_at = now();
    }

    $order->save();

    // Mail seulement si nouvelle expédition OU numéro changé
    if ((!$wasShippedBefore || $numberChanged) && $order->customer_email) {
        Mail::to($order->customer_email)->queue(new OrderShippedMail($order));
    }

    return redirect()
        ->route('admin.orders.index', ['token' => $request->token])
        ->with('success', 'Commande expédiée avec succès !');
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
