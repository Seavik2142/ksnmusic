<?php

namespace App\Enums\Store;

enum PaymentVerificationStatus: string
{
    case PAID = 'paid';
    case PENDING = 'pending';
    case FAILED = 'failed';
}
