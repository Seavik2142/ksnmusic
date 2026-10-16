<?php

namespace App\Http\Controllers\Store;

use App\Enums\Store\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Services\Store\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * PayWay notifies this endpoint when a payment completes. The notification itself is never trusted:
 * it only prompts a signed, server-to-server status check with PayWay before anything is fulfilled.
 */
class PayWayCallbackController extends Controller
{
    public function __invoke(Request $request, OrderRepository $orderRepository, CheckoutService $checkoutService)
    {
        $order = $orderRepository->findOneByProviderReference(
            PaymentProvider::PAYWAY,
            (string) $request->input('tran_id'),
        );

        abort_unless((bool) $order, Response::HTTP_NOT_FOUND);

        $checkoutService->refreshPaymentStatus($order);

        return response()->json(['status' => 'received']);
    }
}
