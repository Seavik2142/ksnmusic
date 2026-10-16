<?php

namespace App\Services\Payments;

use App\Enums\Store\PaymentProvider;
use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\PayPal\PayPalGateway;
use App\Services\Payments\PayWay\PayWayGateway;

class PaymentGatewayResolver
{
    public function __construct(
        private readonly PayPalGateway $payPalGateway,
        private readonly PayWayGateway $payWayGateway,
    ) {}

    public function resolve(PaymentProvider $provider): PaymentGateway
    {
        return match ($provider) {
            PaymentProvider::PAYPAL => $this->payPalGateway,
            PaymentProvider::PAYWAY => $this->payWayGateway,
        };
    }

    /** @return array<int, PaymentProvider> */
    public function configuredProviders(): array
    {
        return array_values(array_filter(PaymentProvider::cases(), fn (PaymentProvider $provider): bool => $this->resolve(
            $provider,
        )->isConfigured()));
    }
}
