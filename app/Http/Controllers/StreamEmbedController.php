<?php

namespace App\Http\Controllers;

use App\Models\Embed;
use App\Models\Song;
use App\Services\Store\EntitlementService;
use App\Services\Streamer\Streamer;
use App\Values\RequestedStreamingConfig;

class StreamEmbedController extends Controller
{
    public function __invoke(EntitlementService $entitlementService, Embed $embed, Song $song)
    {
        $config = RequestedStreamingConfig::make(previewSeconds: $entitlementService->previewSecondsFor(null, $song));

        return (new Streamer(song: $song, config: $config))->stream();
    }
}
