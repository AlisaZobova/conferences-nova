<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $users = User::all(['id'])->toArray();

        $index = array_rand($users);
        $user = $users[$index]['id'];

        $reports = Report::all(['id'])->toArray();
        $index = array_rand($reports);
        $report = $reports[$index]['id'];

        return [
            'user_id' => $user,
            'report_id' => $report,
            'content' => fake()->sentence(),
            'publication_date' => fake()->dateTime()
        ];
    }
}
