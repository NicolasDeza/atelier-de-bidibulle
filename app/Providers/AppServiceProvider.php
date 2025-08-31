<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use App\Models\Review;
use App\Policies\ReviewPolicy;
use App\Models\Order;
use Inertia\Inertia;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
 public function boot(): void
{
    Gate::policy(Review::class, ReviewPolicy::class);

    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }

    // Partager le compteur du panier avec toutes les vues Inertia
    Inertia::share('cartCount', function () {
        if (auth()->check()) {
            $cart = auth()->user()->cart;
            return $cart ? $cart->cartItems()->sum('quantity') : 0;
        }

        $sessionCart = session()->get('cart', []);
        return collect($sessionCart)->sum('quantity');
    });
}
}

