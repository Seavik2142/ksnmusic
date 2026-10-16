<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Store\UpdatePriceRequest;
use App\Http\Resources\AlbumResource;
use App\Models\Album;
use App\Models\User;
use App\Repositories\AlbumRepository;
use App\Services\Store\PricingService;
use Illuminate\Contracts\Auth\Authenticatable;

class UpdateAlbumPriceController extends Controller
{
    public function __construct(
        private readonly PricingService $pricingService,
        private readonly AlbumRepository $albumRepository,
    ) {}

    /** @param User $user */
    public function __invoke(UpdatePriceRequest $request, Album $album, Authenticatable $user)
    {
        $this->authorize('setPrice', $album);

        $this->pricingService->setPrice($album, $request->price_cents);

        return AlbumResource::make($this->albumRepository->getOne($album->id, $user))->for($user);
    }
}
