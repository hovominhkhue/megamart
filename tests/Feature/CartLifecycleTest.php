<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User, Product, Cart};

class CartLifecycleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function new_draft_cart_is_used_after_previous_is_ordered()
    {
        $this->startSession();

        $user = User::factory()->create();
        $p = Product::factory()->create(['stock' => 10, 'price_cents' => 1000]);

        // 1) Crée un panier draft
        $this->post(route('cart.add', $p), ['qty' => 1])->assertRedirect();
        $firstCart = Cart::where('status', 'draft')->firstOrFail();

        // 2) SIMULATION achat : clôturer le panier existant
        $firstCart->update(['status' => 'ordered']);

        // 3) Ajout après achat → doit créer un NOUVEAU cart draft
        $this->post(route('cart.add', $p), ['qty' => 1])->assertRedirect();
        $newDraft = Cart::where('status', 'draft')->latest()->first();

        $this->assertNotNull($newDraft);
        $this->assertNotEquals($firstCart->id, $newDraft->id);
    }
}