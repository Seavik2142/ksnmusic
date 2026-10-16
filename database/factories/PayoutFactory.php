<?php

namespace Database\Factories;

use App\Models\Payout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payout> */
class PayoutFactory extends Factory
{
    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'seller_id' => User::factory(),
            'amount_cents' => fake()->numberBetween(100, 10_000),
            'paid_at' => now(),
        ];
    }
}
