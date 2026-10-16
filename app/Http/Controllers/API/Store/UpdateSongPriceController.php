<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Store\UpdatePriceRequest;
use App\Http\Resources\SongResource;
use App\Models\Song;
use App\Models\User;
use App\Repositories\SongRepository;
use App\Services\Store\PricingService;
use Illuminate\Contracts\Auth\Authenticatable;

class UpdateSongPriceController extends Controller
{
    public function __construct(
        private readonly PricingService $pricingService,
        private readonly SongRepository $songRepository,
    ) {}

    /** @param User $user */
    public function __invoke(UpdatePriceRequest $request, Song $song, Authenticatable $user)
    {
        $this->authorize('setPrice', $song);

        $this->pricingService->setPrice($song, $request->price_cents);

        return SongResource::make($this->songRepository->getOne($song->id, $user))->for($user);
    }
}
