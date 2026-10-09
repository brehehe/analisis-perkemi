<?php

namespace Database\Factories;

use App\Models\CompetitionEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompetitionEvent>
 */
class CompetitionEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city().' Open Championship',
            'starts_at' => fake()->dateTimeBetween('-6 months', '+3 months'),
            'ends_at' => null,
            'venue' => fake()->streetName().' Arena',
            'city' => fake()->city(),
            'level' => fake()->randomElement(['regional', 'national', 'international']),
            'status' => 'scheduled',
        ];
    }
}
