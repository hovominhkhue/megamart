<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User, Order};

class OrderSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_cannot_view_someone_elses_order()
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $order = Order::create([
            'user_id'     => $u1->id,
            'total_cents' => 1500,
            'status'      => 'paid',
            'placed_at'   => now(),
        ]);

        $this->actingAs($u2)
            ->get(route('orders.show', $order->id))
            ->assertForbidden(); 
    }
}