<?php

namespace App\Http\Controllers\API;

use App\Enums\Acl\Permission;
use App\Enums\Acl\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ArtistStudioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtistStudioController extends Controller
{
    public function __construct(
        private readonly ArtistStudioService $artistStudioService,
    ) {
    }

    public function overview(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorizeAccess($user);

        $artistId = $request->query('artist_id');
        $artist = $this->artistStudioService->resolveArtistForUser($user, is_string($artistId) ? $artistId : null);

        abort_unless($artist, 404, 'No artist profile found for this account.');

        return response()->json($this->artistStudioService->getOverview($artist));
    }

    public function artists(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorizeAccess($user);

        return response()->json($this->artistStudioService->getAccessibleArtists($user));
    }

    private function authorizeAccess(User $user): void
    {
        $canAccess = $user->hasRole(Role::ARTIST)
            || $user->hasRole(Role::ADMIN)
            || $user->hasRole(Role::MANAGER)
            || $user->hasPermissionTo(Permission::MANAGE_SONGS);

        abort_unless($canAccess, 403, 'Artist Studio requires an Artist, Manager, or Admin account.');
    }
}
