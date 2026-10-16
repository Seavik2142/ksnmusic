<?php

namespace Tests\Feature\Store;

use App\Enums\Store\PurchasableType;
use App\Models\Album;
use App\Models\Purchase;
use App\Models\Song;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

use function Tests\create_user;

class FetchPurchasesTest extends TestCase
{
    #[Test]
    public function listOnlyTheBuyersOwnPurchases(): void
    {
        $buyer = create_user();
        $boughtSong = Song::factory()->createOne(['price_cents' => 99]);
        $boughtAlbum = Album::factory()->createOne(['price_cents' => 499]);
        Song::factory()->createOne(['price_cents' => 99]);

        Purchase::factory()->for($buyer)->createOne([
            'purchasable_type' => PurchasableType::SONG->value,
            'purchasable_id' => $boughtSong->id,
        ]);
        Purchase::factory()->for($buyer)->createOne([
            'purchasable_type' => PurchasableType::ALBUM->value,
            'purchasable_id' => $boughtAlbum->id,
        ]);

        $response = $this->getAs('api/store/purchases', $buyer)->assertOk();

        self::assertSame([$boughtSong->id], array_column($response->json('songs'), 'id'));
        self::assertSame([$boughtAlbum->id], array_column($response->json('albums'), 'id'));
        $response->assertJsonPath('songs.0.is_purchased', true)->assertJsonPath('albums.0.is_purchased', true);
    }

    #[Test]
    public function tellTheAppWhichPaymentProvidersAreAvailable(): void
    {
        config([
            'services.paypal.client_id' => 'id',
            'services.paypal.client_secret' => 'secret',
            'services.payway.merchant_id' => null,
        ]);

        $this->getAs('api/data', create_user())
            ->assertJsonPath('store.payment_providers', ['paypal'])
            ->assertJsonPath('store.currency', 'USD')
            ->assertJsonPath('store.preview_seconds', 30);
    }
}
