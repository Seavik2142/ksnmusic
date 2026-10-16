<?php

namespace Database\Factories;

use App\Enums\Store\PurchasableType;
use App\Models\Purchase;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Purchase> */
class PurchaseFactory extends Factory
{
    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'purchasable_type' => PurchasableType::SONG->value,
            'purchasable_id' => Song::factory(),
        ];
    }
}
