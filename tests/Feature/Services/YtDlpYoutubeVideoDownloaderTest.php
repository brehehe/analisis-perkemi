<?php

use App\Models\Video;
use App\Services\YtDlpYoutubeVideoDownloader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

it('stores the video created by yt-dlp in private storage', function () {
    Storage::fake('local');
    $binary = Storage::disk('local')->path('fake-yt-dlp');
    file_put_contents($binary, <<<'SH'
#!/bin/sh
output=''
while [ "$#" -gt 0 ]; do
    if [ "$1" = '--output' ]; then
        shift
        output="$1"
    fi
    shift
done
target="${output%.*}.mp4"
mkdir -p "$(dirname "$target")"
printf 'fake-video-content' > "$target"
SH);
    chmod($binary, 0755);
    config()->set('services.youtube.yt_dlp_binary', $binary);

    $video = new Video([
        'match_record_id' => (string) Str::ulid(),
        'source' => 'youtube',
        'external_url' => 'https://www.youtube.com/watch?v=YoZGDqWRLUo',
    ]);
    $video->id = (string) Str::ulid();

    $result = (new YtDlpYoutubeVideoDownloader)->download($video);

    expect($result['storage_path'])->toEndWith('/video.mp4')
        ->and($result['original_name'])->toBe("youtube-{$video->getKey()}.mp4")
        ->and($result['file_size'])->toBe(18);
    Storage::disk('local')->assertExists($result['storage_path']);
});
