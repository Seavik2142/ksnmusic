<?php

namespace App\Values\Store;

use App\Enums\Store\PaymentVerificationStatus;

/**
 * What a payment provider reports about an order's payment, as confirmed by a server-to-server call.
 */
final readonly class PaymentVerification
{
    private function __construct(
        public PaymentVerificationStatus $status,
        public ?int $amountCents,
        public ?string $currency,
    ) {}

    public static function make(
        PaymentVerificationStatus $status,
        ?int $amountCents = null,
        ?string $currency = null,
    ): self {
        return new self(status: $status, amountCents: $amountCents, currency: $currency);
    }

    public static function pending(): self
    {
        return self::make(PaymentVerificationStatus::PENDING);
    }

    public static function failed(): self
    {
        return self::make(PaymentVerificationStatus::FAILED);
    }
}
