<?php

namespace App\Models;

use App\Enums\Store\OrderStatus;
use App\Enums\Store\PaymentProvider;
use Carbon\Carbon;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property ?Carbon $paid_at
 * @property ?User $user
 * @property ?int $user_id
 * @property ?string $provider_reference
 * @property Carbon $created_at
 * @property Collection<array-key, OrderItem> $items
 * @property OrderStatus $status
 * @property PaymentProvider $provider
 * @property int $amount_cents
 * @property string $currency
 * @property string $id
 *
 * @method static OrderFactory factory(...$parameters)
 */
#[Unguarded]
class Order extends Model
{
    use HasFactory;
    use HasUlids;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'provider' => PaymentProvider::class,
            'amount_cents' => 'int',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::PAID;
    }

    public function amountInMajorUnits(): string
    {
        return number_format($this->amount_cents / 100, 2, '.', '');
    }
}
