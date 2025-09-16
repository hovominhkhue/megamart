<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    protected function currentCart(): \App\Models\Cart {
        $sid = session()->getId();
        $cart = \App\Models\Cart::firstOrCreate(['session_id'=>$sid], [
            'user_id' => auth()->id(), 'status'=>'draft'
        ]);
        if (auth()->check() && !$cart->user_id) { $cart->user_id = auth()->id(); $cart->save(); }
        return $cart;
    }

    public function show() {
        $cart = $this->currentCart()->load('items.product');
        return view('cart.show', compact('cart'));
    }

    public function add(\App\Models\Product $product) {
        $cart = $this->currentCart();
        $item = \App\Models\CartItem::firstOrNew([
            'cart_id'=>$cart->id, 'product_id'=>$product->id
        ]);
        $item->qty = ($item->exists ? $item->qty : 0) + 1;
        $item->unit_price_cents = $product->price_cents;
        $item->save();

        return back()->with('status','Produit ajouté au panier.');
    }

    public function update(\App\Models\Product $product) {
        $qty = max(1, (int) request('qty',1));
        $cart = $this->currentCart();
        $item = \App\Models\CartItem::where('cart_id',$cart->id)->where('product_id',$product->id)->firstOrFail();
        $item->qty = $qty;
        $item->save();
        return back();
    }

    public function remove(\App\Models\Product $product) {
        $cart = $this->currentCart();
        \App\Models\CartItem::where('cart_id',$cart->id)->where('product_id',$product->id)->delete();
        return back();
    }
}