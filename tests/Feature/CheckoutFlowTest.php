<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User, Product, Cart, Order, OrderItem};

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_order_and_see_order_details_without_visiting_checkout_page()
    {
        $this->startSession();

        $user = User::factory()->create();
        $this->actingAs($user);

        $p = Product::factory()->create(['stock' => 5, 'price_cents' => 1200]);

        // 1) Ajout au panier (même session & user)
        $this->post(route('cart.add', $p), ['qty' => 2])->assertRedirect();

        // 2) Récupère le panier courant (draft) et calcule le total
        $cart = Cart::where('status', 'draft')
            ->where('session_id', session()->getId())
            ->with('items')
            ->firstOrFail();

        $total = $cart->items->sum(fn ($it) => $it->qty * $it->unit_price_cents);

        // 3) SIMULATION paiement : créer la commande + lignes et clôturer le panier
        $order = Order::create([
            'user_id'     => $user->id,
            'total_cents' => $total,
            'status'      => 'paid',
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

        $cart->update(['status' => 'ordered']);

        // 4) Vérifs DB
        $this->assertDatabaseHas('orders', [
            'user_id'     => $user->id,
            'total_cents' => 2400,
            'status'      => 'paid',
        ]);
        $this->assertDatabaseHas('carts', ['id' => $cart->id, 'status' => 'ordered']);

        // 5) L’utilisateur voit bien le détail de sa commande
        $this->get(route('orders.show', $order->id))
            ->assertOk()
            ->assertSee('Commande #'.$order->id);
    }
}