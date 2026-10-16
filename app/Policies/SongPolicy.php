<?php

namespace App\Policies;

use App\Enums\Acl\Permission;
use App\Enums\Acl\Role;
use App\Facades\License;
use App\Models\Song;
use App\Models\User;
use App\Services\Store\EntitlementService;

class SongPolicy
{
    public function __construct(
        private readonly EntitlementService $entitlementService,
    ) {}

    public function access(User $user, Song $song): bool
    {
        return License::isCommunity() || $song->accessibleBy($user);
    }

    public function own(User $user, Song $song): bool
    {
        return $song->ownedBy($user);
    }

    public function delete(User $user, Song $song): bool
    {
        return $this->canManage($user, $song);
    }

    public function edit(User $user, Song $song): bool
    {
        return $this->canManage($user, $song);
    }

    public function download(User $user, Song $song): bool
    {
        return $this->access($user, $song) && $this->listen($user, $song);
    }

    /**
     * If the user may hear the full song, as opposed to only its preview.
     */
    public function listen(User $user, Song $song): bool
    {
        return $this->entitlementService->canListen($user, $song);
    }

    public function setPrice(User $user, Song $song): bool
    {
        return !$song->isEpisode() && $this->entitlementService->canSetPrice($user, $song);
    }

    private function canManage(User $user, Song $song): bool
    {
        if ($user->hasRole(Role::ADMIN) || $user->hasRole(Role::MANAGER)) {
            return true;
        }

        if ($song->ownedBy($user)) {
            return true;
        }

        if ($user->hasRole(Role::ARTIST) && ($song->artist_name === $user->name || $song->owner_id === $user->id)) {
            return true;
        }

        return License::isCommunity() ? $user->hasPermissionTo(Permission::MANAGE_SONGS) : $song->ownedBy($user);
    }
}
