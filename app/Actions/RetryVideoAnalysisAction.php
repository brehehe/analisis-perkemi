<?php

namespace App\Actions;

use App\Enums\AnalysisStatus;
use App\Jobs\PrepareVideoAnalysisJob;
use App\Models\Analysis;
use App\Models\User;
use App\Support\AuditTrail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RetryVideoAnalysisAction
{
    public function __construct(private AuditTrail $auditTrail) {}

    public function execute(User $actor, Analysis $analysis, ?string $focusDescription = null): void
    {
        if (! in_array($analysis->status, [AnalysisStatus::Failed, AnalysisStatus::Completed], true)) {
            throw ValidationException::withMessages([
                'analysis' => 'Hanya analisis yang selesai atau gagal yang dapat dijalankan ulang.',
            ]);
        }

        DB::transaction(function () use ($actor, $analysis, $focusDescription): void {
            $analysis->loadMissing('video');
            $oldValues = $analysis->only([
                'status',
                'progress',
                'current_step',
                'failed_at',
                'error_message',
            ]);

            $analysis->update([
                'status' => AnalysisStatus::Queued,
                'progress' => 0,
                'current_step' => 'Menunggu validasi ulang video',
                'started_at' => null,
                'completed_at' => null,
                'failed_at' => null,
                'error_message' => null,
            ]);
            $analysis->video->update([
                'processing_status' => AnalysisStatus::Queued,
                'focus_description' => $focusDescription,
            ]);

            $this->auditTrail->record(
                $actor,
                'analysis.retried',
                $analysis,
                oldValues: $oldValues,
                newValues: $analysis->fresh()->toArray(),
            );

            PrepareVideoAnalysisJob::dispatch($analysis)->afterCommit();
        });
    }
}
