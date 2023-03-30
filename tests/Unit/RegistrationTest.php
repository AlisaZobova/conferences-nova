<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

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

        $this->assertModelExists($response->original);
    }

    public function test_fail_password_confirmation()
    {
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

    public function test_fail_existing_email()
    {
        $user = [
            'email' => 'admin@example.com',
            'password' => '11111111',
            'password_confirmation' => '11111111',
            'type' => 'Announcer'
        ];

        $response = $this->json('POST', 'api/register', $user);

        $response->assertStatus(422);

        $response->assertInvalid(['email' => 'The email has already been taken.']);
    }

    public function test_return_current_user_if_auth()
    {
        $user = User::factory()->create();

        $newUser = [
            'email' => fake()->unique()->safeEmail(),
            'password' => '11111111',
            'password_confirmation' => '11111111',
            'type' => 'Listener'
        ];

        $response = $this->actingAs($user)
            ->json('POST', 'api/register', $newUser);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', ['email' => $newUser['email']]);

        $this->assertTrue($user->id === $response->original['id']);
    }

    public function test_additional_registration()
    {
        $userAd = [
            'firstname' => fake()->firstname(),
            'lastname' => fake()->lastname(),
            'birthdate' => fake()->date(),
            'phone' => fake()->phoneNumber(),
            'country' => fake()->numberBetween(1, 10),
        ];

        $responseAd = $this->json('POST', 'api/register/' . $this->getFirstStepCompleteUserId(), $userAd);

        $responseAd->assertStatus(200);

        $this->assertModelExists($responseAd->original);

        $this->assertAuthenticated();
    }

    public function test_return_current_user_if_auth_on_additional()
    {
        $user = User::factory()->create();

        $userAd = [
            'firstname' => fake()->firstname(),
            'lastname' => fake()->lastname(),
            'birthdate' => fake()->date(),
            'phone' => fake()->phoneNumber(),
            'country' => fake()->numberBetween(1, 10),
        ];

        $responseAd = $this->actingAs($user)
            ->json('POST', 'api/register/' . $this->getFirstStepCompleteUserId(), $userAd);

        $responseAd->assertStatus(200);

        $this->assertTrue($user->id === $responseAd->original['id']);
    }

    public function test_invalid_birthdate()
    {
        $tomorrow = new \DateTime('tomorrow');

        $userAd = [
            'firstname' => fake()->firstname(),
            'lastname' => fake()->lastname(),
            'birthdate' => $tomorrow->format('Y-m-d'),
            'phone' => fake()->phoneNumber(),
            'country' => fake()->numberBetween(1, 10),
        ];

        $responseAd = $this->json('POST', 'api/register/' . $this->getFirstStepCompleteUserId(), $userAd);

        $responseAd->assertStatus(422);

        $responseAd->assertInvalid(['birthdate']);
    }

    public function getFirstStepCompleteUserId()
    {
        $user = [
            'email' => fake()->unique()->safeEmail(),
            'password' => '11111111',
            'password_confirmation' => '11111111',
            'type' => 'Listener'
        ];

        $response = $this->json('POST', 'api/register', $user);

        return $response->original->id;
    }
}
