<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use App\Models\{Cart, CartItem, Product};

class CartController extends Controller
{
    protected function mergeCarts(Cart $into, Cart $from): void
    {
        if ($into->id === $from->id) return;

        $from->load('items');
        foreach ($from->items as $it) {
            $ex = CartItem::firstOrNew([
                'cart_id'    => $into->id,
                'product_id' => $it->product_id,
            ]);
            $ex->qty = ($ex->exists ? $ex->qty : 0) + $it->qty;
            $ex->unit_price_cents = $it->unit_price_cents; // snapshot
            $ex->save();
        }
        $from->delete();
    }

    protected function currentCart(): Cart
    {
        $sid = session()->getId();

        // 1) Si connecté, prioriser le panier 'draft' de l'utilisateur
        $cart = auth()->check()
            ? Cart::where('user_id', auth()->id())->where('status', 'draft')->first()
            : null;

        // 2) Sinon, tenter via le cookie cart_id (survit au login)
        if (!$cart && ($cookieId = request()->cookie('cart_id'))) {
            $cart = Cart::whereKey($cookieId)->where('status', 'draft')->first();
        }

        // 3) En dernier recours, par session_id (cas invité « tout neuf »)
        if (!$cart) {
            $cart = Cart::whereNull('user_id')
                ->where('session_id', $sid)
                ->where('status', 'draft')
                ->first();
        }

        // 4) Créer si rien trouvé
        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $sid,
                'user_id'    => auth()->id(),
                'status'     => 'draft',
            ]);
        }

        // 5) Si invité → connecté, on peut avoir DEUX paniers : fusionner
        if (auth()->check()) {
            // Existe-t-il un autre panier 'guest' lié au cookie ?
            if ($cookieId = request()->cookie('cart_id')) {
                $guest = Cart::whereKey($cookieId)->where('status', 'draft')->first();
                if ($guest && $guest->id !== $cart->id) {
                    $this->mergeCarts($cart, $guest);
                }
            }
            // Attacher l’utilisateur si manquant
            if (!$cart->user_id) $cart->user_id = auth()->id();
        }

        // 6) Synchroniser le session_id courant
        if ($cart->session_id !== $sid) $cart->session_id = $sid;
        $cart->save();

        // 7) (Re)poser le cookie cart_id (30 jours)
        Cookie::queue('cart_id', (string) $cart->id, 60 * 24 * 30);

        return $cart;
    }

    public function show() {
        $cart = $this->currentCart()->load('items.product');
        return view('cart.show', compact('cart'));
    }

    public function add(Product $product, Request $request)
    {
        $qty = max(1, (int) $request->input('qty', 1));
        $cart = $this->currentCart();

        return DB::transaction(function () use ($cart, $product, $qty) {
            // Quantité déjà dans le panier pour ce produit
            $existing = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->value('qty') ?? 0;

            $want = $existing + $qty;

            // Vérif : ne pas dépasser le stock disponible actuel
            if ($want > $product->stock) {
                $maxAdd = max(0, $product->stock - $existing);
                return back()->withErrors([
                    'stock' => "Stock insuffisant pour {$product->name}. Vous avez déjà {$existing} au panier. Ajout possible : {$maxAdd}."
                ]);
            }

            // Insérer ou mettre à jour la ligne du panier
            $item = CartItem::firstOrNew([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
            ]);

            // Snapshot du prix seulement à la première insertion
            if (!$item->exists || !$item->unit_price_cents) {
                $item->unit_price_cents = $product->price_cents;
            }

            $item->qty = $want;
            $item->save();

            return back()->with('status', 'Produit ajouté au panier.');
        });
    }

    public function update(Product $product, Request $request)
    {
        $qty = max(1, (int) $request->input('qty', 1));
        $cart = $this->currentCart();

        if ($qty > $product->stock) {
            return back()->withErrors([
                'stock' => "Stock insuffisant pour {$product->name}. Disponible : {$product->stock}."
            ]);
        }

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        $item->qty = $qty;
        $item->save();

        return back();
    }

    public function remove(Product $product)
    {
        $cart = $this->currentCart();

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            DB::transaction(function () use ($item, $product) {
                $product->increment('stock', $item->qty);
                $item->delete();
            });
        }

        return back()->with('status', 'Produit retiré du panier.');
    }
}