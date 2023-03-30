<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\User;
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
        $countries = Country::all(['id'])->toArray();
        $index = array_rand($countries);
        $country = $countries[$index]['id'];

        $users = User::all(['id'])->toArray();
        $index = array_rand($users);
        $user = $users[$index]['id'];

        return [
            'title' => ucfirst(fake()->word()),
            'conf_date' => fake()->dateTimeBetween('tomorrow', '+10 years'),
            'latitude' => fake()->numberBetween(-90, 90),
            'longitude' => fake()->numberBetween(-90, 90),
            'country_id' => $country,
            'user_id' => $user,
        ];
    }
}
