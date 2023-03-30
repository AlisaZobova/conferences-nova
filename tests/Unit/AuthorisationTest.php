<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorisationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_auth()
    {
        $user = User::factory()->create();

        $response = $this->json(
            'POST', 'api/login',
            ['email' => $user['email'], 'password' => '12345678']
        );

        $response->assertStatus(200);
    }

    public function test_return_current_user_if_auth()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->json('POST', 'api/login', ['email' => $user['email'], 'password' => '12345678']);

        $response->assertStatus(200);

        $this->assertTrue($user->id === $response->original['id']);
    }

    public function test_fail_wrong_credentials_auth()
    {
        $user = User::factory()->create();

        $response = $this->json(
            'POST', 'api/login',
            ['email' => $user['email'], 'password' => '11111111']
        );

        $response->assertStatus(422);

        $response->assertInvalid(["email" => "These credentials do not match our records."]);
    }

    public function test_fail_admin_auth()
    {
        $response = $this->json(
            'POST', 'api/login',
            ['email' => 'admin@example.com', 'password' => '12345678']
        );

        $response->assertStatus(422);

        $response->assertInvalid(["email" => "These credentials do not match our records."]);
    }
}
