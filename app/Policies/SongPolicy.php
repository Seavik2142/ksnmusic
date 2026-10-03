<?php

namespace App\Policies;

use App\Enums\Acl\Permission;
use App\Enums\Acl\Role;
use App\Facades\License;
use App\Models\Song;
use App\Models\User;

class SongPolicy
{
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

    public function edit(User $user, Song $song): bool
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

    public function download(User $user, Song $song): bool
    {
        return $this->access($user, $song);
    }
}
