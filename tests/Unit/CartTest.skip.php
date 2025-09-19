<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Cart;

class CartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_can_add_product_to_cart_and_update_quantity()
    {
        $p = Product::factory()->create(['price_cents'=>999]);

        // 1) Ajoute (crée la session + le panier)
        $this->post(route('cart.add', $p))->assertRedirect();

        $cart = Cart::where('status','draft')->first();
        $this->assertNotNull($cart);
        $this->assertEquals(1, $cart->items()->count());

        // 2) Met à jour (même session, pas de withSession([]))
        $this->post(route('cart.update', $p), ['qty'=>3])->assertRedirect();

        $item = CartItem::first();
        $this->assertEquals(3, $item->qty);
        $this->assertEquals(999, $item->unit_price_cents);
    }

    /** @test */
    public function remove_item_from_cart()
    {
        $p = Product::factory()->create();

        // Ajoute
        $this->post(route('cart.add', $p))->assertRedirect();

        // Supprime (même session)
        $this->delete(route('cart.remove', $p))->assertRedirect();

        $this->assertDatabaseCount('cart_items', 0);
    }
}
