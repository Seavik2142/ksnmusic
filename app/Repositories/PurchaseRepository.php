<?php

namespace App\Repositories;

use App\Enums\Store\PurchasableType;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/** @extends Repository<Purchase> */
class PurchaseRepository extends Repository
{
    /** @return array<string, list<string>> purchasable IDs keyed by purchasable type */
    public function getPurchasableIdsGroupedByType(User $user): array
    {
        $grouped = [];

        foreach (Purchase::query()->whereBelongsTo($user)->get(['purchasable_type', 'purchasable_id']) as $purchase) {
            $grouped[$purchase->purchasable_type][] = $purchase->purchasable_id;
        }

        return $grouped;
    }

    public function userOwnsSongOrItsAlbum(User $user, string $songId, ?string $albumId): bool
    {
        return Purchase::query()
            ->whereBelongsTo($user)
            ->where(static function (Builder $query) use ($songId, $albumId): void {
                $query->where(static function (Builder $songQuery) use ($songId): void {
                    $songQuery->where('purchasable_type', PurchasableType::SONG->value)->where(
                        'purchasable_id',
                        $songId,
                    );
                });

                if ($albumId) {
                    $query->orWhere(static function (Builder $albumQuery) use ($albumId): void {
                        $albumQuery->where('purchasable_type', PurchasableType::ALBUM->value)->where(
                            'purchasable_id',
                            $albumId,
                        );
                    });
                }
            })
            ->exists();
    }

    public function userOwns(User $user, PurchasableType $type, string $purchasableId): bool
    {
        return Purchase::query()
            ->whereBelongsTo($user)
            ->where('purchasable_type', $type->value)
            ->where('purchasable_id', $purchasableId)
            ->exists();
    }
}
