<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\User;

class CheckoutAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_is_redirected_to_login_on_checkout()
    {
        $this->get(route('checkout.show'))->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_with_items_can_view_checkout_page()
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();
        $p = Product::factory()->create(['price_cents'=>1500]);

        // Remplir le panier AVANT de se connecter
        $this->post(route('cart.add', $p))->assertRedirect();

        // Se connecter sans changer de session
        $this->actingAs($user)
            ->get(route('checkout.show'))
            ->assertOk()
            ->assertSee('Récapitulatif');
    }
}