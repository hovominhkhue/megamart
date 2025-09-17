<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class CheckoutController extends Controller
{
    public function show() {
        $cart = \App\Models\Cart::where('session_id', session()->getId())
            ->where('status','draft')
            ->with('items.product')
            ->firstOrFail();

        return view('checkout.show', compact('cart'));
    }

    public function process() {
        return DB::transaction(function () {
            $cart = \App\Models\Cart::where('session_id', session()->getId())
                ->where('status','draft')
                ->with('items.product')
                ->lockForUpdate() // ← évite une modif concurrente pendant le paiement
                ->firstOrFail();

            // Ici on NE décrémente PAS le stock (déjà réservé dans le panier).
            // On peut juste valider les quantités
            foreach ($cart->items as $it) {
                if ($it->qty <= 0) {
                    return back()->withErrors(['stock' => 'Quantité invalide dans le panier.']);
                }
            }

            // Calcul du total
            $total = 0;
            foreach ($cart->items as $it) {
                $total += $it->qty * $it->unit_price_cents;
            }

            $order = \App\Models\Order::create([
                'user_id'     => auth()->id(),
                'total_cents' => $total,
                'status'      => 'paid',   // démo
                'placed_at'   => now(),
            ]);

            foreach ($cart->items as $it) {
                \App\Models\OrderItem::create([
                    'order_id'         => $order->id,
                    'product_id'       => $it->product_id,
                    'qty'              => $it->qty,
                    'unit_price_cents' => $it->unit_price_cents,
                ]);
            }

            $cart->update(['status' => 'ordered']);

            return redirect()->route('orders.show', $order)  ->with('status','Commande confirmée!');
        });
    }
}