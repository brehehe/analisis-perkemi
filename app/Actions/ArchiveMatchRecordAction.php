<?php

namespace App\Actions;

use App\Models\MatchRecord;
use App\Models\User;
use App\Support\AuditTrail;
use Illuminate\Support\Facades\DB;

class ArchiveMatchRecordAction
{
    public function __construct(private AuditTrail $auditTrail) {}

    public function execute(User $actor, MatchRecord $matchRecord): void
    {
        DB::transaction(function () use ($actor, $matchRecord): void {
            $oldValues = $matchRecord->toArray();
            $matchRecord->delete();

            $this->auditTrail->record($actor, 'match.archived', $matchRecord, $oldValues);
        });
    }
}
