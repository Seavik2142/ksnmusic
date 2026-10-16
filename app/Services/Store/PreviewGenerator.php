<?php

namespace App\Services\Store;

use App\Enums\SongStorageType;
use App\Exceptions\PreviewUnavailableException;
use App\Models\Song;
use App\Services\Transcoding\Transcoder;
use getID3;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\File;

/**
 * Produces a cached, start-of-song preview file for songs the listener hasn't bought.
 * With FFmpeg, the preview is an exact-length AAC transcode. Without it, the preview is the
 * file truncated just past the preview's share of its audio data, which only formats that
 * stay playable when cut short (MP3, Ogg, FLAC) can use.
 */
class PreviewGenerator
{
    private const array TRUNCATABLE_MIME_TYPES = [
        'audio/mpeg',
        'audio/ogg',
        'audio/vorbis',
        'audio/opus',
        'audio/flac',
        'audio/x-flac',
    ];

    public function __construct(
        private readonly Transcoder $transcoder,
        #[Config('koel.streaming.ffmpeg_path')]
        private readonly ?string $ffmpegPath = null,
    ) {}

    public function getPreviewPath(Song $song, int $requestedSeconds): string
    {
        throw_unless(
            $song->storage === SongStorageType::LOCAL && File::isReadable($song->path),
            PreviewUnavailableException::class,
        );

        $seconds = self::effectivePreviewSeconds($song, $requestedSeconds);

        return $this->ffmpegPath && is_executable($this->ffmpegPath)
            ? $this->transcodedPreview($song, $seconds)
            : $this->truncatedPreview($song, $seconds);
    }

    /**
     * A preview is never more than half the song, so a very short song can't be heard in full for free.
     */
    public static function effectivePreviewSeconds(Song $song, int $requestedSeconds): float
    {
        return $song->length > 0 ? min((float) $requestedSeconds, $song->length / 2) : (float) $requestedSeconds;
    }

    private function transcodedPreview(Song $song, float $seconds): string
    {
        $destination = self::previewPath($song, $seconds, 'm4a');

        if (!File::exists($destination)) {
            $this->transcoder->transcode($song->path, $destination, 128, $seconds);
        }

        return $destination;
    }

    private function truncatedPreview(Song $song, float $seconds): string
    {
        throw_unless(
            in_array($song->mime_type, self::TRUNCATABLE_MIME_TYPES, true),
            PreviewUnavailableException::class,
        );

        $destination = self::previewPath($song, $seconds, File::extension($song->path) ?: 'audio');

        if (!File::exists($destination)) {
            self::writeTruncatedCopy($song->path, $destination, self::previewByteLength($song, $seconds));
        }

        return $destination;
    }

    /**
     * Keeps everything before the audio data (headers, embedded artwork) plus the preview's
     * proportional share of the audio data itself, so large cover art can't crowd out the audio.
     */
    private static function previewByteLength(Song $song, float $seconds): int
    {
        $info = (new getID3())->analyze($song->path);
        $audioStart = (int) ($info['avdataoffset'] ?? 0);
        $audioEnd = (int) ($info['avdataend'] ?? File::size($song->path));
        $duration = (float) ($info['playtime_seconds'] ?? $song->length);

        throw_unless($duration > 0 && $audioEnd > $audioStart, PreviewUnavailableException::class);

        return $audioStart + (int) ceil(($audioEnd - $audioStart) * min($seconds / $duration, 1.0));
    }

    private static function writeTruncatedCopy(string $source, string $destination, int $byteLength): void
    {
        File::ensureDirectoryExists(dirname($destination));
        $temporaryDestination = $destination . '.part';

        $input = fopen($source, 'rb');
        $output = fopen($temporaryDestination, 'wb');

        try {
            stream_copy_to_stream($input, $output, $byteLength);
        } finally {
            fclose($input);
            fclose($output);
        }

        File::move($temporaryDestination, $destination);
    }

    private static function previewPath(Song $song, float $seconds, string $extension): string
    {
        $fingerprint = $song->hash ?: (string) $song->mtime;

        return artifact_path(sprintf(
            'previews/%s-%s-%s.%s',
            $song->id,
            $fingerprint,
            (int) ceil($seconds),
            $extension,
        ));
    }
}
