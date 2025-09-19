<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function homepage_lists_products()
    {
        $p = Product::factory()->create(['name' => 'Super Produit']);
        $this->get('/')->assertOk()->assertSee('Super Produit');
    }

    /** @test */
    public function product_detail_page_displays_info()
    {
        $p = Product::factory()->create(['name'=>'Mon Item', 'stock'=>7]);
        $this->get('/products/'.$p->slug)
            ->assertOk()
            ->assertSee('Mon Item')
            ->assertSee((string)$p->stock);
    }
}