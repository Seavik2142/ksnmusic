<?php

namespace Tests\Feature\Store;

use App\Enums\Store\PurchasableType;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Purchase;
use App\Models\Song;
use App\Models\User;
use App\Services\Auth\TokenManager;
use App\Services\DownloadService;
use getID3;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

use function Tests\create_admin;
use function Tests\create_user;
use function Tests\test_path;

class PaidSongStreamingTest extends TestCase
{
    private string $artifactsPath;
    private string $songPath;

    public function setUp(): void
    {
        parent::setUp();

        ob_start();

        $this->artifactsPath = sys_get_temp_dir() . '/koel-store-test-' . uniqid();
        $this->songPath = test_path('songs/full.mp3');

        config([
            'koel.artifacts_path' => $this->artifactsPath,
            'koel.streaming.ffmpeg_path' => '',
            'koel.store.preview_seconds' => 30,
        ]);
    }

    protected function tearDown(): void
    {
        ob_end_clean();
        File::deleteDirectory($this->artifactsPath);

        parent::tearDown();
    }

    #[Test]
    public function streamTheWholeOfAFreeSong(): void
    {
        $song = $this->createSong(['price_cents' => null]);

        self::assertSame(filesize($this->songPath), $this->streamedByteCount(create_user(), $song));
    }

    #[Test]
    public function streamTheTagsPlusHalfTheAudioAsThePreviewOfAShortPaidSong(): void
    {
        $song = $this->createSong(['price_cents' => 99]);
        $info = (new getID3())->analyze($this->songPath);
        $audioStart = $info['avdataoffset'];
        $audioBytes = $info['avdataend'] - $audioStart;

        $streamed = $this->streamedByteCount(create_user(), $song);

        self::assertGreaterThan($audioStart + (int) ($audioBytes * 0.45), $streamed);
        self::assertLessThan($audioStart + (int) ($audioBytes * 0.55), $streamed);
    }

    #[Test]
    public function streamTheWholeSongOnceBought(): void
    {
        $song = $this->createSong(['price_cents' => 99]);
        $buyer = create_user();
        $this->purchase($buyer, PurchasableType::SONG, $song->id);

        self::assertSame(filesize($this->songPath), $this->streamedByteCount($buyer, $song));
    }

    #[Test]
    public function streamTheWholeSongOnceItsAlbumIsBought(): void
    {
        $album = Album::factory()->createOne(['price_cents' => 499]);
        $song = $this->createSong(['price_cents' => 99, 'album_id' => $album->id]);
        $buyer = create_user();
        $this->purchase($buyer, PurchasableType::ALBUM, $album->id);

        self::assertSame(filesize($this->songPath), $this->streamedByteCount($buyer, $song));
    }

    #[Test]
    public function streamTheWholeSongToItsSellerAndToAdmins(): void
    {
        $seller = create_user();
        $artist = Artist::factory()->createOne(['seller_id' => $seller->id]);
        $song = $this->createSong(['price_cents' => 99, 'artist_id' => $artist->id]);

        self::assertSame(filesize($this->songPath), $this->streamedByteCount($seller, $song));
        self::assertSame(filesize($this->songPath), $this->streamedByteCount(create_admin(), $song));
    }

    #[Test]
    public function refuseThePreviewWhenTheFormatCannotBeCutWithoutFfmpeg(): void
    {
        $song = $this->createSong(['price_cents' => 99, 'mime_type' => 'audio/mp4']);
        $token = app(TokenManager::class)->createCompositeToken(create_user());

        $this->get("play/{$song->id}?t=$token->audioToken")->assertStatus(Response::HTTP_PAYMENT_REQUIRED);
    }

    #[Test]
    public function refuseToDownloadAPaidSongTheListenerHasNotBought(): void
    {
        $song = $this->createSong(['price_cents' => 99]);
        $this->mock(DownloadService::class)->shouldNotReceive('getDownloadable');

        $this->get(
            "download/songs?songs[]={$song->id}&api_token=" . create_user()->createToken('Koel')->plainTextToken,
        )->assertStatus(Response::HTTP_PAYMENT_REQUIRED);
    }

    /** @param array<string, mixed> $attributes */
    private function createSong(array $attributes): Song
    {
        return Song::factory()->createOne([
            'path' => $this->songPath,
            'mime_type' => 'audio/mpeg',
            'length' => 10.1,
            ...$attributes,
        ]);
    }

    private function purchase(User $buyer, PurchasableType $type, string $purchasableId): void
    {
        Purchase::factory()->for($buyer)->createOne([
            'purchasable_type' => $type->value,
            'purchasable_id' => $purchasableId,
        ]);
    }

    private function streamedByteCount(User $listener, Song $song): int
    {
        $token = app(TokenManager::class)->createCompositeToken($listener);
        $response = $this->get("play/{$song->id}?t=$token->audioToken");
        $response->assertOk();

        return strlen($response->streamedContent());
    }
}
