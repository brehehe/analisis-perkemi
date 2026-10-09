<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'analysis_id',
    'athlete_id',
    'priority',
    'drill',
    'frequency',
    'duration_minutes',
    'target_metric',
    'target_score',
])]
class TrainingRecommendation extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'target_score' => 'decimal:2',
        ];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }
}
