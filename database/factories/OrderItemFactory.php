<?php

namespace Database\Factories;

use App\Enums\Store\PurchasableType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderItem> */
class OrderItemFactory extends Factory
{
    /** @inheritdoc */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'purchasable_type' => PurchasableType::SONG->value,
            'purchasable_id' => Song::factory(),
            'amount_cents' => 100,
            'commission_cents' => 20,
            'seller_earning_cents' => 80,
        ];
    }
}
