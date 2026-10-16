<?php

namespace App\Http\Integrations\PayWay;

use Illuminate\Container\Attributes\Config;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class PayWayConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    public function __construct(
        #[Config('services.payway.sandbox')]
        private readonly bool $sandbox = true,
    ) {}

    public function resolveBaseUrl(): string
    {
        return $this->sandbox ? 'https://checkout-sandbox.payway.com.kh' : 'https://checkout.payway.com.kh';
    }

    public function purchaseUrl(): string
    {
        return $this->resolveBaseUrl() . '/api/payment-gateway/v1/payments/purchase';
    }
}
