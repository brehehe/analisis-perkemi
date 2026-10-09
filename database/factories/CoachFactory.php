<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Coach;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coach>
 */
class CoachFactory extends Factory
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
            'identifier' => fake()->unique()->bothify('PLT-####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'certifications' => 'Pelatih Kempo tingkat nasional',
            'status' => 'active',
        ];
    }
}
