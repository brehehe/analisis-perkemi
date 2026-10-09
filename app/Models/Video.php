<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use App\Enums\VideoSource;
use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'match_record_id',
    'source',
    'original_name',
    'storage_path',
    'external_url',
    'focus_description',
    'mime_type',
    'file_size',
    'duration_seconds',
    'resolution',
    'fps',
    'codec',
    'processing_status',
])]
class Video extends Model
{
    /** @use HasFactory<VideoFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'source' => VideoSource::class,
            'processing_status' => AnalysisStatus::class,
            'file_size' => 'integer',
            'duration_seconds' => 'integer',
            'fps' => 'decimal:2',
        ];
    }

    public function matchRecord(): BelongsTo
    {
        return $this->belongsTo(MatchRecord::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }
}
