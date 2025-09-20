<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use App\Models\{Cart, CartItem, Product};

class CartController extends Controller
{
    // ---------- Utils ----------

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

        $cart = auth()->check()
            ? Cart::where('user_id', auth()->id())->where('status', 'draft')->first()
            : null;

        if (!$cart && ($cookieId = request()->cookie('cart_id'))) {
            $cart = Cart::whereKey($cookieId)->where('status', 'draft')->first();
        }

        if (!$cart) {
            $cart = Cart::whereNull('user_id')
                ->where('session_id', $sid)
                ->where('status', 'draft')
                ->first();
        }

        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $sid,
                'user_id'    => auth()->id(),
                'status'     => 'draft',
            ]);
        }

        if (auth()->check()) {
            if ($cookieId = request()->cookie('cart_id')) {
                $guest = Cart::whereKey($cookieId)->where('status', 'draft')->first();
                if ($guest && $guest->id !== $cart->id) {
                    $this->mergeCarts($cart, $guest);
                }
            }
            if (!$cart->user_id) $cart->user_id = auth()->id();
        }

        if ($cart->session_id !== $sid) $cart->session_id = $sid;
        $cart->save();

        Cookie::queue('cart_id', (string) $cart->id, 60 * 24 * 30);

        return $cart;
    }

    protected function cartCount(Cart $cart): int
    {
        return (int) CartItem::where('cart_id', $cart->id)->sum('qty');
    }

    protected function cartTotals(Cart $cart): array
    {
        $items = $cart->loadMissing('items.product')->items;
        $total_cents = $items->sum(fn ($i) => $i->qty * $i->unit_price_cents);
        return [
            'total_cents' => $total_cents,
            'total_eur'   => number_format($total_cents / 100, 2, ',', ' '),
        ];
    }

    protected function respond(Request $request, Cart $cart, string $message = 'OK', int $status = 200)
    {
        $count = $this->cartCount($cart);
        session()->put('cart_count', $count);

        if ($request->expectsJson()) {
            $totals = $this->cartTotals($cart);
            return response()->json([
                'ok'         => $status < 400,
                'message'    => $message,
                'cart_count' => $count,
                'totals'     => $totals,
            ], $status);
        }

        return back()->with('status', $message)->with('cart_count', $count);
    }

    protected function respondError(Request $request, string $message, int $status = 422)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => false,
                'message' => $message,
            ], $status);
        }
        return back()->withErrors(['stock' => $message]);
    }

    // ---------- Actions ----------

    public function show()
    {
        $cart = $this->currentCart()->load('items.product');

        // Total à afficher en SSR
        $total_cents = $cart->items->sum(fn ($i) => $i->qty * $i->unit_price_cents);

        return view('cart.show', [
            'cart'        => $cart,
            'total_cents' => $total_cents,
        ]);
    }

    public function add(Product $product, Request $request)
    {
        $qty = max(1, (int) $request->input('qty', 1));
        $cart = $this->currentCart();

        return DB::transaction(function () use ($request, $cart, $product, $qty) {
            $existing = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->value('qty') ?? 0;

            $want = $existing + $qty;

            if ($want > $product->stock) {
                $maxAdd = max(0, $product->stock - $existing);
                $msg = "Stock insuffisant pour {$product->name}. Vous avez déjà {$existing} au panier. Ajout possible : {$maxAdd}.";
                return $this->respondError($request, $msg, 422);
            }

            $item = CartItem::firstOrNew([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
            ]);

            if (!$item->exists || !$item->unit_price_cents) {
                $item->unit_price_cents = $product->price_cents; // snapshot au moment de l’ajout
            }

            $item->qty = $want;
            $item->save();

            return $this->respond($request, $cart, 'Produit ajouté au panier.');
        });
    }

    public function update(Product $product, Request $request)
    {
        $qty = max(0, (int) $request->input('qty', 1)); // 0 = suppression
        $cart = $this->currentCart();

        // Si demande de suppression (qty=0)
        if ($qty === 0) {
            CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->delete();

            return $this->respond($request, $cart, 'Produit retiré du panier.');
        }

        if ($qty > $product->stock) {
            $msg = "Stock insuffisant pour {$product->name}. Disponible : {$product->stock}.";
            return $this->respondError($request, $msg, 422);
        }

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if (!$item) {
            // S’il n’y avait pas de ligne, on en crée une directement (utile via +)
            $item = new CartItem([
                'cart_id'          => $cart->id,
                'product_id'       => $product->id,
                'unit_price_cents' => $product->price_cents,
            ]);
        }

        $item->qty = $qty;
        $item->save();

        return $this->respond($request, $cart, 'Quantité mise à jour.');
    }

    public function remove(Product $product, Request $request)
    {
        $cart = $this->currentCart();

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            // ⚠️ On ne touche pas au stock produit ici.
            // La décrémentation/incrémentation de stock doit se faire au checkout.
            $item->delete();
        }

        return $this->respond($request, $cart, 'Produit retiré du panier.');
    }
}