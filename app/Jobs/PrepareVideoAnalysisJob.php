<?php

namespace App\Jobs;

use App\Enums\AnalysisStatus;
use App\Enums\VideoSource;
use App\Models\Analysis;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\UniqueFor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

#[UniqueFor(3600)]
class PrepareVideoAnalysisJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public int $timeout = 120;

    public function __construct(public Analysis $analysis) {}

    public function uniqueId(): string
    {
        return (string) $this->analysis->getKey();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->analysis->loadMissing('video');

        if ($this->analysis->video->source === VideoSource::Upload) {
            $path = $this->analysis->video->storage_path;

            if ($path === null || ! Storage::disk('local')->exists($path)) {
                throw new RuntimeException('The uploaded video is not available in private storage.');
            }
        }

        if ($this->analysis->video->source === VideoSource::Youtube) {
            $url = $this->analysis->video->external_url;
            $host = $url === null ? null : parse_url($url, PHP_URL_HOST);

            if (! in_array($host, ['youtube.com', 'www.youtube.com', 'youtu.be', 'm.youtube.com'], true)) {
                throw new RuntimeException('The external video URL is not a supported YouTube URL.');
            }
        }

        $this->analysis->video->update(['processing_status' => AnalysisStatus::Queued]);

        if ($this->analysis->video->source === VideoSource::Upload) {
            $this->analysis->update([
                'status' => AnalysisStatus::Queued,
                'progress' => 10,
                'current_step' => 'Masuk antrean analisis OpenAI',
            ]);

            AnalyzeVideoWithOpenAiJob::dispatch($this->analysis);

            return;
        }

        $this->analysis->update([
            'status' => AnalysisStatus::Queued,
            'progress' => 5,
            'current_step' => 'URL YouTube valid; masuk antrean pengunduhan',
        ]);

        DownloadYoutubeVideoJob::dispatch($this->analysis);
    }

    public function failed(?Throwable $exception): void
    {
        $this->analysis->update([
            'status' => AnalysisStatus::Failed,
            'failed_at' => now(),
            'error_message' => 'Video tidak dapat dipersiapkan untuk analisis.',
            'current_step' => 'Persiapan video gagal',
        ]);

        $this->analysis->video()->update(['processing_status' => AnalysisStatus::Failed]);

        Log::error('Video analysis preparation failed.', [
            'analysis_id' => $this->analysis->getKey(),
            'video_id' => $this->analysis->video_id,
            'exception' => $exception,
        ]);
    }
}
