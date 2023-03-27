<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationTest extends TestCase
{

    public function test_listener_registration()
    {
        $user = [
            'email' => fake()->unique()->safeEmail(),
            'password' => '11111111',
            'password_confirmation' => '11111111',
            'type' => 'Listener'
        ];

        $response = $this->json('POST', 'api/register', $user);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => $user['email']]);

        $this->assertModelExists($response->original);
    }

    public function test_announcer_registration()
    {
        $user = [
            'email' => fake()->unique()->safeEmail(),
            'password' => '11111111',
            'password_confirmation' => '11111111',
            'type' => 'Announcer'
        ];

        $response = $this->json('POST', 'api/register', $user);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => $user['email']]);
    }

    public function test_fail_password_confirmation() {
        $user = [
            'email' => fake()->unique()->safeEmail(),
            'password' => '11111111',
            'password_confirmation' => '11111112',
            'type' => 'Announcer'
        ];

        $response = $this->json('POST', 'api/register', $user);

        $response->assertStatus(422);

        $response->assertInvalid(['password']);

        $this->assertDatabaseMissing('users', ['email' => $user['email']]);
    }

    public function test_fail_existing_email() {
        $user = [
            'email' => 'admin@example.com',
            'password' => '11111111',
            'password_confirmation' => '11111111',
            'type' => 'Announcer'
        ];

        $response = $this->json('POST', 'api/register', $user);

        $response->assertStatus(422);

        $response->assertInvalid(['email']);
    }

    public function test_no_access_if_auth() {
        $newUser = [
            'email' => fake()->unique()->safeEmail(),
            'password' => '11111111',
            'password_confirmation' => '11111111',
            'type' => 'Listener'
        ];

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->json('POST', 'api/register', $newUser);

        $response->assertStatus(500);

        $this->assertDatabaseMissing('users', ['email' => $newUser['email']]);
    }

    public function test_additional_registration() {
        $user = [
            'email' => fake()->unique()->safeEmail(),
            'password' => '11111111',
            'password_confirmation' => '11111111',
            'type' => 'Listener'
        ];

        $response = $this->json('POST', 'api/register', $user);

        $userAd = [
            'firstname' => fake()->firstname(),
            'lastname' => fake()->lastname(),
            'birthdate' =>  fake()->date(),
            'phone' => fake()->phoneNumber(),
            'country' => fake()->numberBetween(1, 10),
        ];

        $responseAd = $this->json('POST', 'api/register/' . $response->original->id, $userAd);

        $responseAd->assertStatus(200);

        $this->assertModelExists($responseAd->original);

        $this->assertAuthenticated();
    }

    public function test_invalid_birthdate() {
        $user = [
            'email' => fake()->unique()->safeEmail(),
            'password' => '11111111',
            'password_confirmation' => '11111111',
            'type' => 'Listener'
        ];

        $response = $this->json('POST', 'api/register', $user);

        $tomorrow = new \DateTime('tomorrow');

        $userAd = [
            'firstname' => fake()->firstname(),
            'lastname' => fake()->lastname(),
            'birthdate' =>  $tomorrow->format('Y-m-d'),
            'phone' => fake()->phoneNumber(),
            'country' => fake()->numberBetween(1, 10),
        ];

        $responseAd = $this->json('POST', 'api/register/' . $response->original->id, $userAd);

        $responseAd->assertStatus(422);

        $responseAd->assertInvalid(['birthdate']);
    }
}
