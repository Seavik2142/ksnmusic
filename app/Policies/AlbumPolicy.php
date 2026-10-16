<?php

namespace App\Policies;

use App\Enums\Acl\Permission;
use App\Enums\Acl\Role;
use App\Facades\License;
use App\Models\Album;
use App\Models\User;
use App\Services\Store\EntitlementService;

class AlbumPolicy
{
    public function __construct(
        private readonly EntitlementService $entitlementService,
    ) {}

    public function create(User $user): bool
    {
        return License::isCommunity() ? $user->hasPermissionTo(Permission::MANAGE_SONGS) : $user->role !== Role::GUEST;
    }

    public function access(User $user, Album $album): bool
    {
        return License::isCommunity() || $album->belongsToUser($user);
    }

    /**
     * If the user can update the album (e.g., edit name, year, or upload the cover image).
     */
    public function update(User $user, Album $album): bool
    {
        // Unknown albums are not editable.
        if ($album->is_unknown) {
            return false;
        }

        if ($album->belongsToUser($user)) {
            return true;
        }

        if (License::isCommunity()) {
            return $user->hasRole(Role::ADMIN)
                || $user->hasRole(Role::MANAGER)
                || $user->hasPermissionTo(Permission::MANAGE_SONGS);
        }

        if (
            $user->hasRole(Role::ARTIST)
            && (
                $album->artist_name === $user->name
                || $album->artist->name === $user->name
                || $album->songs()->where('owner_id', $user->id)->exists()
            )
        ) {
            return true;
        }

        return false;
    }

    public function edit(User $user, Album $album): bool
    {
        return $this->update($user, $album);
    }

    public function setPrice(User $user, Album $album): bool
    {
        return !$album->is_unknown && $this->entitlementService->canSetPrice($user, $album);
    }
}
