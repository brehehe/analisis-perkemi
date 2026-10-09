<?php

use App\Contracts\DownloadsYoutubeVideo;
use App\Enums\AnalysisStatus;
use App\Jobs\AnalyzeVideoWithOpenAiJob;
use App\Jobs\DownloadYoutubeVideoJob;
use App\Models\Analysis;
use App\Models\Athlete;
use App\Models\MatchRecord;
use App\Models\Video;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\mock;

it('retries a failed YouTube download and dispatches OpenAI analysis', function () {
    Queue::fake([AnalyzeVideoWithOpenAiJob::class]);
    $athlete = Athlete::factory()->create();
    $match = MatchRecord::factory()->create(['athlete_id' => $athlete->getKey()]);
    $video = Video::factory()->create([
        'match_record_id' => $match->getKey(),
        'source' => 'youtube',
        'external_url' => 'https://www.youtube.com/watch?v=YoZGDqWRLUo',
        'storage_path' => null,
        'processing_status' => 'failed',
    ]);
    $analysis = Analysis::factory()->create([
        'match_record_id' => $match->getKey(),
        'video_id' => $video->getKey(),
        'athlete_id' => $athlete->getKey(),
        'status' => 'failed',
        'progress' => 10,
        'failed_at' => now(),
        'error_message' => 'yt-dlp belum tersedia.',
    ]);
    $downloader = mock(DownloadsYoutubeVideo::class);
    $downloader->shouldReceive('download')
        ->once()
        ->withArgs(fn (Video $receivedVideo): bool => $receivedVideo->is($video))
        ->andReturn([
            'storage_path' => "match-videos/{$match->getKey()}/video.mp4",
            'original_name' => 'youtube-video.mp4',
            'mime_type' => 'video/mp4',
            'file_size' => 2048,
        ]);

    (new DownloadYoutubeVideoJob($analysis))->handle($downloader);

    $analysis->refresh();
    $video->refresh();
    expect($analysis->status)->toBe(AnalysisStatus::Queued)
        ->and($analysis->progress)->toBe(18)
        ->and($analysis->current_step)->toBe('Video YouTube siap; masuk antrean analisis OpenAI')
        ->and($analysis->failed_at)->toBeNull()
        ->and($analysis->error_message)->toBeNull()
        ->and($video->storage_path)->toBe("match-videos/{$match->getKey()}/video.mp4")
        ->and($video->file_size)->toBe(2048);
    Queue::assertPushed(AnalyzeVideoWithOpenAiJob::class, fn (AnalyzeVideoWithOpenAiJob $job): bool => $job->analysis->is($analysis));
});
