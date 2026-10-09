<?php

namespace App\Models;

use App\Enums\MatchDivision;
use App\Enums\MatchResult;
use App\Enums\MatchStatus;
use App\Enums\MatchType;
use Database\Factories\MatchRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'competition_event_id',
    'athlete_id',
    'opponent_name',
    'opponent_club',
    'match_date',
    'category',
    'match_type',
    'division',
    'result',
    'athlete_score',
    'opponent_score',
    'status',
    'notes',
])]
class MatchRecord extends Model
{
    /** @use HasFactory<MatchRecordFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'match_type' => MatchType::class,
            'division' => MatchDivision::class,
            'result' => MatchResult::class,
            'status' => MatchStatus::class,
            'athlete_score' => 'integer',
            'opponent_score' => 'integer',
        ];
    }

    public function competitionEvent(): BelongsTo
    {
        return $this->belongsTo(CompetitionEvent::class);
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(Athlete::class)
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    /** @param list<string> $athleteIds */
    public function syncTeamMembers(array $athleteIds): void
    {
        $teamMembers = collect($athleteIds)->mapWithKeys(
            fn (string $athleteId, int $index): array => [$athleteId => ['position' => $index + 1]],
        );

        $this->athletes()->sync($teamMembers);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }
}
