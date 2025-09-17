<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Cart, CartItem, Product};

class CartController extends Controller
{
    protected function currentCart(): \App\Models\Cart {
        $sid = session()->getId();
        $cart = Cart::firstOrCreate(['session_id' => $sid], [
            'user_id' => auth()->id(),
            'status'  => 'draft',
        ]);

        if (auth()->check() && !$cart->user_id) {
            $cart->user_id = auth()->id();
            $cart->save();
        }
        return $cart;
    }

    public function show() {
        $cart = $this->currentCart()->load('items.product');
        return view('cart.show', compact('cart'));
    }

    // Réservation du stock à l'ajout
    public function add(Product $product, Request $request)
    {
        $qty = max(1, (int) $request->input('qty', 1));

        // Vérifier le stock disponible
        if ($product->stock < $qty) {
            return back()->withErrors(['stock' => "Stock insuffisant pour {$product->name}"]);
        }

        $cart = $this->currentCart();

        DB::transaction(function () use ($cart, $product, $qty) {
            $item = CartItem::firstOrNew([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
            ]);

            $item->qty = ($item->exists ? $item->qty : 0) + $qty;
            $item->unit_price_cents = $item->unit_price_cents ?: $product->price_cents;
            $item->save();

            // Réserver le stock
            $product->decrement('stock', $qty);
        });

        return back()->with('status', 'Produit ajouté au panier.');
    }

    // Mise à jour de quantité avec ajustement du stock réservé
    public function update(Request $request, Product $product)
    {
        $qty  = max(1, (int) $request->input('qty', 1));
        $cart = $this->currentCart();

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        DB::transaction(function () use ($item, $product, $qty) {
            $old  = $item->qty;
            $diff = $qty - $old; // >0 on réserve plus, <0 on libère

            if ($diff > 0) {
                if ($product->stock < $diff) {
                    abort(400, "Stock insuffisant pour {$product->name}");
                }
                $product->decrement('stock', $diff);
            } elseif ($diff < 0) {
                $product->increment('stock', -$diff);
            }

            $item->qty = $qty;
            if (!$item->unit_price_cents) {
                $item->unit_price_cents = $product->price_cents;
            }
            $item->save();
        });

        return back()->with('status', 'Quantité mise à jour.');
    }

    // Retrait d’un article : on remet le stock
    public function remove(Product $product)
    {
        $cart = $this->currentCart();

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            DB::transaction(function () use ($item, $product) {
                // Rendre le stock réservé
                $product->increment('stock', $item->qty);
                $item->delete();
            });
        }

        return back()->with('status', 'Produit retiré du panier.');
    }
}