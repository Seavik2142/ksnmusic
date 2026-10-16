<?php

namespace App\Services\Payments\PayWay;

use App\Enums\Store\PaymentProvider;
use App\Enums\Store\PaymentVerificationStatus;
use App\Http\Integrations\PayWay\PayWayConnector;
use App\Http\Integrations\PayWay\Requests\CheckTransactionRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Song;
use App\Services\Payments\Contracts\PaymentGateway;
use App\Values\Store\PaymentInitiation;
use App\Values\Store\PaymentVerification;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Arr;
use SensitiveParameter;
use Throwable;

class PayWayGateway implements PaymentGateway
{
    /** PayWay accepts transaction IDs of at most 20 characters. */
    private const int TRANSACTION_ID_LENGTH = 20;

    private const int APPROVED_STATUS_CODE = 0;
    private const array FAILED_STATUS_CODES = [3, 4, 7];

    /** @param array<string, string> $paymentOptions */
    public function __construct(
        private readonly PayWayConnector $connector,
        private readonly PayWaySigner $signer,
        #[Config('services.payway.merchant_id')]
        private readonly ?string $merchantId = null,
        #[Config('services.payway.api_key')]
        #[SensitiveParameter]
        private readonly ?string $apiKey = null,
        #[Config('services.payway.payment_options')]
        private readonly array $paymentOptions = [],
    ) {}

    public function provider(): PaymentProvider
    {
        return PaymentProvider::PAYWAY;
    }

    public function isConfigured(): bool
    {
        return (bool) $this->merchantId && (bool) $this->apiKey;
    }

    public function initiate(Order $order, ?string $paymentMethod = null): PaymentInitiation
    {
        $order->update(['provider_reference' => self::transactionIdFor($order)]);
        $orderPage = client_url("store/orders/{$order->id}");

        $fields = array_filter(
            [
                'req_time' => now('UTC')->format('YmdHis'),
                'merchant_id' => (string) $this->merchantId,
                'tran_id' => $order->provider_reference,
                'amount' => $order->amountInMajorUnits(),
                'items' => base64_encode(json_encode(self::describeItems($order), JSON_THROW_ON_ERROR)),
                'email' => (string) $order->user?->email,
                'payment_option' => (string) Arr::get($this->paymentOptions, (string) $paymentMethod, ''),
                'return_url' => base64_encode(route('store.payway.callback')),
                'cancel_url' => $orderPage,
                'continue_success_url' => $orderPage,
                'currency' => $order->currency,
            ],
            static fn (string $value): bool => $value !== '',
        );

        $fields['hash'] = $this->signer->signPurchase($fields);

        return PaymentInitiation::formPost($this->connector->purchaseUrl(), $fields);
    }

    public function verify(Order $order): PaymentVerification
    {
        if (!$order->provider_reference) {
            return PaymentVerification::pending();
        }

        $requestTime = now('UTC')->format('YmdHis');

        try {
            $response = $this->connector->send(new CheckTransactionRequest(
                requestTime: $requestTime,
                merchantId: (string) $this->merchantId,
                transactionId: $order->provider_reference,
                hash: $this->signer->signTransactionCheck(
                    $requestTime,
                    (string) $this->merchantId,
                    $order->provider_reference,
                ),
            ));
        } catch (Throwable) {
            return PaymentVerification::pending();
        }

        $statusCode = $response->json('data.payment_status_code');

        if ($statusCode === self::APPROVED_STATUS_CODE && $response->json('data.payment_status') === 'APPROVED') {
            return PaymentVerification::make(
                PaymentVerificationStatus::PAID,
                (int) round((float) $response->json('data.payment_amount') * 100),
                (string) $response->json('data.payment_currency'),
            );
        }

        return in_array($statusCode, self::FAILED_STATUS_CODES, true)
            ? PaymentVerification::failed()
            : PaymentVerification::pending();
    }

    public static function transactionIdFor(Order $order): string
    {
        return substr($order->id, -self::TRANSACTION_ID_LENGTH);
    }

    /** @return array<int, array{name: string, quantity: int, price: string}> */
    private static function describeItems(Order $order): array
    {
        return $order->items->map(static fn (OrderItem $item): array => [
            'name' => mb_substr(
                $item->purchasable instanceof Song ? $item->purchasable->title : (string) $item->purchasable?->name,
                0,
                60,
            ),
            'quantity' => 1,
            'price' => number_format($item->amount_cents / 100, 2, '.', ''),
        ])->all();
    }
}
