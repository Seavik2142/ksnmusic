<?php

namespace App\Http\Integrations\PayPal;

use Illuminate\Container\Attributes\Config;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class PayPalConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    public function __construct(
        #[Config('services.paypal.sandbox')]
        private readonly bool $sandbox = true,
    ) {}

    public function resolveBaseUrl(): string
    {
        return $this->sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }
}
