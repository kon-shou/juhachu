<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SalesOrderItem>
 */
class SalesOrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sales_order_id' => SalesOrder::factory(),
            'product_id' => Product::factory(),
            'qty' => fake()->numberBetween(1, 20),
            'unit_price' => fake()->numberBetween(100, 10000),
        ];
    }
}
