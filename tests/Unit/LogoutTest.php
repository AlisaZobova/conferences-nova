<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    public function test_successful_logout () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->json('POST', 'api/logout');

        $response->assertStatus(200);
    }

    public function test_fail_logout_when_no_auth () {
        $response = $this->json('POST', 'api/logout');

        $response->assertStatus(401);
    }
}
