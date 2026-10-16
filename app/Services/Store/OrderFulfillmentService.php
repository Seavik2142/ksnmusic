<?php

namespace App\Services\Store;

use App\Enums\Store\OrderStatus;
use App\Enums\Store\PaymentVerificationStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchase;
use App\Repositories\OrderRepository;
use App\Values\Store\PaymentVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderFulfillmentService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
    ) {}

    /**
     * Apply a provider-confirmed payment status to the order. Callbacks, the buyer's return
     * from checkout, and status polling can all race, so this locks the order and does
     * nothing once the order has left the pending state.
     */
    public function applyVerification(Order $order, PaymentVerification $verification): Order
    {
        return DB::transaction(function () use ($order, $verification): Order {
            $lockedOrder = $this->orderRepository->getOneLockedForUpdate($order->id);

            if (!$lockedOrder->isPending()) {
                return $lockedOrder;
            }

            match ($verification->status) {
                PaymentVerificationStatus::PAID => $this->fulfilIfAmountMatches($lockedOrder, $verification),
                PaymentVerificationStatus::FAILED => $lockedOrder->update(['status' => OrderStatus::FAILED]),
                PaymentVerificationStatus::PENDING => null,
            };

            return $lockedOrder->refresh();
        });
    }

    private function fulfilIfAmountMatches(Order $order, PaymentVerification $verification): void
    {
        $amountMatches =
            $verification->amountCents === $order->amount_cents
            && strtoupper((string) $verification->currency) === strtoupper($order->currency);

        if (!$amountMatches) {
            Log::error('Paid amount does not match the order; leaving it pending for review.', [
                'order' => $order->id,
                'expected' => [$order->amount_cents, $order->currency],
                'received' => [$verification->amountCents, $verification->currency],
            ]);

            return;
        }

        $order->update(['status' => OrderStatus::PAID, 'paid_at' => now()]);

        if (!$order->user_id) {
            return;
        }

        $order->items->each(static function (OrderItem $item) use ($order): void {
            Purchase::query()->firstOrCreate([
                'user_id' => $order->user_id,
                'purchasable_type' => $item->purchasable_type,
                'purchasable_id' => $item->purchasable_id,
            ], ['order_id' => $order->id]);
        });
    }
}
