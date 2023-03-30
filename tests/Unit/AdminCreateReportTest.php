<?php

namespace Tests\Unit;

use App\Mail\JoinAnnouncer;
use App\Models\Conference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCreateReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_creating()
    {
        $admin = $this->getAdmin();

        $conference = Conference::factory()->create();

        $announcer = User::factory()->create_announcer();

        $report = $this->getReportData($announcer, $conference);

        $response = $this->actingAs($admin)
            ->json('POST', 'nova-api/reports?editing=true&editMode=create', $report);

        $response->assertStatus(201);

        $this->assertDatabaseHas('reports', ['id' => $response->original['id']]);

        $this->assertTrue(
            $announcer->joinedConferences()
                ->where('conference_id', $conference->id)->count() === 1
        );
    }

    public function test_successful_presentation_upload()
    {
        Storage::fake('upload');

        $admin = $this->getAdmin();

        $conference = Conference::factory()->create();

        $announcer = User::factory()->create_announcer();

        $report = $this->getReportData($announcer, $conference);
        $report['presentation'] = UploadedFile::fake()
            ->create(
                'Presentation', 5000,
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            );

        $response = $this->actingAs($admin)
            ->json('POST', 'nova-api/reports?editing=true&editMode=create', $report);

        $response->assertStatus(201);

        Storage::disk('upload')->assertExists($response->original['resource']['presentation']);
    }

    public function test_mail_sending()
    {
        Mail::fake();

        $admin = $this->getAdmin();

        $conference = $this->getConferenceWithListener();

        $announcer = User::factory()->create_announcer();

        $report = $this->getReportData($announcer, $conference);

        $response = $this->actingAs($admin)
            ->json('POST', 'nova-api/reports?editing=true&editMode=create', $report);

        $response->assertStatus(201);

        Mail::assertQueued(JoinAnnouncer::class);
    }

    public function test_fail_creating_invalid_data()
    {
        $admin = $this->getAdmin();

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

    public function test_fail_no_auth()
    {
        $conference = Conference::factory()->create();

        $announcer = User::factory()->create_announcer();

        $report = $this->getReportData($announcer, $conference);

        $response = $this
            ->json('POST', 'nova-api/reports?editing=true&editMode=create', $report);

        $response->assertStatus(401);
    }

    public function test_fail_no_admin()
    {
        $user = User::factory()->create();

        $conference = Conference::factory()->create();

        $announcer = User::factory()->create_announcer();

        $report = $this->getReportData($announcer, $conference);

        $response = $this->actingAs($user)
            ->json('POST', 'nova-api/reports?editing=true&editMode=create', $report);

        $response->assertStatus(403);
    }

    public function getConferenceWithListener()
    {
        $listener = User::factory()->create_listener();

        $listener->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')->create();

        $conference = Conference::factory()->create();

        $this->actingAs($listener)->json('POST', 'api/conferences/' . $conference->id . '/join');

        return $conference;
    }

    public function getReportData($announcer, $conference)
    {
        return [
            'user' => $announcer->id,
            'conference' => $conference->id,
            'topic' => 'Topic',
            'start_time' => '12:00',
            'end_time' => '12:30',
            'description' => 'Lorem ipsum',
            'presentation' => null
        ];
    }

    public function getAdmin()
    {
        return User::whereHas(
            'roles', function ($q) {
                $q->where('name', 'Admin');
            }
        )->first();
    }
}
