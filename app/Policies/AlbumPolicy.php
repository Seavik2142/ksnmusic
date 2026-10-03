<?php

namespace App\Policies;

use App\Enums\Acl\Permission;
use App\Enums\Acl\Role;
use App\Facades\License;
use App\Models\Album;
use App\Models\User;

class AlbumPolicy
{
    public function create(User $user): bool
    {
        return License::isCommunity()
            ? $user->hasPermissionTo(Permission::MANAGE_SONGS)
            : $user->role !== Role::GUEST;
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

        if ($user->hasRole(Role::ADMIN) || $user->hasRole(Role::MANAGER)) {
            return true;
        }

        if ($album->belongsToUser($user)) {
            return true;
        }

        if ($user->hasRole(Role::ARTIST) && (
            $album->artist_name === $user->name
            || $album->artist?->name === $user->name
            || $album->songs()->where('owner_id', $user->id)->exists()
        )) {
            return true;
        }

        // For CE, if the user can manage songs, they can update any album.
        if ($user->hasPermissionTo(Permission::MANAGE_SONGS) && License::isCommunity()) {
            return true;
        }

        // For Plus, only the owner of the album can update it.
        return $album->belongsToUser($user) && License::isPlus();
    }

    public function edit(User $user, Album $album): bool
    {
        return $this->update($user, $album);
    }
}
