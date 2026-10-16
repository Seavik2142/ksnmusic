<?php

namespace Tests\Unit\Services\Payments\PayWay;

use App\Services\Payments\PayWay\PayWaySigner;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PayWaySignerTest extends TestCase
{
    #[Test]
    public function signPurchaseFieldsInPayWaysOrderRegardlessOfTheirOrderInTheArray(): void
    {
        $signer = new PayWaySigner('secret-key');

        $hash = $signer->signPurchase([
            'currency' => 'USD',
            'amount' => '1.99',
            'tran_id' => 'T123',
            'merchant_id' => 'M1',
            'req_time' => '20261017120000',
            'payment_option' => 'abapay_khqr',
            'return_url' => 'aHR0cHM6Ly9leGFtcGxlLnRlc3Q=',
        ]);

        $expected = base64_encode(hash_hmac(
            'sha512',
            '20261017120000M1T1231.99abapay_khqraHR0cHM6Ly9leGFtcGxlLnRlc3Q=USD',
            'secret-key',
            true,
        ));

        self::assertSame($expected, $hash);
    }

    #[Test]
    public function signTransactionChecksOverRequestTimeMerchantAndTransaction(): void
    {
        $hash = (new PayWaySigner('secret-key'))->signTransactionCheck('20261017120000', 'M1', 'T123');

        self::assertSame(base64_encode(hash_hmac('sha512', '20261017120000M1T123', 'secret-key', true)), $hash);
    }

    #[Test]
    public function aDifferentKeyProducesADifferentSignature(): void
    {
        self::assertNotSame(
            (new PayWaySigner('one'))->signTransactionCheck('t', 'm', 'x'),
            (new PayWaySigner('two'))->signTransactionCheck('t', 'm', 'x'),
        );
    }
}
