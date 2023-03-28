<?php

namespace Tests\Unit;

use App\Mail\AdminUpdateReport;
use App\Models\Conference;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUpdateReportTest extends TestCase
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

        $response = $this->actingAs($this->admin)->json('POST', 'nova-api/reports?editing=true&editMode=create', $report);

        $this->reportId = $response->original['resource']['id'];

        $this->newFields = $this->getNewFields();
    }

    public function test_successful_update()
    {
        Storage::fake('upload');

        $response = $this->actingAs($this->admin)
            ->json('PUT', 'nova-api/reports/' . $this->reportId .'?editing=true&editMode=update', $this->newFields);

        $response->assertStatus(200);

        $this->assertTrue(!array_diff(Report::find($this->reportId)->toArray(), $response->original['resource']));


        Storage::disk('upload')->assertExists($response->original['resource']['presentation']);
    }

    public function test_successful_report_update_join_and_cancel()
    {
        Storage::fake('upload');

        $newConference = Conference::factory()->create();
        $newAnnouncer = User::factory()->create_announcer();

        $newFields = $this->getReportData($newAnnouncer, $newConference);

        $response = $this->actingAs($this->admin)
            ->json('PUT', 'nova-api/reports/' . $this->reportId .'?editing=true&editMode=update', $newFields);

        $response->assertStatus(200);
        Storage::disk('upload')->assertExists($response->original['resource']['presentation']);

        $this->assertTrue(
            $this->announcer->joinedConferences()
                ->where('conference_id', $this->conference->id)->count() === 0 &&
            $newAnnouncer->joinedConferences()->where('conference_id', $newConference->id)->count() === 1
        );

    }

    public function test_update_emails_sending()
    {
        Mail::fake();

        Storage::fake('upload');

        $response = $this->actingAs($this->admin)
            ->json('PUT', 'nova-api/reports/' . $this->reportId .'?editing=true&editMode=update', $this->newFields);

        $response->assertStatus(200);

        Mail::assertQueued(AdminUpdateReport::class);
    }


    public function test_fail_with_invalid_data()
    {
        Storage::fake('upload');

        $newFields = [
            'user' => $this->announcer->id,
            'conference' => $this->conference->id,
            'topic' => 'Topic',
            'start_time' => '07:05',
            'end_time' => '22:20',
            'presentation' => 444
        ];

        $response = $this->actingAs($this->admin)
            ->json('PUT', 'nova-api/reports/' . $this->reportId .'?editing=true&editMode=update', $newFields);

        $response->assertStatus(422);

        $response->assertInvalid(['start_time', 'end_time', 'presentation']);
    }

    public function test_fail_with_non_existent_report()
    {
        Storage::fake('upload');

        $reportId = Report::withTrashed()->latest()->first()->id + 1;

        $response = $this->actingAs($this->admin)
            ->json('PUT', 'nova-api/reports/' . $reportId .'?editing=true&editMode=update', $this->newFields);

        $response->assertStatus(404);
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
            'presentation' => UploadedFile::fake()
                ->create(
                    fake()->word(), 5000,
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                )
        ];
    }

    public function getNewFields()
    {
        return [
            'user' => $this->announcer->id,
            'conference' => $this->conference->id,
            'topic' => 'New',
            'start_time' => '11:50',
            'end_time' => '12:20',
            'presentation' => UploadedFile::fake()
                ->create(
                    fake()->word(), 5000,
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                )
        ];
    }
}
