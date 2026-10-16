<?php

namespace App\Services\Payments\Contracts;

use App\Enums\Store\PaymentProvider;
use App\Models\Order;
use App\Values\Store\PaymentInitiation;
use App\Values\Store\PaymentVerification;

interface PaymentGateway
{
    public function provider(): PaymentProvider;

    public function isConfigured(): bool;

    /**
     * Register the order with the provider (recording the provider's reference on it) and
     * tell the browser how to continue to the provider's checkout.
     */
    public function initiate(Order $order, ?string $paymentMethod = null): PaymentInitiation;

    /**
     * Ask the provider, server to server, whether the order has been paid.
     */
    public function verify(Order $order): PaymentVerification;
}
