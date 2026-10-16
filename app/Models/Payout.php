<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PayoutFactory;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ?User $recorder
 * @property ?User $seller
 * @property ?int $recorded_by
 * @property ?int $seller_id
 * @property ?string $note
 * @property Carbon $paid_at
 * @property int $amount_cents
 * @property int $id
 *
 * @method static PayoutFactory factory(...$parameters)
 */
#[Unguarded]
class Payout extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount_cents' => 'int',
            'paid_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
