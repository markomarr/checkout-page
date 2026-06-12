<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);
        $price = fake()->numberBetween(10000, 500000);

        return [
            'order_code' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
            'customer_name' => fake()->name(),
            'customer_phone' => '6281234567890',
            'customer_address' => fake()->address(),
            'courier' => fake()->randomElement(['jne', 'jnt', 'sicepat']),
            'payment_method' => fake()->randomElement(['bank_transfer', 'qris']),
            'payment_proof_path' => 'payments/1/dummy.jpg',
            'quantity' => $quantity,
            'total_price' => $price * $quantity,
            'status' => 'pending',
        ];
    }
}
