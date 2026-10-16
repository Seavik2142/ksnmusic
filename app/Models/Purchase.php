<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PurchaseFactory;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property ?Order $order
 * @property ?string $order_id
 * @property Carbon $created_at
 * @property string $purchasable_type
 * @property Song|Album $purchasable
 * @property User $user
 * @property int $id
 * @property int $user_id
 * @property string $purchasable_id
 *
 * @method static PurchaseFactory factory(...$parameters)
 */
#[Unguarded]
class Purchase extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }
}
