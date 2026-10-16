<?php

namespace App\Repositories;

use App\Enums\Store\PaymentProvider;
use App\Models\Order;

/** @extends Repository<Order> */
class OrderRepository extends Repository
{
    public function getOneLockedForUpdate(string $id): Order
    {
        return Order::query()->lockForUpdate()->findOrFail($id);
    }

    public function findOneByProviderReference(PaymentProvider $provider, string $providerReference): ?Order
    {
        return Order::query()
            ->where('provider', $provider->value)
            ->where('provider_reference', $providerReference)
            ->first();
    }
}
