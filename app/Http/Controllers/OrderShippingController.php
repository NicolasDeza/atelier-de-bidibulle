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
    public function update(Request $request, $orderUuid)
    {
        $order = Order::where('uuid', $orderUuid)->firstOrFail();

        $data = $request->validate(['tracking_number' => ['required','string','max:255']]);

        $wasShippedBefore = $order->shipping_status === 'shipped';

        $order->tracking_number = $data['tracking_number'];
        $order->shipping_status = 'shipped';
        $order->shipped_at = now();
        $order->save();

        if (!$wasShippedBefore && $order->customer_email) {
            Mail::to($order->customer_email)->send(new OrderShippedMail($order));
        }

        return back()->with('status', 'Suivi enregistré'.(!$wasShippedBefore ? ' et e-mail envoyé.' : '.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
