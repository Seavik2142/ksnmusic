<?php

namespace App\Policies;

use App\Enums\Acl\Permission;
use App\Enums\Acl\Role;
use App\Facades\License;
use App\Models\Artist;
use App\Models\User;

class ArtistPolicy
{
    public function access(User $user, Artist $artist): bool
    {
        return License::isCommunity() || $artist->belongsToUser($user);
    }

    public function update(User $user, Artist $artist): bool
    {
        if ($artist->is_unknown || $artist->is_various) {
            return false;
        }

        if ($artist->belongsToUser($user)) {
            return true;
        }

        if (License::isCommunity()) {
            return $user->hasRole(Role::ADMIN)
                || $user->hasRole(Role::MANAGER)
                || $user->hasPermissionTo(Permission::MANAGE_SONGS);
        }

        if ($user->hasRole(Role::ARTIST) && ($artist->name === $user->name || $artist->songs()->where('owner_id', $user->id)->exists())) {
            return true;
        }

        return false;
    }

    public function edit(User $user, Artist $artist): bool
    {
        return $this->update($user, $artist);
    }
}
