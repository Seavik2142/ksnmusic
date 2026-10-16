<?php

namespace Tests\Integration\Services\Store;

use App\Enums\Store\PurchasableType;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Purchase;
use App\Models\Song;
use App\Services\Store\EntitlementService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

use function Tests\create_admin;
use function Tests\create_manager;
use function Tests\create_user;

class EntitlementServiceTest extends TestCase
{
    private EntitlementService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(EntitlementService::class);
    }

    #[Test]
    public function anyoneCanListenToAFreeSong(): void
    {
        $song = Song::factory()->createOne(['price_cents' => null]);

        self::assertTrue($this->service->canListen(create_user(), $song));
        self::assertTrue($this->service->canListen(null, $song));
        self::assertNull($this->service->previewSecondsFor(null, $song));
    }

    #[Test]
    public function aListenerWhoHasNotBoughtAPaidSongOnlyGetsAPreview(): void
    {
        $song = Song::factory()->createOne(['price_cents' => 99]);
        $user = create_user();

        self::assertFalse($this->service->canListen($user, $song));
        self::assertSame(30, $this->service->previewSecondsFor($user, $song));
        self::assertTrue($this->service->isPreviewOnly($user, $song));
    }

    #[Test]
    public function aGuestOnlyGetsAPreviewOfAPaidSong(): void
    {
        $song = Song::factory()->createOne(['price_cents' => 99]);

        self::assertFalse($this->service->canListen(null, $song));
        self::assertTrue($this->service->isPreviewOnly(null, $song));
    }

    #[Test]
    public function buyingTheSongUnlocksIt(): void
    {
        $song = Song::factory()->createOne(['price_cents' => 99]);
        $user = create_user();
        Purchase::factory()->for($user)->createOne([
            'purchasable_type' => PurchasableType::SONG->value,
            'purchasable_id' => $song->id,
        ]);

        self::assertTrue($this->service->canListen($user, $song));
        self::assertTrue($this->service->hasPurchasedSong($user, $song));
        self::assertFalse($this->service->isPreviewOnly($user, $song));
    }

    #[Test]
    public function buyingTheAlbumUnlocksItsSongs(): void
    {
        $album = Album::factory()->createOne(['price_cents' => 499]);
        $song = Song::factory()->for($album)->createOne(['price_cents' => 99]);
        $user = create_user();
        Purchase::factory()->for($user)->createOne([
            'purchasable_type' => PurchasableType::ALBUM->value,
            'purchasable_id' => $album->id,
        ]);

        self::assertTrue($this->service->canListen($user, $song));
        self::assertTrue($this->service->hasPurchasedAlbum($user, $album));
        self::assertFalse($this->service->isPreviewOnly($user, $song));
    }

    #[Test]
    public function anotherListenersPurchaseDoesNotUnlockTheSong(): void
    {
        $song = Song::factory()->createOne(['price_cents' => 99]);
        Purchase::factory()->createOne([
            'purchasable_type' => PurchasableType::SONG->value,
            'purchasable_id' => $song->id,
        ]);

        self::assertFalse($this->service->canListen(create_user(), $song));
    }

    #[Test]
    public function theSellerCanListenToTheirOwnPaidSongs(): void
    {
        $seller = create_user();
        $artist = Artist::factory()->createOne(['seller_id' => $seller->id]);
        $song = Song::factory()->for($artist)->createOne(['price_cents' => 99]);

        self::assertTrue($this->service->canListen($seller, $song));
        self::assertFalse($this->service->isPreviewOnly($seller, $song));
    }

    #[Test]
    public function matchingTheArtistNameDoesNotMakeSomeoneTheSeller(): void
    {
        $artist = Artist::factory()->createOne(['name' => 'Impersonated']);
        $song = Song::factory()->for($artist)->createOne(['price_cents' => 99]);
        $impostor = create_user(['name' => 'Impersonated']);

        self::assertFalse($this->service->canListen($impostor, $song));
        self::assertFalse($this->service->canSetPrice($impostor, $song));
    }

    #[Test]
    public function adminsAndManagersCanListenToAndPricePaidSongs(): void
    {
        $song = Song::factory()->createOne(['price_cents' => 99]);

        foreach ([create_admin(), create_manager()] as $staff) {
            self::assertTrue($this->service->canListen($staff, $song));
            self::assertTrue($this->service->canSetPrice($staff, $song));
        }
    }

    #[Test]
    public function onlyTheSellerOrStaffCanSetPrices(): void
    {
        $seller = create_user();
        $artist = Artist::factory()->createOne(['seller_id' => $seller->id]);
        $song = Song::factory()->for($artist)->createOne();
        $album = Album::factory()->for($artist)->createOne();

        self::assertTrue($this->service->canSetPrice($seller, $song));
        self::assertTrue($this->service->canSetPrice($seller, $album));
        self::assertFalse($this->service->canSetPrice(create_user(), $song));
        self::assertFalse($this->service->canSetPrice(create_user(), $album));
    }
}
