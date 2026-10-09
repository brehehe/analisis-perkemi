<?php

namespace App\Contracts;

use App\Models\Analysis;

interface AnalyzesMatchVideo
{
    /**
     * @param  list<array{index: int, timestamp_seconds: float, absolute_path: string, mime_type: string}>  $frames
     * @return array<string, mixed>
     */
    public function analyze(Analysis $analysis, array $frames): array;
}
