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
            // Pendant l’install/migrations, évite les erreurs
            if (!Schema::hasTable('carts')) {
                $view->with('cartCount', 0);
                return;
            }

            $q = Cart::query()->where('status', 'draft');

            if (auth()->check()) {
                // Priorité : panier draft de l'utilisateur connecté
                $q->where('user_id', auth()->id());
            } elseif ($cid = request()->cookie('cart_id')) {
                // Sinon : panier référencé par cookie (survit au login)
                $q->whereKey($cid);
            } else {
                // Sinon : panier invité lié à la session courante
                $q->where('session_id', session()->getId());
            }

            // Si la table/colonne existe, on somme les quantités ; sinon fallback au nombre de lignes
            if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'qty')) {
                $cart = $q->withSum('items as qty_sum', 'qty')->first();
                $count = (int) ($cart->qty_sum ?? 0);
            } else {
                $cart = $q->withCount('items')->first();
                $count = (int) ($cart->items_count ?? 0);
            }

            $view->with('cartCount', $count);
        });
    }
}