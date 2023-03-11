<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'firstname' => fake()->firstname(),
            'lastname' => fake()->lastname(),
            'password' => Hash::make(12345678),
            'birthdate' =>  fake()->date(),
            'phone' => fake()->phoneNumber(),
            'country_id' => fake()->numberBetween(1, 10),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function create($attributes = [], ?Model $parent = null)
    {
        $role = array_rand(['Listener' => '', 'Announcer' => '']);
        $user = $this->model::create($this->definition());
        $user->assignRole($role);
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(
            fn (array $attributes) => [
            'email_verified_at' => null,
            ]
        );
    }
}
