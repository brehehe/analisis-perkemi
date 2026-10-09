<?php

namespace App\Jobs;

use App\Contracts\DownloadsYoutubeVideo;
use App\Enums\AnalysisStatus;
use App\Enums\VideoSource;
use App\Models\Analysis;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\UniqueFor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

#[UniqueFor(3600)]
class DownloadYoutubeVideoJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    /** @var list<int> */
    public array $backoff = [60, 180];

    public int $timeout = 600;

    public bool $failOnTimeout = true;

    public function __construct(public Analysis $analysis) {}

    public function uniqueId(): string
    {
        return (string) $this->analysis->getKey();
    }

    /**
     * Execute the job.
     */
    public function handle(DownloadsYoutubeVideo $downloader): void
    {
        $this->analysis->refresh()->loadMissing('video');

        if (in_array($this->analysis->status, [AnalysisStatus::Completed, AnalysisStatus::Cancelled], true)) {
            return;
        }

        if ($this->analysis->video->source !== VideoSource::Youtube) {
            throw new RuntimeException('Job unduhan hanya mendukung sumber video YouTube.');
        }

        $existingPath = $this->analysis->video->storage_path;

        if ($existingPath !== null && Storage::disk('local')->exists($existingPath)) {
            AnalyzeVideoWithOpenAiJob::dispatch($this->analysis);

            return;
        }

        $this->analysis->video->update(['processing_status' => AnalysisStatus::Downloading]);
        $this->analysis->update([
            'status' => AnalysisStatus::Downloading,
            'progress' => 10,
            'current_step' => 'Mengunduh video YouTube ke penyimpanan privat',
            'started_at' => $this->analysis->started_at ?? now(),
            'failed_at' => null,
            'error_message' => null,
        ]);

        $download = $downloader->download($this->analysis->video);

        $this->analysis->video->update([
            ...$download,
            'processing_status' => AnalysisStatus::Queued,
        ]);
        $this->analysis->update([
            'status' => AnalysisStatus::Queued,
            'progress' => 18,
            'current_step' => 'Video YouTube siap; masuk antrean analisis OpenAI',
        ]);

        AnalyzeVideoWithOpenAiJob::dispatch($this->analysis);
    }

    public function failed(?Throwable $exception): void
    {
        $analysis = $this->analysis->fresh();

        if ($analysis === null) {
            return;
        }

        $binaryMissing = str_contains($exception?->getMessage() ?? '', 'No such file or directory')
            || str_contains($exception?->getMessage() ?? '', 'not found');

        $analysis->update([
            'status' => AnalysisStatus::Failed,
            'failed_at' => now(),
            'error_message' => $binaryMissing
                ? 'Pengunduh YouTube belum tersedia. Instal yt-dlp lalu jalankan ulang analisis.'
                : 'Video YouTube gagal diunduh. Pastikan videonya publik dan dapat diputar.',
            'current_step' => 'Pengunduhan video YouTube gagal',
            'retry_count' => $analysis->retry_count + 1,
        ]);
        $analysis->video()->update(['processing_status' => AnalysisStatus::Failed]);

        Log::error('YouTube video download failed.', [
            'analysis_id' => $analysis->getKey(),
            'video_id' => $analysis->video_id,
            'exception_class' => $exception === null ? null : $exception::class,
            'exception_message' => Str::limit($exception?->getMessage() ?? 'Unknown failure', 500),
        ]);
    }
}
