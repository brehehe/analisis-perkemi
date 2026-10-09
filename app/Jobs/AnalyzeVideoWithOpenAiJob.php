<?php

namespace App\Jobs;

use App\Actions\PersistOpenAiAnalysisAction;
use App\Contracts\AnalyzesMatchVideo;
use App\Contracts\ExtractsVideoFrames;
use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\UniqueFor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

#[UniqueFor(3600)]
class AnalyzeVideoWithOpenAiJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    /** @var list<int> */
    public array $backoff = [60, 180];

    public int $timeout = 300;

    public function __construct(public Analysis $analysis) {}

    public function uniqueId(): string
    {
        return (string) $this->analysis->getKey();
    }

    /**
     * Execute the job.
     */
    public function handle(
        ExtractsVideoFrames $frameExtractor,
        AnalyzesMatchVideo $videoAnalyzer,
        PersistOpenAiAnalysisAction $persistAnalysis,
    ): void {
        $this->analysis->refresh()->loadMissing('video');

        if (in_array($this->analysis->status, [AnalysisStatus::Completed, AnalysisStatus::Cancelled], true)) {
            return;
        }

        $storagePath = $this->analysis->video->storage_path;

        if ($storagePath === null || ! Storage::disk('local')->exists($storagePath)) {
            throw new RuntimeException('File video privat tidak tersedia untuk analisis OpenAI.');
        }

        $this->analysis->video->update(['processing_status' => AnalysisStatus::Preprocessing]);
        $this->analysis->update([
            'status' => AnalysisStatus::Preprocessing,
            'progress' => 20,
            'current_step' => 'Mengekstrak frame representatif dengan FFmpeg',
            'started_at' => $this->analysis->started_at ?? now(),
            'failed_at' => null,
            'error_message' => null,
        ]);

        try {
            $frames = $frameExtractor->extract(
                Storage::disk('local')->path($storagePath),
                (string) $this->analysis->getKey(),
            );

            $this->analysis->update([
                'status' => AnalysisStatus::PerformanceAnalysis,
                'progress' => 60,
                'current_step' => 'GPT-6 Sol menyusun AI Match Intelligence',
            ]);

            $result = $videoAnalyzer->analyze($this->analysis, $frames);

            $this->analysis->update([
                'status' => AnalysisStatus::StrategyGeneration,
                'progress' => 85,
                'current_step' => 'Menyimpan profil, timeline, strategi, dan program latihan',
            ]);

            try {
                $persistAnalysis->execute($this->analysis, $result);
            } catch (ValidationException $exception) {
                Log::error('OpenAI analysis response validation failed.', [
                    'analysis_id' => $this->analysis->getKey(),
                    'video_id' => $this->analysis->video_id,
                    'validation_errors' => $exception->errors(),
                    'response_shape' => $this->responseShape($result),
                ]);

                throw $exception;
            }
        } finally {
            $frameExtractor->cleanup((string) $this->analysis->getKey());
        }
    }

    public function failed(?Throwable $exception): void
    {
        $analysis = $this->analysis->fresh();

        if ($analysis === null) {
            return;
        }

        $configurationMissing = str_contains($exception?->getMessage() ?? '', 'API key');

        $analysis->update([
            'status' => AnalysisStatus::Failed,
            'failed_at' => now(),
            'error_message' => $configurationMissing
                ? 'OpenAI belum dikonfigurasi. Tambahkan API key baru lalu jalankan ulang analisis.'
                : 'Analisis AI gagal. Periksa worker, FFmpeg, dan koneksi OpenAI sebelum mencoba lagi.',
            'current_step' => 'Analisis OpenAI gagal',
            'retry_count' => $analysis->retry_count + 1,
        ]);

        $analysis->video()->update(['processing_status' => AnalysisStatus::Failed]);

        Log::error('OpenAI video analysis failed.', [
            'analysis_id' => $analysis->getKey(),
            'video_id' => $analysis->video_id,
            'exception_class' => $exception === null ? null : $exception::class,
            'exception_message' => Str::limit($exception?->getMessage() ?? 'Unknown failure', 500),
            'validation_errors' => $exception instanceof ValidationException ? $exception->errors() : null,
        ]);
    }

    /**
     * Summarize the response without logging model-generated analysis content.
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function responseShape(array $result): array
    {
        $shape = ['keys' => array_keys($result)];

        foreach (['overall_score', 'match_intelligence', 'metrics', 'events', 'opportunities', 'strategy', 'training_recommendations'] as $key) {
            $value = $result[$key] ?? null;
            $shape[$key] = [
                'type' => get_debug_type($value),
                'count' => is_countable($value) ? count($value) : null,
            ];
        }

        return $shape;
    }
}
