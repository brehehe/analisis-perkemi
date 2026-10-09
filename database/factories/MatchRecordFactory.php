<?php

namespace Database\Factories;

use App\Models\Athlete;
use App\Models\CompetitionEvent;
use App\Models\MatchRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatchRecord>
 */
class MatchRecordFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (MatchRecord $matchRecord): void {
            if (! $matchRecord->athletes()->whereKey($matchRecord->athlete_id)->exists()) {
                $matchRecord->syncTeamMembers([$matchRecord->athlete_id]);
            }
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'competition_event_id' => CompetitionEvent::factory(),
            'athlete_id' => Athlete::factory()->state(['gender' => 'male']),
            'opponent_name' => fake()->name(),
            'opponent_club' => fake()->company().' Dojo',
            'match_date' => fake()->dateTimeBetween('-3 months', '+1 month'),
            'category' => 'Randori Putra',
            'match_type' => 'randori',
            'division' => 'male',
            'result' => 'pending',
            'status' => 'scheduled',
            'notes' => null,
        ];
    }
}
