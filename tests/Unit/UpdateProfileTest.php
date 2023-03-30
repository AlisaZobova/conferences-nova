<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_update()
    {
        $user = $this->getUser();

        $newFields = $this->getNewFields($user->id);

        $response = $this->actingAs($user)->json('POST', 'api/profile', $newFields);

        $response->assertStatus(200);

        $this->assertModelExists($response->original);
    }

    public function test_fail_update_by_another_user()
    {
        $user = $this->getUser();

        $newFields = $this->getNewFields($user->id);

        $anotherUser = $this->getUser();

        $response = $this->actingAs($anotherUser)->json('POST', 'api/profile', $newFields);

        $response->assertStatus(403);
    }

    public function test_fail_update_with_invalid_data()
    {
        $user = $this->getUser();

        $newFields = $this->getInvalidFields($user->id);

        $response = $this->actingAs($user)->json('POST', 'api/profile', $newFields);

        $response->assertStatus(422);

        $response->assertInvalid(
            [
                'email' => 'This email already exists',
                'password' => 'The password confirmation does not match.',
                'birthdate' => 'The birthdate is not a valid date.'
            ]
        );
    }

    public function getUser()
    {
        return User::factory()->create();
    }

    public function getNewFields($id)
    {
        $countries = Country::all(['id'])->toArray();
        $index = array_rand($countries);
        $country = $countries[$index]['id'];

        return [
            'id' => $id,
            'email' => fake()->unique()->safeEmail(),
            'firstname' => fake()->firstname(),
            'lastname' => fake()->lastname(),
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'birthdate' => fake()->date(),
            'phone' => fake()->phoneNumber(),
            'country_id' => $country,
        ];
    }

    public function getInvalidFields($id)
    {
        $user = $this->getUser();
        $countries = Country::all(['id'])->toArray();
        $index = array_rand($countries);
        $country = $countries[$index]['id'];

        return [
            'id' => $id,
            'firstname' => fake()->firstname(),
            'lastname' => fake()->lastname(),
            'email' => $user->email,
            'password' => '12345678',
            'password_confirmation' => '123456789',
            'birthdate' => fake()->word(),
            'phone' => fake()->phoneNumber(),
            'country_id' => $country,
        ];
    }
}
