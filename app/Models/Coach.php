<?php

namespace App\Models;

use App\Enums\AccountStatus;
use Database\Factories\CoachFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'club_id', 'identifier', 'name', 'email', 'phone', 'certifications', 'status'])]
class Coach extends Model
{
    /** @use HasFactory<CoachFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return ['status' => AccountStatus::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function athletes(): HasMany
    {
        return $this->hasMany(Athlete::class);
    }
}
