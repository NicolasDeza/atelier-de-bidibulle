<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
         public function index(Request $request)
{
    $q     = trim((string) $request->get('q', ''));
    $scope = $request->get('scope', 'to-ship'); // to-ship | shipped | all

    $orders = Order::query()
        ->select([
            'id','uuid','customer_email','currency','total_price',
            'payment_status','shipping_status','shipping_method_label',
            'shipping_total','paid_at','shipped_at','shipping_address_json'
        ])
        ->when($scope === 'to-ship', fn($qq) => $qq
            ->where('payment_status','paid')
            ->where(function($w){
                $w->whereNull('shipping_status')
                  ->orWhere('shipping_status','!=','shipped');
            }))
        ->when($scope === 'shipped', fn($qq) => $qq->where('shipping_status','shipped'))
        ->when($q !== '', fn($qq) => $qq->where(fn($w) =>
            $w->where('uuid','like',"%{$q}%")
              ->orWhere('customer_email','like',"%{$q}%")))
        ->withSum('orderProducts', 'quantity') // 👈 c’est ça qui change
        ->orderByDesc('paid_at')
        ->paginate(20)
        ->withQueryString();

    return Inertia::render('Admin/Orders/Index', [
        'orders'  => $orders,
        'filters' => ['q'=>$q,'scope'=>$scope],
        'token'   => $request->query('token'),
    ]);
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
    public function show(Request $request, $orderUuid)
    {
        $order = Order::where('uuid', $orderUuid)->firstOrFail();
        $order->loadMissing('orderProducts.product');
        $addr = $order->shipping_address_json ? json_decode($order->shipping_address_json, true) : null;

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'uuid' => $order->uuid,
                'customer_email' => $order->customer_email,
                'currency' => $order->currency,
                'total_price' => (float) $order->total_price,
                'shipping_method_label' => $order->shipping_method_label,
                'shipping_total' => (float) $order->shipping_total,
                'payment_status' => $order->payment_status,
                'shipping_status' => $order->shipping_status,
                'tracking_number' => $order->tracking_number,
                'paid_at' => optional($order->paid_at)->toDateTimeString(),
                'shipped_at' => optional($order->shipped_at)->toDateTimeString(),
                'order_products' => $order->orderProducts->map(fn($op)=>[
                    'id'=>$op->id,'quantity'=>(int)$op->quantity,'price'=>(float)$op->price,
                    'product'=>['id'=>$op->product?->id,'name'=>$op->product?->name ?? 'Produit'],
                    'customization'=>$op->customization,
                ])->values(),
            ],
            'addr'  => $addr,
            'token' => $request->query('token'),
        ]);
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
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
