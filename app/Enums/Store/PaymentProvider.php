<?php

namespace App\Enums\Store;

enum PaymentProvider: string
{
    case PAYWAY = 'payway';
    case PAYPAL = 'paypal';
}
