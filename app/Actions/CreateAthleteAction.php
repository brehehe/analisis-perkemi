<?php

namespace App\Actions;

use App\Models\Athlete;
use App\Models\User;
use App\Support\AuditTrail;
use Illuminate\Support\Facades\DB;

class CreateAthleteAction
{
    public function __construct(private AuditTrail $auditTrail) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $actor, array $attributes): Athlete
    {
        return DB::transaction(function () use ($actor, $attributes): Athlete {
            $athlete = Athlete::query()->create($attributes);

            $this->auditTrail->record($actor, 'athlete.created', $athlete, newValues: $athlete->toArray());

            return $athlete;
        });
    }
}
