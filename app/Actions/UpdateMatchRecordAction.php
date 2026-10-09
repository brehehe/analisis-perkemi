<?php

namespace App\Actions;

use App\Enums\MatchDivision;
use App\Enums\MatchType;
use App\Models\MatchRecord;
use App\Models\User;
use App\Support\AuditTrail;
use Illuminate\Support\Facades\DB;

class UpdateMatchRecordAction
{
    public function __construct(private AuditTrail $auditTrail) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $actor, MatchRecord $matchRecord, array $attributes): MatchRecord
    {
        return DB::transaction(function () use ($actor, $matchRecord, $attributes): MatchRecord {
            $oldValues = $matchRecord->toArray();
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

            $matchRecord->update($attributes);
            $matchRecord->syncTeamMembers($athleteIds);

            $this->auditTrail->record(
                $actor,
                'match.updated',
                $matchRecord,
                $oldValues,
                $matchRecord->fresh()->toArray(),
            );

            return $matchRecord->load('athletes');
        });
    }
}
