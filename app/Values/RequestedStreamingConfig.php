<?php

namespace App\Values;

final readonly class RequestedStreamingConfig
{
    private function __construct(
        public bool $transcode,
        public ?int $bitRate,
        public float $startTime,
        public ?int $previewSeconds,
    ) {}

    /**
     * @param ?int $previewSeconds If set, only a preview of this many seconds is streamed.
     */
    public static function make(
        bool $transcode = false,
        ?int $bitRate = 128,
        float $startTime = 0.0,
        ?int $previewSeconds = null,
    ): self {
        return new self($transcode, $bitRate, $startTime, $previewSeconds);
    }
}
