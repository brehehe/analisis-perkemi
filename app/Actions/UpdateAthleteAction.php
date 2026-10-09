<?php

namespace App\Actions;

use App\Models\Athlete;
use App\Models\User;
use App\Support\AuditTrail;
use Illuminate\Support\Facades\DB;

class UpdateAthleteAction
{
    public function __construct(private AuditTrail $auditTrail) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $actor, Athlete $athlete, array $attributes): Athlete
    {
        return DB::transaction(function () use ($actor, $athlete, $attributes): Athlete {
            $oldValues = $athlete->toArray();
            $athlete->update($attributes);

            $this->auditTrail->record(
                $actor,
                'athlete.updated',
                $athlete,
                $oldValues,
                $athlete->fresh()->toArray(),
            );

            return $athlete;
        });
    }
}
