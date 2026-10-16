<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Store\CheckoutService;

class ShowOrderController extends Controller
{
    public function __invoke(Order $order, CheckoutService $checkoutService)
    {
        $this->authorize('view', $order);

        return OrderResource::make($checkoutService->refreshPaymentStatus($order));
    }
}
