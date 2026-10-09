<?php

namespace App\Models;

use App\Enums\ValidationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'analysis_id',
    'analysis_event_id',
    'opportunity_type',
    'occurred_at_ms',
    'confidence',
    'trigger',
    'explanation',
    'recommended_action',
    'evidence',
    'validation_status',
])]
class PointOpportunity extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'occurred_at_ms' => 'integer',
            'confidence' => 'decimal:4',
            'evidence' => 'array',
            'validation_status' => ValidationStatus::class,
        ];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }

    public function analysisEvent(): BelongsTo
    {
        return $this->belongsTo(AnalysisEvent::class);
    }
}
