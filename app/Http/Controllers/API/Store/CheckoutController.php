<?php

namespace App\Http\Controllers\API\Store;

use App\Enums\Store\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Store\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\User;
use App\Services\Store\CheckoutService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Response;

class CheckoutController extends Controller
{
    /** @param User $user */
    public function __invoke(CheckoutRequest $request, CheckoutService $checkoutService, Authenticatable $user)
    {
        ['order' => $order, 'payment' => $payment] = $checkoutService->checkout(
            $user,
            $request->items,
            PaymentProvider::from($request->provider),
            $request->payment_method,
        );

        return response()->json([
            'order' => OrderResource::make($order),
            'payment' => $payment->toArray(),
        ], Response::HTTP_CREATED);
    }
}
