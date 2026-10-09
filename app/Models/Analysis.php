<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use Database\Factories\AnalysisFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'match_record_id',
    'video_id',
    'athlete_id',
    'status',
    'progress',
    'current_step',
    'model_version',
    'overall_score',
    'match_intelligence',
    'started_at',
    'completed_at',
    'failed_at',
    'error_message',
    'retry_count',
])]
class Analysis extends Model
{
    /** @use HasFactory<AnalysisFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'status' => AnalysisStatus::class,
            'progress' => 'integer',
            'overall_score' => 'decimal:2',
            'match_intelligence' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function matchRecord(): BelongsTo
    {
        return $this->belongsTo(MatchRecord::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(AnalysisMetric::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AnalysisEvent::class)->orderBy('occurred_at_ms');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(PointOpportunity::class)->orderBy('occurred_at_ms');
    }

    public function latestStrategy(): HasOne
    {
        return $this->hasOne(Strategy::class)->latestOfMany('version');
    }

    public function strategies(): HasMany
    {
        return $this->hasMany(Strategy::class);
    }

    public function trainingRecommendations(): HasMany
    {
        return $this->hasMany(TrainingRecommendation::class)
            ->orderByRaw("case priority when 'high' then 1 when 'medium' then 2 else 3 end")
            ->orderBy('id');
    }

    public function coachReport(): HasOne
    {
        return $this->hasOne(CoachReport::class);
    }
}
