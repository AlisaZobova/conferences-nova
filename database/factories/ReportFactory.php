<?php

namespace Database\Factories;

use App\Models\Conference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'Announcer');
        }
        )->get(['id'])->toArray();

        $index = array_rand($users);
        $user = $users[$index]['id'];

        $conferences = Conference::all()->toArray();
        $index = array_rand($conferences);
        $conferenceId = $conferences[$index]['id'];

        $conference = $conferences[$index];

        return [
            'user_id' => $user,
            'conference_id' => $conferenceId,
            'topic' => fake()->word(),
            'start_time' => substr($conference['conf_date'], 0, 10) . ' ' . fake()->time(),
            'end_time' => substr($conference['conf_date'], 0, 10) . ' ' . fake()->time(),
            'description' => fake()->sentence(),
            'presentation' => null,
        ];
    }
}
