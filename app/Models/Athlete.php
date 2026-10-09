<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\AthleteGender;
use Database\Factories\AthleteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'club_id',
    'coach_id',
    'identifier',
    'name',
    'gender',
    'date_of_birth',
    'category',
    'weight_class',
    'experience_years',
    'status',
    'profile_photo_path',
])]
class Athlete extends Model
{
    /** @use HasFactory<AthleteFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'gender' => AthleteGender::class,
            'date_of_birth' => 'date',
            'weight_class' => 'decimal:2',
            'experience_years' => 'integer',
            'status' => AccountStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function matches(): BelongsToMany
    {
        return $this->belongsToMany(MatchRecord::class)
            ->withPivot('position')
            ->withTimestamps();
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }
}
