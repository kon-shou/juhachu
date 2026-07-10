<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $cost = fake()->numberBetween(100, 5000);

        return [
            'sku' => strtoupper(fake()->unique()->bothify('??-####')),
            'name' => fake()->words(3, true),
            'price' => (int) round($cost * fake()->randomFloat(2, 1.2, 2.5)),
            'cost' => $cost,
            'stock_qty' => fake()->numberBetween(0, 200),
            'supplier_id' => Supplier::factory(),
        ];
    }
}
