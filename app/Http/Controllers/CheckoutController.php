<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function show() {
        $cart = \App\Models\Cart::where('session_id', session()->getId())
            ->where('status','draft')->with('items.product')->firstOrFail();
        return view('checkout.show', compact('cart'));
    }

    public function process() {
        $cart = \App\Models\Cart::where('session_id', session()->getId())
            ->where('status','draft')->with('items.product')->firstOrFail();

        // Vérif stock minimal
        foreach ($cart->items as $it) {
            if ($it->qty > $it->product->stock) {
                return back()->withErrors(['stock'=>"Stock insuffisant pour {$it->product->name}"]);
            }
        }

        // Décrémenter le stock + calcul total
        $total = 0;
        foreach ($cart->items as $it) {
            $it->product->decrement('stock', $it->qty);
            $total += $it->qty * $it->unit_price_cents;
        }

        $order = \App\Models\Order::create([
            'user_id' => auth()->id(),
            'total_cents' => $total,
            'status' => 'paid',           // payé (fictif)
            'placed_at' => now(),
        ]);

        foreach ($cart->items as $it) {
            \App\Models\OrderItem::create([
                'order_id'=>$order->id,
                'product_id'=>$it->product_id,
                'qty'=>$it->qty,
                'unit_price_cents'=>$it->unit_price_cents,
            ]);
        }

        $cart->update(['status'=>'ordered']);
        return redirect()->route('home')->with('status','Commande confirmée');
    }
}