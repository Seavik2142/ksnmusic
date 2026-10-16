<?php

namespace App\Http\Integrations\PayWay\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CheckTransactionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $requestTime,
        private readonly string $merchantId,
        private readonly string $transactionId,
        private readonly string $hash,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/api/payment-gateway/v1/payments/check-transaction-2';
    }

    /** @inheritdoc */
    protected function defaultBody(): array
    {
        return [
            'req_time' => $this->requestTime,
            'merchant_id' => $this->merchantId,
            'tran_id' => $this->transactionId,
            'hash' => $this->hash,
        ];
    }
}
