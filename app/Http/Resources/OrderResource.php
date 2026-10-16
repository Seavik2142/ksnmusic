<?php

namespace App\Http\Resources;

use App\Enums\Store\PurchasableType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public const array JSON_STRUCTURE = [
        'type',
        'id',
        'status',
        'provider',
        'amount_cents',
        'currency',
        'paid_at',
        'created_at',
        'items' => [
            '*' => ['type', 'id', 'name', 'amount_cents'],
        ],
    ];

    public function __construct(
        private readonly Order $order,
    ) {
        parent::__construct($order);
    }

    /** @inheritdoc */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'orders',
            'id' => $this->order->id,
            'status' => $this->order->status->value,
            'provider' => $this->order->provider->value,
            'amount_cents' => $this->order->amount_cents,
            'currency' => $this->order->currency,
            'paid_at' => $this->order->paid_at,
            'created_at' => $this->order->created_at,
            'items' => $this->order->items->map(static fn (OrderItem $item): array => [
                'type' => $item->purchasable_type === PurchasableType::ALBUM->value ? 'albums' : 'songs',
                'id' => $item->purchasable_id,
                'name' => $item->purchasable instanceof Song ? $item->purchasable->title : $item->purchasable?->name,
                'amount_cents' => $item->amount_cents,
            ]),
        ];
    }
}
