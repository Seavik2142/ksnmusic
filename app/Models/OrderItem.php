<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property ?User $seller
 * @property ?int $seller_id
 * @property Order $order
 * @property string $purchasable_type
 * @property Song|Album|null $purchasable The item sold, unless it has since been deleted
 * @property int $amount_cents
 * @property int $commission_cents
 * @property int $id
 * @property int $seller_earning_cents
 * @property string $order_id
 * @property string $purchasable_id
 *
 * @method static OrderItemFactory factory(...$parameters)
 */
#[Unguarded]
class OrderItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount_cents' => 'int',
            'commission_cents' => 'int',
            'seller_earning_cents' => 'int',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
