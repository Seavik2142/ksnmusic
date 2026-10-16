<?php

namespace App\Http\Controllers\Store;

use App\Enums\Store\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Store\CheckoutService;
use Illuminate\Http\Response;

/**
 * The buyer lands here after approving the payment on PayPal. Capturing happens server-side,
 * then the buyer is sent to the order page in the app.
 */
class PayPalReturnController extends Controller
{
    public function __invoke(Order $order, CheckoutService $checkoutService)
    {
        abort_unless($order->provider === PaymentProvider::PAYPAL, Response::HTTP_NOT_FOUND);

        $checkoutService->refreshPaymentStatus($order);

        return redirect(client_url("store/orders/{$order->id}"));
    }
}
