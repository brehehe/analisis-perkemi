<?php

namespace Database\Factories;

use App\Models\Analysis;
use App\Models\Athlete;
use App\Models\MatchRecord;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Analysis>
 */
class AnalysisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'match_record_id' => MatchRecord::factory(),
            'video_id' => Video::factory(),
            'athlete_id' => Athlete::factory(),
            'status' => 'queued',
            'progress' => 0,
            'current_step' => 'Menunggu antrean',
        ];
    }
}
