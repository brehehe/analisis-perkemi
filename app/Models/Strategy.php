<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'analysis_id',
    'version',
    'summary',
    'attack_strategy',
    'counter_strategy',
    'defensive_strategy',
    'what_to_avoid',
    'priority_points',
    'generated_at',
    'approved_by',
    'approved_at',
])]
class Strategy extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'priority_points' => 'array',
            'generated_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
