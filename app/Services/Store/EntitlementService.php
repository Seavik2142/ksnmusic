<?php

namespace App\Services\Store;

use App\Enums\Acl\Role;
use App\Enums\Store\PurchasableType;
use App\Models\Album;
use App\Models\Song;
use App\Models\User;
use App\Repositories\PurchaseRepository;
use Illuminate\Container\Attributes\Scoped;

#[Scoped]
class EntitlementService
{
    /** @var array<int, array<string, list<string>>> */
    private array $ownedIdsByUser = [];

    public function __construct(
        private readonly PurchaseRepository $purchaseRepository,
    ) {}

    public function canListen(?User $user, Song $song): bool
    {
        if (!$song->isForSale()) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if (self::managesStore($user) || $song->artist?->isSoldBy($user)) {
            return true;
        }

        return $this->purchaseRepository->userOwnsSongOrItsAlbum($user, $song->id, $song->album_id);
    }

    /**
     * How many seconds of the song may be streamed, or null if the user may hear all of it.
     */
    public function previewSecondsFor(?User $user, Song $song): ?int
    {
        return $this->canListen($user, $song) ? null : (int) config('koel.store.preview_seconds');
    }

    public function canSetPrice(User $user, Song|Album $item): bool
    {
        return self::managesStore($user) || (bool) $item->artist?->isSoldBy($user);
    }

    /**
     * The same answer as canListen(), but using purchases loaded once per request, for rendering song lists.
     */
    public function isPreviewOnly(?User $user, Song $song): bool
    {
        if (!$song->isForSale()) {
            return false;
        }

        if (!$user) {
            return true;
        }

        return !(self::managesStore($user) || $song->artist?->isSoldBy($user) || $this->hasPurchasedSong($user, $song));
    }

    /**
     * Whether the user bought this song (directly or via its album), using IDs loaded once per request,
     * so that rendering a long song list doesn't query the database per song.
     */
    public function hasPurchasedSong(User $user, Song $song): bool
    {
        $ownedIds = $this->ownedIdsFor($user);

        return (
            in_array($song->id, $ownedIds[PurchasableType::SONG->value] ?? [], true)
            || $song->album_id && in_array($song->album_id, $ownedIds[PurchasableType::ALBUM->value] ?? [], true)
        );
    }

    public function hasPurchasedAlbum(User $user, Album $album): bool
    {
        return in_array($album->id, $this->ownedIdsFor($user)[PurchasableType::ALBUM->value] ?? [], true);
    }

    /** @return array<string, list<string>> */
    private function ownedIdsFor(User $user): array
    {
        return $this->ownedIdsByUser[$user->id] ??= $this->purchaseRepository->getPurchasableIdsGroupedByType($user);
    }

    private static function managesStore(User $user): bool
    {
        return $user->hasRole(Role::ADMIN) || $user->hasRole(Role::MANAGER);
    }
}
