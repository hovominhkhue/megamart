<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    /**
     * Page récap + clé Stripe publique pour Elements
     */
    public function show() {
        $cart = \App\Models\Cart::query()
            ->where('status', 'draft')
            ->with('items.product')
            ->where(function ($q) {
                $q->where('session_id', session()->getId());
                if (auth()->check()) {
                    $q->orWhere('user_id', auth()->id());
                }
            })
            ->firstOrFail();

        // ➜ ajoute ces 3 variables attendues par la vue
        $total     = $cart->items->sum(fn($it) => $it->qty * $it->unit_price_cents); // en centimes
        $currency  = env('STRIPE_CURRENCY', 'eur');
        $stripeKey = config('services.stripe.key') ?? env('STRIPE_KEY');

        return view('checkout.show', compact('cart', 'total', 'currency', 'stripeKey'));
    }

    /**
     * Crée un PaymentIntent Stripe et renvoie le client_secret (AJAX)
     */
    public function paymentIntent(Request $request)
    {
        $cart = Cart::where('session_id', session()->getId())
            ->where('status', 'draft')
            ->with('items.product')
            ->firstOrFail();

        $total = $cart->items->sum(fn ($it) => $it->qty * $it->unit_price_cents);
        if ($total <= 0) {
            return response()->json(['error' => 'Panier vide'], 422);
        }

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret') ?? env('STRIPE_SECRET'));

        $pi = $stripe->paymentIntents->create([
            'amount'   => $total, // en cents
            'currency' => env('STRIPE_CURRENCY', 'eur'),
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'user_id' => (string) auth()->id(),
                'cart_id' => (string) $cart->id,
            ],
        ]);

        return response()->json([
            'clientSecret'     => $pi->client_secret,
            'paymentIntentId'  => $pi->id,
        ]);
    }

    /**
     * Finalise le paiement après confirmation Stripe côté front :
     * - vérifie le PaymentIntent
     * - verrouille le panier
     * - vérifie le stock
     * - crée commande + lignes
     * - enregistre le paiement (si modèle Payment présent)
     * - clôture le panier
     */
    public function finalize(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret') ?? env('STRIPE_SECRET'));
        $pi = $stripe->paymentIntents->retrieve($request->payment_intent_id);

        if (!in_array($pi->status, ['succeeded', 'processing'])) {
            return back()->withErrors(['payment' => 'Paiement non confirmé. Statut: ' . $pi->status]);
        }

        return DB::transaction(function () use ($pi) {
            $cart = Cart::where('session_id', session()->getId())
                ->where('status', 'draft')
                ->with('items.product')
                ->lockForUpdate()
                ->firstOrFail();

            // Vérif quantités & stock
            foreach ($cart->items as $it) {
                if ($it->qty <= 0) {
                    return back()->withErrors(['stock' => 'Quantité invalide dans le panier.']);
                }
                if ($it->qty > $it->product->stock) {
                    return back()->withErrors(['stock' => "Stock insuffisant pour {$it->product->name}"]);
                }
            }

            // Décrément stock + calcul total sécurisé
            $total = 0;
            foreach ($cart->items as $it) {
                $it->product->decrement('stock', $it->qty);
                $total += $it->qty * $it->unit_price_cents;
            }

            // Crée la commande
            $order = Order::create([
                'user_id'     => auth()->id(),
                'total_cents' => $total,
                'status'      => $pi->status === 'succeeded' ? 'paid' : 'processing',
                'placed_at'   => now(),
            ]);

            foreach ($cart->items as $it) {
                OrderItem::create([
                    'order_id'         => $order->id,
                    'product_id'       => $it->product_id,
                    'qty'              => $it->qty,
                    'unit_price_cents' => $it->unit_price_cents,
                ]);
            }

            // Enregistre le paiement si le modèle Payment existe
            if (class_exists('App\\Models\\Payment')) {
                \App\Models\Payment::create([
                    'order_id'     => $order->id,
                    'provider'     => 'stripe',
                    'provider_ref' => $pi->id,
                    'amount_cents' => (int) $pi->amount,
                    'status'       => $pi->status,
                    'payload'      => $pi->toArray(),
                ]);
            }

            // Clôture le panier
            $cart->update(['status' => 'ordered']);

            return redirect()
                ->route('orders.show', $order->id)
                ->with('status', 'Paiement réussi ✅');
        });
    }

    /**
     * Alias de compatibilité si tes routes POST pointent encore sur /checkout (process)
     */
    public function process(Request $request)
    {
        return $this->finalize($request);
    }
}