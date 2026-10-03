<?php

namespace App\Services;

use App\Enums\Acl\Role;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Song;
use App\Models\User;
use App\Repositories\ArtistRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ArtistStudioService
{
    public function __construct(
        private readonly ArtistRepository $artistRepository,
    ) {
    }

    /**
     * Resolves the primary artist profile for the given user, or specified artist ID for admins.
     */
    public function resolveArtistForUser(User $user, ?string $artistId = null): ?Artist
    {
        if ($artistId) {
            $artist = $this->artistRepository->findOne($artistId);

            if ($artist && ($user->hasRole(Role::ADMIN) || $user->hasRole(Role::MANAGER) || $artist->belongsToUser($user))) {
                return $artist;
            }
        }

        // 1. Check if user owns an artist by user_id
        $artist = Artist::query()
            ->onlyStandard()
            ->where('user_id', $user->id)
            ->first();

        if ($artist) {
            return $artist;
        }

        // 2. Check if an artist exists with the exact same name as the user
        $artist = Artist::query()
            ->onlyStandard()
            ->where('name', $user->name)
            ->first();

        if ($artist) {
            return $artist;
        }

        // 3. Check if user owns any songs and get their artist
        $song = Song::query()
            ->where('owner_id', $user->id)
            ->first();

        if ($song && $song->artist) {
            return $song->artist;
        }

        // 4. For admins/managers, default to the first standard artist in library
        if ($user->hasRole(Role::ADMIN) || $user->hasRole(Role::MANAGER)) {
            return Artist::query()->onlyStandard()->first();
        }

        // 5. Otherwise create or get an artist for this user account so they have an active profile
        return Artist::getOrCreate($user, $user->name);
    }

    /**
     * Returns the list of artists accessible to this user (for admins, all artists; for artists, their own).
     *
     * @return Collection<int, array{id: string, name: string, image: ?string}>
     */
    public function getAccessibleArtists(User $user): Collection
    {
        if ($user->hasRole(Role::ADMIN) || $user->hasRole(Role::MANAGER)) {
            return Artist::query()
                ->onlyStandard()
                ->orderBy('name')
                ->get()
                ->map(static fn (Artist $artist): array => [
                    'id' => $artist->id,
                    'name' => $artist->name,
                    'image' => image_storage_url($artist->image),
                ]);
        }

        $primaryArtist = $this->resolveArtistForUser($user);

        if (!$primaryArtist) {
            return collect();
        }

        return collect([[
            'id' => $primaryArtist->id,
            'name' => $primaryArtist->name,
            'image' => image_storage_url($primaryArtist->image),
        ]]);
    }

    /**
     * Aggregates real-time statistics and studio analytics for an artist.
     *
     * @return array<string, mixed>
     */
    public function getOverview(Artist $artist): array
    {
        /** @var array<int, string> $songIds */
        $songIds = Song::query()
            ->leftJoin('albums', 'songs.album_id', '=', 'albums.id')
            ->where(static function (Builder $query) use ($artist): void {
                $query->where('songs.artist_id', $artist->id)
                    ->orWhere('albums.artist_id', $artist->id);
            })
            ->pluck('songs.id')
            ->all();

        $totalStreams = !$songIds ? 0 : (int) DB::table('interactions')
            ->whereIn('song_id', $songIds)
            ->sum('play_count');

        $monthlyListeners = !$songIds ? 0 : (int) DB::table('interactions')
            ->whereIn('song_id', $songIds)
            ->where('last_played_at', '>=', now()->subDays(30))
            ->distinct()
            ->count('user_id');

        $followers = DB::table('favorites')
            ->where('favoriteable_type', 'artist')
            ->where('favoriteable_id', $artist->id)
            ->count();

        $totalTracks = count($songIds);
        $totalAlbums = DB::table('albums')->where('artist_id', $artist->id)->count();

        $topSongStats = !$songIds ? collect() : DB::table('interactions')
            ->whereIn('song_id', $songIds)
            ->groupBy('song_id')
            ->selectRaw('song_id, sum(play_count) as total_plays')
            ->pluck('total_plays', 'song_id');

        $songFavorites = !$songIds ? collect() : DB::table('favorites')
            ->where('favoriteable_type', 'song')
            ->whereIn('favoriteable_id', $songIds)
            ->groupBy('favoriteable_id')
            ->selectRaw('favoriteable_id, count(*) as total_favorites')
            ->pluck('total_favorites', 'favoriteable_id');

        $topSongs = Song::query()
            ->with('album')
            ->whereIn('id', $songIds)
            ->get()
            ->map(static function (Song $song) use ($topSongStats, $songFavorites): array {
                return [
                    'id' => $song->id,
                    'title' => $song->title,
                    'album_id' => $song->album_id,
                    'album_name' => $song->album_name,
                    'album_cover' => image_storage_url($song->album?->cover),
                    'length' => $song->length,
                    'play_count' => (int) ($topSongStats[$song->id] ?? 0),
                    'favorites_count' => (int) ($songFavorites[$song->id] ?? 0),
                    'created_at' => $song->created_at?->toIso8601String(),
                ];
            })
            ->sortByDesc('play_count')
            ->values()
            ->take(10)
            ->all();

        $releases = Album::query()
            ->where('artist_id', $artist->id)
            ->withCount('songs')
            ->get()
            ->map(static function (Album $album) use ($topSongStats): array {
                /** @var array<int, string> $albumSongIds */
                $albumSongIds = $album->songs()->pluck('id')->all();
                $albumPlays = 0;

                foreach ($albumSongIds as $id) {
                    $albumPlays += (int) ($topSongStats[$id] ?? 0);
                }

                return [
                    'id' => $album->id,
                    'name' => $album->name,
                    'cover' => image_storage_url($album->cover),
                    'year' => $album->year,
                    'track_count' => $album->songs_count,
                    'total_plays' => $albumPlays,
                    'created_at' => $album->created_at?->toIso8601String(),
                ];
            })
            ->sortByDesc('created_at')
            ->values()
            ->all();

        $playlistAppearances = !$songIds ? [] : Playlist::query()
            ->whereHas('playables', static function (Builder $query) use ($songIds): void {
                $query->whereIn('songs.id', $songIds);
            })
            ->withCount('playables')
            ->withCount([
                'playables as artist_tracks_count' => static function (Builder $query) use ($songIds): void {
                    $query->whereIn('songs.id', $songIds);
                },
            ])
            ->orderByDesc('artist_tracks_count')
            ->limit(10)
            ->get()
            ->map(static function (Playlist $playlist): array {
                $ownerName = $playlist->users->firstWhere('pivot.role', 'owner')?->name ?? 'Community';

                return [
                    'id' => $playlist->id,
                    'name' => $playlist->name,
                    'creator_name' => $ownerName,
                    'cover' => image_storage_url($playlist->cover),
                    'songs_count' => (int) $playlist->playables_count,
                    'artist_tracks_count' => (int) $playlist->artist_tracks_count,
                ];
            })
            ->all();

        $topListeners = !$songIds ? [] : DB::table('interactions')
            ->join('users', 'interactions.user_id', '=', 'users.id')
            ->whereIn('interactions.song_id', $songIds)
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->selectRaw('users.id, users.name, users.avatar, sum(interactions.play_count) as total_plays, max(interactions.last_played_at) as last_played_at')
            ->orderByDesc('total_plays')
            ->limit(10)
            ->get()
            ->map(static function (object $row): array {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'avatar' => User::query()->find($row->id)?->avatar ?? '',
                    'play_count' => (int) $row->total_plays,
                    'last_played_at' => $row->last_played_at ? Carbon::parse($row->last_played_at)->toIso8601String() : null,
                ];
            })
            ->all();

        return [
            'artist' => [
                'id' => $artist->id,
                'name' => $artist->name,
                'image' => image_storage_url($artist->image),
            ],
            'stats' => [
                'total_streams' => $totalStreams,
                'monthly_listeners' => $monthlyListeners,
                'followers' => $followers,
                'total_tracks' => $totalTracks,
                'total_albums' => $totalAlbums,
                'total_playlists' => count($playlistAppearances),
            ],
            'top_songs' => $topSongs,
            'releases' => $releases,
            'playlist_appearances' => $playlistAppearances,
            'top_listeners' => $topListeners,
        ];
    }
}
