<?php

namespace Database\Factories;

use App\Enums\PurchaseOrderStatus;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'PO-'.fake()->unique()->numerify('########-####'),
            'supplier_id' => Supplier::factory(),
            'status' => PurchaseOrderStatus::Draft,
            'order_date' => fake()->dateTimeBetween('-2 months'),
            'total' => 0,
            'note' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }
}
