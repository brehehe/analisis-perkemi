<?php

namespace App\Contracts;

use App\Models\Video;

interface DownloadsYoutubeVideo
{
    /**
     * @return array{storage_path: string, original_name: string, mime_type: string, file_size: int}
     */
    public function download(Video $video): array;
}
