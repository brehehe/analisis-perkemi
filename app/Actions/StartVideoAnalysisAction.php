<?php

namespace App\Actions;

use App\Enums\AnalysisStatus;
use App\Enums\MatchStatus;
use App\Enums\VideoSource;
use App\Jobs\PrepareVideoAnalysisJob;
use App\Models\Analysis;
use App\Models\MatchRecord;
use App\Models\User;
use App\Models\Video;
use App\Support\AuditTrail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StartVideoAnalysisAction
{
    public function __construct(private AuditTrail $auditTrail) {}

    /**
     * @param  array{source: string, youtube_url?: string|null, video_file?: UploadedFile|null, focus_description?: string|null}  $attributes
     */
    public function execute(User $actor, MatchRecord $matchRecord, array $attributes): Analysis
    {
        $source = VideoSource::from($attributes['source']);
        $uploadedFile = $attributes['video_file'] ?? null;
        $storagePath = null;

        if ($source === VideoSource::Upload && $uploadedFile instanceof UploadedFile) {
            $storagePath = $uploadedFile->store("match-videos/{$matchRecord->getKey()}", 'local');
        }

        try {
            $analysis = DB::transaction(function () use (
                $actor,
                $matchRecord,
                $attributes,
                $source,
                $uploadedFile,
                $storagePath,
            ): Analysis {
                $video = Video::query()->create([
                    'match_record_id' => $matchRecord->getKey(),
                    'source' => $source,
                    'original_name' => $uploadedFile?->getClientOriginalName(),
                    'storage_path' => $storagePath,
                    'external_url' => $attributes['youtube_url'] ?? null,
                    'focus_description' => $attributes['focus_description'] ?? null,
                    'mime_type' => $uploadedFile?->getMimeType(),
                    'file_size' => $uploadedFile?->getSize(),
                    'processing_status' => AnalysisStatus::Uploaded,
                ]);

                $analysis = Analysis::query()->create([
                    'match_record_id' => $matchRecord->getKey(),
                    'video_id' => $video->getKey(),
                    'athlete_id' => $matchRecord->athlete_id,
                    'status' => AnalysisStatus::Queued,
                    'progress' => 0,
                    'current_step' => 'Menunggu validasi video',
                ]);

                $matchRecord->update(['status' => MatchStatus::InAnalysis]);

                $this->auditTrail->record(
                    $actor,
                    'analysis.queued',
                    $analysis,
                    newValues: $analysis->toArray(),
                );

                PrepareVideoAnalysisJob::dispatch($analysis)->afterCommit();

                return $analysis;
            });
        } catch (Throwable $exception) {
            if ($storagePath !== null) {
                Storage::disk('local')->delete($storagePath);
            }

            throw $exception;
        }

        return $analysis;
    }
}
