<?php

namespace App\Http\Integrations\PayPal\Requests;

use App\Models\Order;
use Saloon\Contracts\Authenticator;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;
use SensitiveParameter;

class CreateOrderRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        #[SensitiveParameter]
        private readonly string $accessToken,
        private readonly Order $order,
        private readonly string $returnUrl,
        private readonly string $cancelUrl,
        private readonly string $brandName,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/v2/checkout/orders';
    }

    protected function defaultAuth(): Authenticator
    {
        return new TokenAuthenticator($this->accessToken);
    }

    /** @inheritdoc */
    protected function defaultHeaders(): array
    {
        return ['PayPal-Request-Id' => "create-{$this->order->id}"];
    }

    /** @inheritdoc */
    protected function defaultBody(): array
    {
        return [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $this->order->id,
                'custom_id' => $this->order->id,
                'description' => "{$this->brandName} purchase",
                'amount' => [
                    'currency_code' => $this->order->currency,
                    'value' => $this->order->amountInMajorUnits(),
                ],
            ]],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'brand_name' => $this->brandName,
                        'shipping_preference' => 'NO_SHIPPING',
                        'user_action' => 'PAY_NOW',
                        'return_url' => $this->returnUrl,
                        'cancel_url' => $this->cancelUrl,
                    ],
                ],
            ],
        ];
    }
}
