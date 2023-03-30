<?php

namespace Tests\Unit;

use App\Mail\AdminDeleteConference;
use App\Models\Conference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminDeleteConferenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_conference_delete()
    {
        Mail::fake();

        $admin = User::whereHas(
            'roles', function ($q) {
            $q->where('name', 'Admin');
        }
        )->first();

        $conference = $this->getConferenceWithListener();

        $response = $this->actingAs($admin)
            ->json('DELETE', 'nova-api/conferences?resources[]=' . $conference->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted($conference);

        Mail::assertQueued(AdminDeleteConference::class);
    }

    public function test_delete_non_existent_conference()
    {
        $admin = User::whereHas(
            'roles', function ($q) {
            $q->where('name', 'Admin');
        }
        )->first();

        $conferenceId = Conference::withTrashed()->orderBy('id', 'DESC')->first()->id + 1;

        $this->assertTrue(!Conference::find($conferenceId));

        $response = $this->actingAs($admin)->json('DELETE', 'nova-api/conferences?resources[]=' . $conferenceId);

        $response->assertStatus(200);
    }

    public function test_fail_no_auth()
    {
        $conference = Conference::factory()->create();

        $response = $this->json('DELETE', 'nova-api/conferences?resources[]=' . $conference->id);

        $response->assertStatus(401);

        $this->assertModelExists($conference);
    }

    public function test_fail_no_admin()
    {
        $user = User::factory()->create();

        $conference = Conference::factory()->create();

        $response = $this->actingAs($user)->json('DELETE', 'nova-api/conferences?resources[]=' . $conference->id);

        $response->assertStatus(403);

        $this->assertModelExists($conference);
    }

    public function getConferenceWithListener()
    {
        $listener = User::factory()->create_listener();

        $listener->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')->create();

        $conference = Conference::factory()->create();

        $this->actingAs($listener)->json('POST', 'api/conferences/' . $conference->id . '/join');

        return $conference;
    }
}
