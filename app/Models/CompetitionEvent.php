<?php

namespace App\Models;

use Database\Factories\CompetitionEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'starts_at', 'ends_at', 'venue', 'city', 'level', 'status'])]
class CompetitionEvent extends Model
{
    /** @use HasFactory<CompetitionEventFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchRecord::class);
    }
}
