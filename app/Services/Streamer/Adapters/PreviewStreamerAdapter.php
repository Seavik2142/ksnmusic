<?php

namespace App\Services\Streamer\Adapters;

use App\Http\Responses\StreamedFileResponse;
use App\Models\Song;
use App\Services\Store\PreviewGenerator;
use App\Services\Streamer\Adapters\Concerns\StreamsLocalPath;
use App\Values\RequestedStreamingConfig;

class PreviewStreamerAdapter implements StreamerAdapter
{
    use StreamsLocalPath;

    public function __construct(
        private readonly PreviewGenerator $previewGenerator,
    ) {}

    public function stream(Song $song, ?RequestedStreamingConfig $config = null): StreamedFileResponse
    {
        $seconds = $config?->previewSeconds ?: (int) config('koel.store.preview_seconds');

        return self::streamLocalPath($this->previewGenerator->getPreviewPath($song, $seconds));
    }
}
