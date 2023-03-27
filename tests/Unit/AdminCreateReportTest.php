<?php

namespace Tests\Unit;

use App\Mail\JoinAnnouncer;
use App\Models\Conference;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCreateReportTest extends TestCase
{
    public function test_successful_creating()
    {
        Mail::fake();

        Storage::fake('upload');

        $admin = User::whereHas(
            'roles', function ($q) {
                $q->where('name', 'Admin');
            }
        )->first();

        $conference = $this->getConferenceWithListener();

        $announcer = User::factory()->create_announcer();

        $report = [
            'user' => $announcer->id,
            'conference' => $conference->id,
            'topic' => 'Topic',
            'start_time' => '12:00',
            'end_time' => '12:30',
            'description' => 'Lorem ipsum',
            'presentation' => UploadedFile::fake()
                ->create(
                    'Presentation', 5000,
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                )
        ];

        $response = $this->actingAs($admin)
            ->json('POST', 'nova-api/reports?editing=true&editMode=create', $report);

        $response->assertStatus(201);

        Storage::disk('upload')->assertExists($response->original['resource']['presentation']);

        $this->assertTrue(
            $announcer->joinedConferences()
                ->where('conference_id', $conference->id)->count() === 1
        );

        $this->assertDatabaseHas('reports', ['id' => $response->original['id']]);

        Mail::assertQueued(JoinAnnouncer::class);
    }

    public function test_fail_creating_invalid_data()
    {
        Storage::fake('upload');

        $admin = User::whereHas(
            'roles', function ($q) {
                $q->where('name', 'Admin');
            }
        )->first();

        $announcer = User::factory()->create_announcer();

        $conference = Conference::factory()->create();

        $report = [
            'user' => $announcer->id,
            'conference' => $conference->id,
            'topic' => 'Topic',
            'start_time' => '16:00',
            'end_time' => '15:30',
            'description' => 'Lorem ipsum',
            'presentation' => UploadedFile::fake()
                ->create(
                    'Presentation', 5000,
                    '	application/msword'
                )
        ];

        $response = $this->actingAs($admin)
            ->json('POST', 'nova-api/reports?editing=true&editMode=create', $report);

        $response->assertStatus(422);

        $response->assertInvalid(['start_time', 'presentation']);
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
