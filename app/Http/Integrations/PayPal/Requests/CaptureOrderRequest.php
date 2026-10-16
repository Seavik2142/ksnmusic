<?php

namespace App\Http\Integrations\PayPal\Requests;

use Saloon\Contracts\Authenticator;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;
use SensitiveParameter;

class CaptureOrderRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        #[SensitiveParameter]
        private readonly string $accessToken,
        private readonly string $payPalOrderId,
        private readonly string $idempotencyKey,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/v2/checkout/orders/{$this->payPalOrderId}/capture";
    }

    protected function defaultAuth(): Authenticator
    {
        return new TokenAuthenticator($this->accessToken);
    }

    /** @inheritdoc */
    protected function defaultHeaders(): array
    {
        return ['PayPal-Request-Id' => $this->idempotencyKey];
    }

    /** @inheritdoc */
    protected function defaultBody(): array
    {
        return [];
    }
}
