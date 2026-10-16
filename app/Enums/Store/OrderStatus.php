<?php

namespace App\Enums\Store;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function isFinal(): bool
    {
        return $this !== self::PENDING;
    }
}
