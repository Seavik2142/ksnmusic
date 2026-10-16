<?php

namespace App\Http\Integrations\PayPal\Requests;

use Saloon\Contracts\Authenticator;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Request;
use SensitiveParameter;

class GetOrderRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        #[SensitiveParameter]
        private readonly string $accessToken,
        private readonly string $payPalOrderId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/v2/checkout/orders/{$this->payPalOrderId}";
    }

    protected function defaultAuth(): Authenticator
    {
        return new TokenAuthenticator($this->accessToken);
    }
}
