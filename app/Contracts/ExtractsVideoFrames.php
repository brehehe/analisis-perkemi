<?php

namespace App\Contracts;

interface ExtractsVideoFrames
{
    /**
     * @return list<array{index: int, timestamp_seconds: float, absolute_path: string, mime_type: string}>
     */
    public function extract(string $videoPath, string $analysisId): array;

    public function cleanup(string $analysisId): void;
}
