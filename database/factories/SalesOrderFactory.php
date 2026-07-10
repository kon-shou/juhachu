<?php

namespace Database\Factories;

use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'SO-'.fake()->unique()->numerify('########-####'),
            'customer_id' => Customer::factory(),
            'status' => SalesOrderStatus::Draft,
            'order_date' => fake()->dateTimeBetween('-2 months'),
            'total' => 0,
            'note' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }
}
