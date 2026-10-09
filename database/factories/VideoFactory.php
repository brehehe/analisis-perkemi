<?php

namespace Database\Factories;

use App\Models\MatchRecord;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
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
            'source' => 'youtube',
            'external_url' => 'https://www.youtube.com/watch?v=YoZGDqWRLUo',
            'processing_status' => 'uploaded',
        ];
    }
}
