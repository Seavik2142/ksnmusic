<?php

namespace App\Http\Controllers\API\Store;

use App\Enums\Store\PurchasableType;
use App\Http\Controllers\Controller;
use App\Http\Resources\AlbumResource;
use App\Http\Resources\SongResource;
use App\Models\User;
use App\Repositories\AlbumRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\SongRepository;
use Illuminate\Contracts\Auth\Authenticatable;

class FetchPurchasesController extends Controller
{
    /** @param User $user */
    public function __invoke(
        PurchaseRepository $purchaseRepository,
        SongRepository $songRepository,
        AlbumRepository $albumRepository,
        Authenticatable $user,
    ) {
        $purchasedIds = $purchaseRepository->getPurchasableIdsGroupedByType($user);

        return response()->json([
            'songs' => SongResource::collection($songRepository->getMany(
                $purchasedIds[PurchasableType::SONG->value] ?? [],
                scopedUser: $user,
            )),
            'albums' => AlbumResource::collection($albumRepository->getMany(
                $purchasedIds[PurchasableType::ALBUM->value] ?? [],
                user: $user,
            )),
        ]);
    }
}
