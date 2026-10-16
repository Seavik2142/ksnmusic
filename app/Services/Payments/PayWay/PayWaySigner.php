<?php

namespace App\Services\Payments\PayWay;

use Illuminate\Container\Attributes\Config;
use SensitiveParameter;

/**
 * PayWay signs requests with HMAC-SHA512 over the concatenated field values, base64-encoded.
 * The purchase field order follows PayWay's checkout documentation; confirm it in the PayWay
 * sandbox for your merchant profile before going live, as a wrong order is rejected silently.
 */
class PayWaySigner
{
    public const array PURCHASE_HASH_FIELD_ORDER = [
        'req_time',
        'merchant_id',
        'tran_id',
        'amount',
        'items',
        'shipping',
        'firstname',
        'lastname',
        'email',
        'phone',
        'type',
        'payment_option',
        'return_url',
        'cancel_url',
        'continue_success_url',
        'return_deeplink',
        'currency',
        'custom_fields',
        'return_params',
        'payout',
        'lifetime',
        'additional_params',
        'google_pay_token',
        'skip_success_page',
    ];

    public function __construct(
        #[Config('services.payway.api_key')]
        #[SensitiveParameter]
        private readonly ?string $apiKey = null,
    ) {}

    /** @param array<string, string> $fields */
    public function signPurchase(array $fields): string
    {
        $concatenatedValues = implode('', array_map(
            static fn (string $field): string => $fields[$field] ?? '',
            self::PURCHASE_HASH_FIELD_ORDER,
        ));

        return $this->sign($concatenatedValues);
    }

    public function signTransactionCheck(string $requestTime, string $merchantId, string $transactionId): string
    {
        return $this->sign($requestTime . $merchantId . $transactionId);
    }

    private function sign(string $message): string
    {
        return base64_encode(hash_hmac('sha512', $message, (string) $this->apiKey, true));
    }
}
