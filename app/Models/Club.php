<?php

namespace App\Models;

use App\Enums\AccountStatus;
use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'city', 'province', 'status'])]
class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return ['status' => AccountStatus::class];
    }

    public function athletes(): HasMany
    {
        return $this->hasMany(Athlete::class);
    }

    public function coaches(): HasMany
    {
        return $this->hasMany(Coach::class);
    }
}
