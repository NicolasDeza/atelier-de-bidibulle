<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CheckoutAddressController;
use App\Http\Controllers\CheckoutPaymentController;
use App\Http\Controllers\CartCheckoutController;
use App\Http\Controllers\StripeWebhookController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Routes pour les produits
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::middleware(['auth'])->group(function () {
Route::get('/favoris', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle/{productId}', [WishlistController::class, 'toggle'])
    ->middleware('auth')
    ->where('productId', '[0-9]+')
    ->name('wishlist.toggle');

});

// Routes pour le panier
Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::middleware(['auth'])->group(function () {
    Route::put('/cart/update/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{cartItem}', [CartController::class, 'destroy'])->name('cart.remove');

});
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// PANIER INVITÉ (SESSION)
Route::put('/panier-session/{key}', [CartController::class, 'updateSession'])->name('cart.session.update');
Route::delete('/panier-session/{key}', [CartController::class, 'removeSession'])->name('cart.session.remove');
// Route::delete('/panier-session', [CartController::class, 'clearSession'])->name('cart.session.clear');


//! CHECKOUT

// 1) Du panier → créer la commande
Route::post('/cart/checkout', [CartCheckoutController::class, 'createOrderFromCart'])
    ->name('cart.checkout');

// 2) Afficher Stripe Checkout (show = compat avec ton code actuel)
Route::get('/checkout/payment/{order}', [CheckoutPaymentController::class, 'startAndRedirect'])
    ->name('checkout.payment.show');

// 3) Variante "start" (peut servir si on change le flux)
Route::get('/checkout/payment/start/{order}', [CheckoutPaymentController::class, 'startAndRedirect'])
    ->name('checkout.payment.start');

// 4) Retour après succès ou annulation - CORRIGÉ
Route::get('/checkout/return/{order}', [CheckoutPaymentController::class, 'return'])
    ->name('checkout.payment.return');


    //! ROUTE AVIS
// Routes pour les avis
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');


// Routes pour les catégories
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categorie/{category}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/categorie/{category}', [CategoryController::class, 'show'])->name('categories.show');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        // ✅ Voir les commandes sur le dashboard
        $orders = \App\Models\Order::with(['orderProducts.product'])
            ->latest()
            ->take(10)
            ->get();
            
        return Inertia::render('Dashboard', [
            'orders' => $orders->map(fn($order) => [
                'id' => $order->id,
                'uuid' => $order->uuid,
                'payment_status' => $order->payment_status,
                'total_price' => (float) $order->total_price,
                'paid_at' => $order->paid_at?->format('d/m/Y H:i'),
                'items_count' => $order->orderProducts->count(),
            ])
        ]);
    })->name('dashboard');
});


