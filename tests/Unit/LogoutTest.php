<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->json('POST', 'api/logout');

        $response->assertStatus(200);
    }

    public function test_fail_logout_when_no_auth()
    {
        $response = $this->json('POST', 'api/logout');

        $response->assertStatus(401);
    }
}
