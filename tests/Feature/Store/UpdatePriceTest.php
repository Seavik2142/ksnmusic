<?php

namespace Tests\Feature\Store;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

use function Tests\create_admin;
use function Tests\create_user;

class UpdatePriceTest extends TestCase
{
    #[Test]
    public function theSellerSetsASongPrice(): void
    {
        $seller = create_user();
        $song = Song::factory()->for(Artist::factory()->state(['seller_id' => $seller->id]))->createOne();

        $this
            ->putAs("api/songs/{$song->id}/price", ['price_cents' => 129], $seller)
            ->assertOk()
            ->assertJsonPath('price_cents', 129)
            ->assertJsonPath('preview_only', false);

        self::assertSame(129, $song->refresh()->price_cents);
    }

    #[Test]
    public function aZeroPriceMakesTheSongFree(): void
    {
        $song = Song::factory()->createOne(['price_cents' => 99]);

        $this
            ->putAs("api/songs/{$song->id}/price", ['price_cents' => 0], create_admin())
            ->assertOk()
            ->assertJsonPath('price_cents', null);

        self::assertNull($song->refresh()->price_cents);
    }

    #[Test]
    public function aListenerCannotSetPrices(): void
    {
        $song = Song::factory()->createOne();

        $this->putAs("api/songs/{$song->id}/price", ['price_cents' => 1], create_user())->assertForbidden();

        self::assertNull($song->refresh()->price_cents);
    }

    #[Test]
    public function rejectInvalidPrices(): void
    {
        $song = Song::factory()->createOne();
        $admin = create_admin();

        foreach ([-1, 100_001, 'free'] as $invalidPrice) {
            $this->putAs(
                "api/songs/{$song->id}/price",
                ['price_cents' => $invalidPrice],
                $admin,
            )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Test]
    public function theSellerSetsAnAlbumBundlePrice(): void
    {
        $seller = create_user();
        $album = Album::factory()->for(Artist::factory()->state(['seller_id' => $seller->id]))->createOne();

        $this
            ->putAs("api/albums/{$album->id}/price", ['price_cents' => 799], $seller)
            ->assertOk()
            ->assertJsonPath('price_cents', 799)
            ->assertJsonPath('permissions.set_price', true);

        self::assertSame(799, $album->refresh()->price_cents);
    }

    #[Test]
    public function aListenerCannotSetAlbumPrices(): void
    {
        $album = Album::factory()->createOne();

        $this->putAs("api/albums/{$album->id}/price", ['price_cents' => 799], create_user())->assertForbidden();
    }
}
