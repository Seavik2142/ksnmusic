<?php

namespace App\Services\Payments\PayPal;

use App\Enums\Store\PaymentProvider;
use App\Enums\Store\PaymentVerificationStatus;
use App\Http\Integrations\PayPal\PayPalConnector;
use App\Http\Integrations\PayPal\Requests\CaptureOrderRequest;
use App\Http\Integrations\PayPal\Requests\CreateOrderRequest;
use App\Http\Integrations\PayPal\Requests\GetAccessTokenRequest;
use App\Http\Integrations\PayPal\Requests\GetOrderRequest;
use App\Models\Order;
use App\Services\Payments\Contracts\PaymentGateway;
use App\Values\Store\PaymentInitiation;
use App\Values\Store\PaymentVerification;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Saloon\Http\Response;
use SensitiveParameter;
use Throwable;

class PayPalGateway implements PaymentGateway
{
    private const array BUYER_APPROVAL_LINK_RELATIONS = ['payer-action', 'approve'];

    public function __construct(
        private readonly PayPalConnector $connector,
        #[Config('services.paypal.client_id')]
        private readonly ?string $clientId = null,
        #[Config('services.paypal.client_secret')]
        #[SensitiveParameter]
        private readonly ?string $clientSecret = null,
    ) {}

    public function provider(): PaymentProvider
    {
        return PaymentProvider::PAYPAL;
    }

    public function isConfigured(): bool
    {
        return (bool) $this->clientId && (bool) $this->clientSecret;
    }

    public function initiate(Order $order, ?string $paymentMethod = null): PaymentInitiation
    {
        $response = $this->connector->send(new CreateOrderRequest(
            accessToken: $this->accessToken(),
            order: $order,
            returnUrl: route('store.paypal.return', ['order' => $order->id]),
            cancelUrl: client_url("store/orders/{$order->id}"),
            brandName: (string) koel_branding('name'),
        ));

        $order->update(['provider_reference' => $response->json('id')]);

        return PaymentInitiation::redirect(self::buyerApprovalUrl($response));
    }

    public function verify(Order $order): PaymentVerification
    {
        if (!$order->provider_reference) {
            return PaymentVerification::pending();
        }

        try {
            $payPalOrder = $this->connector
                ->send(new GetOrderRequest($this->accessToken(), $order->provider_reference))
                ->json();

            if (Arr::get($payPalOrder, 'status') === 'APPROVED') {
                $payPalOrder = $this->connector
                    ->send(new CaptureOrderRequest(
                        accessToken: $this->accessToken(),
                        payPalOrderId: $order->provider_reference,
                        idempotencyKey: "capture-{$order->id}",
                    ))
                    ->json();
            }
        } catch (Throwable) {
            return PaymentVerification::pending();
        }

        return self::verificationFromPayPalOrder($payPalOrder);
    }

    /** @param array<string, mixed> $payPalOrder */
    private static function verificationFromPayPalOrder(array $payPalOrder): PaymentVerification
    {
        $capture = Arr::get($payPalOrder, 'purchase_units.0.payments.captures.0');

        if (Arr::get($payPalOrder, 'status') === 'COMPLETED' && Arr::get($capture, 'status') === 'COMPLETED') {
            return PaymentVerification::make(
                PaymentVerificationStatus::PAID,
                (int) round((float) Arr::get($capture, 'amount.value') * 100),
                (string) Arr::get($capture, 'amount.currency_code'),
            );
        }

        if (Arr::get($payPalOrder, 'status') === 'VOIDED' || Arr::get($capture, 'status') === 'DECLINED') {
            return PaymentVerification::failed();
        }

        return PaymentVerification::pending();
    }

    private static function buyerApprovalUrl(Response $response): string
    {
        foreach ((array) $response->json('links') as $link) {
            if (in_array(Arr::get($link, 'rel'), self::BUYER_APPROVAL_LINK_RELATIONS, true)) {
                return (string) Arr::get($link, 'href');
            }
        }

        throw new RuntimeException('PayPal did not return a link for the buyer to approve the payment.');
    }

    private function accessToken(): string
    {
        $cacheKey = cache_key('paypal access token', $this->clientId);
        $token = Cache::get($cacheKey);

        if ($token) {
            return $token;
        }

        $response = $this->connector->send(
            new GetAccessTokenRequest((string) $this->clientId, (string) $this->clientSecret),
        );

        $token = (string) $response->json('access_token');
        Cache::put($cacheKey, $token, now()->addSeconds(max((int) $response->json('expires_in') - 60, 60)));

        return $token;
    }
}
