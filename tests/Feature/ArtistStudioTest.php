<?php

namespace Tests\Feature;

use App\Enums\Acl\Role;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Favorite;
use App\Models\Interaction;
use App\Models\Playlist;
use App\Models\Song;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

use function Tests\create_admin;
use function Tests\create_playlist;
use function Tests\create_user;

class ArtistStudioTest extends TestCase
{
    #[Test]
    public function artistStudioRequiresAuthorizedRole(): void
    {
        $regularUser = create_user();

        $this->getAs('api/artist-studio/overview', $regularUser)
            ->assertForbidden();
    }

    #[Test]
    public function artistStudioOverviewForArtistUser(): void
    {
        $artistUser = create_user();
        $artistUser->syncRoles(Role::ARTIST);

        $artist = Artist::factory()->createOne([
            'name' => $artistUser->name,
            'user_id' => $artistUser->id,
        ]);

        $album = Album::factory()->for($artist)->createOne();
        $songs = Song::factory()->for($artist)->for($album)->createMany(3);

        $listener = create_user();

        foreach ($songs as $song) {
            Interaction::factory()->createOne([
                'user_id' => $listener->id,
                'song_id' => $song->id,
                'play_count' => 15,
                'last_played_at' => now()->subDays(2),
            ]);
        }

        Favorite::factory()->createOne([
            'user_id' => $listener->id,
            'favoriteable_id' => $artist->id,
            'favoriteable_type' => 'artist',
        ]);

        $playlist = create_playlist();
        $playlist->addPlayables([$songs->first()->id]);

        $response = $this->getAs('api/artist-studio/overview', $artistUser);

        $response->assertOk()
            ->assertJsonStructure([
                'artist' => ['id', 'name', 'image'],
                'stats' => [
                    'total_streams',
                    'monthly_listeners',
                    'followers',
                    'total_tracks',
                    'total_albums',
                    'total_playlists',
                ],
                'top_songs',
                'releases',
                'playlist_appearances',
                'top_listeners',
            ]);

        $this->assertSame(45, $response->json('stats.total_streams'));
        $this->assertSame(1, $response->json('stats.monthly_listeners'));
        $this->assertSame(1, $response->json('stats.followers'));
        $this->assertSame(3, $response->json('stats.total_tracks'));
        $this->assertSame(1, $response->json('stats.total_albums'));
        $this->assertSame(1, $response->json('stats.total_playlists'));
    }

    #[Test]
    public function adminCanViewAnyArtistStudio(): void
    {
        $admin = create_admin();
        $artist = Artist::factory()->createOne();
        Song::factory()->for($artist)->createMany(2);

        $this->getAs('api/artist-studio/overview?artist_id=' . $artist->id, $admin)
            ->assertOk()
            ->assertJsonPath('artist.id', $artist->id);
    }

    #[Test]
    public function getAccessibleArtists(): void
    {
        $admin = create_admin();
        Artist::factory()->createMany(3);

        $this->getAs('api/artist-studio/artists', $admin)
            ->assertOk()
            ->assertJsonStructure([
                '*' => ['id', 'name', 'image'],
            ]);
    }
}
