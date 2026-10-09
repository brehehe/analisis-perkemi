<?php

namespace App\Actions;

use App\Enums\MatchDivision;
use App\Enums\MatchType;
use App\Models\MatchRecord;
use App\Models\User;
use App\Support\AuditTrail;
use Illuminate\Support\Facades\DB;

class CreateMatchRecordAction
{
    public function __construct(private AuditTrail $auditTrail) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $actor, array $attributes): MatchRecord
    {
        return DB::transaction(function () use ($actor, $attributes): MatchRecord {
            /** @var list<string> $athleteIds */
            $athleteIds = array_values($attributes['athlete_ids']);
            $matchType = MatchType::from($attributes['match_type']);
            $division = MatchDivision::from($attributes['division']);

            unset($attributes['athlete_ids']);
            $attributes['athlete_id'] = $athleteIds[0];
            $attributes['category'] = $matchType->categoryLabel($division);

            if (! $matchType->hasOpponent()) {
                $attributes['opponent_name'] = null;
                $attributes['opponent_club'] = null;
                $attributes['opponent_score'] = null;
            }

            $matchRecord = MatchRecord::query()->create($attributes);
            $matchRecord->syncTeamMembers($athleteIds);

            $this->auditTrail->record(
                $actor,
                'match.created',
                $matchRecord,
                newValues: $matchRecord->toArray(),
            );

            return $matchRecord->load('athletes');
        });
    }
}
