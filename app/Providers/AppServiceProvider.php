<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Cart;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $sid = session()->getId();

            $base = Cart::query()
                ->where('session_id', $sid)
                ->where('status', 'draft');

            if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'qty')) {
                // Somme des quantités si la colonne existe
                $cart = $base->withSum('items as qty_sum', 'qty')->first();
                $count = (int) ($cart->qty_sum ?? 0);
            } else {
                // Fallback : nombre de lignes du panier
                $cart = $base->withCount('items')->first();
                $count = (int) ($cart->items_count ?? 0);
            }

            $view->with('cartCount', $count);
        });
    }
}