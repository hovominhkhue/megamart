<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $name = fake()->unique()->words(3, true);
        return [
            'category_id' => null,
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000,9999),
            'price_cents' => fake()->numberBetween(990, 99900),
            'stock' => fake()->numberBetween(0, 50),
            'cover_image' => null,
            'description' => fake()->sentence(12),
        ];
    }
}
