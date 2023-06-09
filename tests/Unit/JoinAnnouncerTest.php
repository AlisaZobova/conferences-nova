<?php

namespace Tests\Unit;

use App\Mail\JoinAnnouncer;
use App\Models\Conference;
use App\Models\Plan;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class JoinAnnouncerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->seed();
    }

    public function test_successful_join()
    {
        Mail::fake();

        $announcer = $this->getAnnouncer();
        $conference = $this->getConferenceWithListener();

        $this->createReport($announcer, $conference);

        $response = $this->actingAs($announcer)->json('POST', 'api/conferences/' . $conference->id . '/join');

        $response->assertStatus(200);

        $this->assertTrue(
            $announcer->joinedConferences()
                ->where('conference_id', $conference->id)
                ->get()
                ->count() === 1
        );

        Mail::assertQueued(JoinAnnouncer::class);
    }

    public function test_fail_join_on_plan_limit()
    {
        Mail::fake();

        $announcer = $this->getAnnouncerWithSubscription();

        $conference = $this->getConferenceWithListener();
        $this->createReport($announcer, $conference);
        $response = $this->actingAs($announcer)->json('POST', 'api/conferences/' . $conference->id . '/join');

        $response->assertStatus(500);

        $response->assertInvalid(['plan' => 'The available monthly joins for the current plan have run out!']);

        $this->assertTrue(
            $announcer->joinedConferences()
                ->where('conference_id', $conference->id)
                ->get()
                ->count() === 0
        );

        Mail::assertNotQueued(JoinAnnouncer::class);
    }

    public function getConferenceWithListener()
    {
        $listener = User::factory()->create_listener();

        $listener->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')->create();

        $conference = Conference::factory()->create();

        $this->actingAs($listener)->json('POST', 'api/conferences/' . $conference->id . '/join');

        return $conference;
    }

    public function getAnnouncer()
    {
        $announcer = User::factory()->create_announcer();
        $announcer->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')->create();

        return $announcer;
    }

    public function createReport($announcer, $conference)
    {
        Report::factory()->create(
            [
                'user_id' => $announcer->id,
                'conference_id' => $conference->id,
                'start_time' => $conference->conf_date->format('Y-m-d') . ' 12:00:00',
                'end_time' => $conference->conf_date->format('Y-m-d') . ' 12:30:00'
            ]
        );
    }

    public function getAnnouncerWithSubscription()
    {
        $announcer = $this->getAnnouncer();

        $conference = Conference::factory()->create();
        $this->createReport($announcer, $conference);
        $this->actingAs($announcer)->json('POST', 'api/conferences/' . $conference->id . '/join');

        return $announcer;
    }
}
