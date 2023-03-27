<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class AuthorisationTest extends TestCase
{
    public function test_successful_auth()
    {
        $user = User::factory()->create();

        $response = $this->json(
            'POST', 'api/login',
            ['email' => $user['email'], 'password' => '12345678']
        );

        $response->assertStatus(200);
    }

    public function test_fail_auth()
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

    public function test_fail_if_auth()
    {
        $user = User::factory()->create();

        $newUser = User::factory()->create();

        $response = $this->actingAs($user)
            ->json('POST', 'api/login', ['email' => $newUser['email'], 'password' => '12345678']);

        $response->assertStatus(500);
    }
}
