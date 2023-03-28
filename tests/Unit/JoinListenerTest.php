<?php

namespace Tests\Unit;

use App\Mail\JoinListener;
use App\Models\Conference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class JoinListenerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_join()
    {
        Mail::fake();

        $listener = $this->getListener();

        $conference = $this->getConferenceWithAnnouncer();

        $response = $this->actingAs($listener)->json('POST', 'api/conferences/' . $conference->id . '/join');

        $response->assertStatus(200);

        $this->assertTrue(
            $listener->joinedConferences()
                ->where('conference_id', $conference->id)
                ->get()
                ->count() === 1
        );

        Mail::assertQueued(JoinListener::class);
    }

    public function test_fail_join_on_plan_limit()
    {
        Mail::fake();

        $listener = $this->getListenerWithSubscription();

        $conference = $this->getConferenceWithAnnouncer();

        $response = $this->actingAs($listener)->json('POST', 'api/conferences/' . $conference->id . '/join');

        $response->assertStatus(500);

        $response->assertInvalid(['plan' => 'The available monthly joins for the current plan have run out!']);

        $this->assertTrue(
            $listener->joinedConferences()
                ->where('conference_id', $conference->id)
                ->get()
                ->count() === 0
        );

        Mail::assertNotQueued(JoinListener::class);
    }

    public function getConferenceWithAnnouncer()
    {
        $announcer = User::factory()->create_announcer();

        $announcer->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')->create();

        $conference = Conference::factory()->create();

        $this->actingAs($announcer)->json('POST', 'api/conferences/' . $conference->id . '/join');

        return $conference;
    }

    public function getListener()
    {
        $listener = User::factory()->create_listener();
        $listener->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')->create();

        return $listener;
    }

    public function getListenerWithSubscription()
    {
        $listener = $this->getListener();

        $conference = Conference::factory()->create();

        $this->actingAs($listener)->json('POST', 'api/conferences/' . $conference->id . '/join');

        return $listener;
    }
}
