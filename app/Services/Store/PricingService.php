<?php

namespace App\Services\Store;

use App\Models\Album;
use App\Models\Song;

class PricingService
{
    /**
     * Set or clear the price. A null or zero price makes the item free.
     */
    public function setPrice(Song|Album $item, ?int $priceCents): void
    {
        $item->price_cents = $priceCents ?: null;
        $item->save();
    }
}
