<?php

namespace Tests\Unit;

use App\Mail\UpdateReportTime;
use App\Models\Conference;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    /**
     * Set up the test
     */
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('upload');

        $this->admin = User::whereHas(
            'roles', function ($q) {
                $q->where('name', 'Admin');
            }
        )->first();

        $this->conference = Conference::factory()->create();
        $this->announcer = User::factory()->create_announcer();
        $report = $this->getReportData($this->announcer, $this->conference);

        $response = $this->actingAs($this->announcer)->json('POST', 'api/reports', $report);
        $this->reportId = $response->original->id;

        $this->confWithList = $this->getConferenceWithListener();
        $reportWithList = $this->getReportData($this->announcer, $this->confWithList);
        $responseList = $this->actingAs($this->announcer)->json('POST', 'api/reports', $reportWithList);
        $this->reportIdWithList = $responseList->original->id;

        $this->newFields = $this->getNewFields();
    }


    public function test_successful_update()
    {
        Storage::fake('upload');

        $response = $this->actingAs($this->announcer)->json('PATCH', 'api/reports/' . $this->reportId, $this->newFields);

        $response->assertStatus(200);
        $this->assertModelExists($response->original);
        Storage::disk('upload')->assertExists($response->original->presentation);

    }

    public function test_time_update_emails_sending()
    {
        Mail::fake();

        $newFields = [
            'start_time' => $this->confWithList->conf_date->format('Y-m-d') . ' 12:50:00',
            'end_time' => $this->confWithList->conf_date->format('Y-m-d') . ' 13:30:00',
            'presentation' => null
        ];

        $response = $this->actingAs($this->announcer)->json('PATCH', 'api/reports/' . $this->reportIdWithList, $newFields);

        $response->assertStatus(200);

        Mail::assertQueued(UpdateReportTime::class);
    }

    public function test_fail_update_by_listener()
    {

        $listener = User::factory()->create_listener();

        $response = $this->actingAs($listener)->json('PATCH', 'api/reports/' . $this->reportId, $this->newFields);

        $response->assertStatus(403);
    }

    public function test_fail_update_by_not_report_creator()
    {

        $anotherAnnouncer = User::factory()->create_announcer();

        $response = $this->actingAs($anotherAnnouncer)->json('PATCH', 'api/reports/' . $this->reportId, $this->newFields);

        $response->assertStatus(403);
    }

    public function test_fail_with_invalid_data()
    {

        $newFields = [
            'start_time' => $this->conference->conf_date->format('Y-m-d') . ' 07:05:00',
            'end_time' => $this->conference->conf_date->format('Y-m-d') . ' 22:20:00',
            'presentation' => 444
        ];

        $response = $this->actingAs($this->announcer)->json('PATCH', 'api/reports/' . $this->reportId, $newFields);

        $response->assertStatus(422);

        $response->assertInvalid(['start_time', 'end_time', 'presentation']);
    }

    public function test_fail_with_non_existent_report()
    {
        Storage::fake('upload');

        $reportId = Report::withTrashed()->latest()->first()->id + 1;

        $response = $this->actingAs($this->announcer)->json('PATCH', 'api/reports/' . $reportId, $this->newFields);

        $response->assertStatus(404);
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
            'user_id' => $announcer->id,
            'conference_id' => $conference->id,
            'topic' => 'Topic',
            'start_time' => $conference->conf_date->format('Y-m-d') . ' 12:00:00',
            'end_time' => $conference->conf_date->format('Y-m-d') . ' 12:30:00',
            'description' => 'Lorem ipsum',
            'presentation' => null
        ];
    }

    public function getNewFields()
    {
        return [
            'topic' => 'New',
            'description' => 'Some text',
            'end_time' => $this->conference->conf_date->format('Y-m-d') . ' 12:20:00',
            'presentation' => UploadedFile::fake()
                ->create(
                    'Presentation', 5000,
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                )
        ];
    }
}
