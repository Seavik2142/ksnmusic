<?php

namespace Database\Factories;

use App\Enums\Store\OrderStatus;
use App\Enums\Store\PaymentProvider;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => OrderStatus::PENDING,
            'provider' => PaymentProvider::PAYPAL,
            'amount_cents' => fake()->numberBetween(50, 2_000),
            'currency' => 'USD',
        ];
    }

    public function paid(): self
    {
        return $this->state(static fn (): array => [
            'status' => OrderStatus::PAID,
            'paid_at' => now(),
        ]);
    }
}
