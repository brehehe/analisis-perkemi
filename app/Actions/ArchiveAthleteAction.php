<?php

namespace App\Actions;

use App\Models\Athlete;
use App\Models\User;
use App\Support\AuditTrail;
use Illuminate\Support\Facades\DB;

class ArchiveAthleteAction
{
    public function __construct(private AuditTrail $auditTrail) {}

    public function execute(User $actor, Athlete $athlete): void
    {
        DB::transaction(function () use ($actor, $athlete): void {
            $oldValues = $athlete->toArray();
            $athlete->delete();

            $this->auditTrail->record($actor, 'athlete.archived', $athlete, $oldValues);
        });
    }
}
