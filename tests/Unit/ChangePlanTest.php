<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\User;
use Tests\TestCase;

class ChangePlanTest extends TestCase
{
    public function test_successful_change_plan()
    {
        $user = User::factory()->create();

        $user->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')->create();

        $plan = Plan::find(3);

        $response = $this->actingAs($user)->json(
            'POST', 'api/subscription',
            ['plan' => $plan, 'paymentMethodId' => 'pm_card_visa']
        );

        $response->assertStatus(200);

        $this->assertTrue($user->getAttributeValue('active_subscription')->name === $plan->name);
    }

    public function test_fail_change_plan_with_declined_card()
    {
        $user = User::factory()->create();

        $user->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')->create();

        $plan = Plan::find(3);

        $response = $this->actingAs($user)->json(
            'POST', 'api/subscription',
            ['plan' => $plan, 'paymentMethodId' => 'pm_card_visa_chargeDeclined']
        );

        $response->assertStatus(500);

        $this->assertTrue($response->original['message'] === "Your card was declined.");

        $this->assertTrue($user->getAttributeValue('active_subscription')->name === 'Free');
    }

    public function test_admin_fail_change_plan()
    {
        $admin = User::whereHas(
            'roles', function ($q) {
                $q->where('name', 'Admin');
            }
        )->first();

        $plan = Plan::find(3);

        $response = $this->actingAs($admin)->json(
            'POST', 'api/subscription',
            ['plan' => $plan, 'paymentMethodId' => 'pm_card_visa']
        );

        $response->assertStatus(403);
    }
}
