<?php

namespace App\Models;

use App\Enums\ValidationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'analysis_id',
    'event_type',
    'occurred_at_ms',
    'confidence',
    'title',
    'description',
    'evidence',
    'validation_status',
    'validated_by',
    'validated_at',
])]
class AnalysisEvent extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'occurred_at_ms' => 'integer',
            'confidence' => 'decimal:4',
            'evidence' => 'array',
            'validation_status' => ValidationStatus::class,
            'validated_at' => 'datetime',
        ];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
