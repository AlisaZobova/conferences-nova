<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conference>
 */
class ConferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => ucfirst(fake()->word()),
            'conf_date' => fake()->date(),
            'latitude' => fake()->numberBetween(-90, 90),
            'longitude' => fake()->numberBetween(-90, 90),
            'country_id' => fake()->numberBetween(1, 10),
            'user_id' => fake()->numberBetween(1, 10),
        ];
    }
}
