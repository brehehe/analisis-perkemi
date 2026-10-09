<?php

namespace Database\Factories;

use App\Models\Athlete;
use App\Models\Club;
use App\Models\Coach;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Athlete>
 */
class AthleteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'coach_id' => Coach::factory(),
            'identifier' => fake()->unique()->bothify('ATL-#####'),
            'name' => fake()->name(),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-30 years', '-16 years'),
            'category' => fake()->randomElement(['Randori Putra', 'Randori Putri', 'Embu Pasangan']),
            'weight_class' => fake()->randomFloat(2, 45, 90),
            'experience_years' => fake()->numberBetween(1, 12),
            'status' => 'active',
        ];
    }
}
