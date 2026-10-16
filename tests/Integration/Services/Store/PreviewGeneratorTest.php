<?php

namespace Tests\Integration\Services\Store;

use App\Exceptions\PreviewUnavailableException;
use App\Models\Song;
use App\Services\Store\PreviewGenerator;
use App\Services\Transcoding\Transcoder;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

use function Tests\test_path;

class PreviewGeneratorTest extends TestCase
{
    private string $artifactsPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->artifactsPath = sys_get_temp_dir() . '/koel-preview-test-' . uniqid();
        config(['koel.artifacts_path' => $this->artifactsPath]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->artifactsPath);

        parent::tearDown();
    }

    #[Test]
    public function transcodeAnExactLengthPreviewWhenFfmpegIsAvailable(): void
    {
        $song = Song::factory()->createOne(['path' => test_path('songs/full.mp3'), 'length' => 200]);
        $transcoder = $this->mock(Transcoder::class);
        $transcoder
            ->expects('transcode')
            ->withArgs(
                static fn (string $source, string $destination, int $bitRate, ?float $seconds): bool => (
                    $source === $song->path
                    && str_ends_with($destination, '.m4a')
                    && $seconds === 30.0
                ),
            );

        $path = (new PreviewGenerator($transcoder, PHP_BINARY))->getPreviewPath($song, 30);

        self::assertStringContainsString('/previews/', $path);
    }

    #[Test]
    public function capThePreviewAtHalfOfAShortSong(): void
    {
        $song = Song::factory()->makeOne(['length' => 40]);

        self::assertSame(20.0, PreviewGenerator::effectivePreviewSeconds($song, 30));
    }

    #[Test]
    public function refuseWhenTheSongFileIsMissing(): void
    {
        $song = Song::factory()->createOne(['path' => '/nowhere/song.mp3']);

        $this->expectException(PreviewUnavailableException::class);

        app(PreviewGenerator::class)->getPreviewPath($song, 30);
    }
}
