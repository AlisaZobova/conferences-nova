<?php

namespace Tests\Unit;

use App\Mail\JoinAnnouncer;
use App\Models\Conference;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_creating_without_zoom()
    {
        Storage::fake('upload');

        $announcer = User::factory()->create_announcer();

        $conference = Conference::factory()->create();

        $report = $this->getReportData($announcer, $conference);

        $response = $this->actingAs($announcer)->json('POST', 'api/reports', $report);

        $response->assertStatus(201);

        Storage::disk('upload')->assertExists($response->original->presentation);

        $this->assertModelExists($response->original);

        $this->assertTrue(Report::find($response->original->id)->meeting === null);
    }

    public function test_successful_creating_with_zoom()
    {
        Storage::fake('upload');

        $announcer = User::factory()->create_announcer();

        $conference = Conference::factory()->create();

        $report = $this->getReportData($announcer, $conference);
        $report['online'] = 'true';

        $response = $this->actingAs($announcer)->json('POST', 'api/reports', $report);

        $response->assertStatus(201);

        Storage::disk('upload')->assertExists($response->original->presentation);

        $this->assertModelExists($response->original);

        $this->assertTrue(Report::find($response->original->id)->meeting !== null);
    }

    public function test_fail_creating_by_listener()
    {
        $listener = User::factory()->create_listener();

        $conference = Conference::factory()->create();

        $report = $this->getReportData($listener, $conference);

        $response = $this->actingAs($listener)->json('POST', 'api/reports', $report);

        $response->assertStatus(403);
    }

    public function test_fail_creating_invalid_data()
    {
        Storage::fake('upload');

        $announcer = User::factory()->create_announcer();

        $conference = Conference::factory()->create();

        $report = [
            'user_id' => $announcer->id,
            'conference_id' => $conference->id,
            'topic' => 'Topic',
            'start_time' => $conference->conf_date->format('Y-m-d') . ' 16:00:00',
            'end_time' => $conference->conf_date->format('Y-m-d') . ' 15:30:00',
            'description' => 'Lorem ipsum',
            'presentation' => UploadedFile::fake()
                ->create(
                    'Presentation', 5000,
                    '	application/msword'
                )
        ];

        $response = $this->actingAs($announcer)->json('POST', 'api/reports', $report);

        $response->assertStatus(422);

        $response->assertInvalid(['end_time', 'presentation']);
    }

    public function test_fail_creating_time_overlap()
    {
        $announcer = User::factory()->create_announcer();

        $conference = Conference::factory()->create();

        Report::factory()->create(
            [
                'conference_id' => $conference->id,
                'start_time' => $conference->conf_date->format('Y-m-d') . ' 15:00:00',
                'end_time' => $conference->conf_date->format('Y-m-d') . ' 15:30:00'
            ]
        );

        $overlapReport = [
            'user_id' => $announcer->id,
            'conference_id' => $conference->id,
            'topic' => 'Topic',
            'start_time' => $conference->conf_date->format('Y-m-d') . ' 15:10:00',
            'end_time' => $conference->conf_date->format('Y-m-d') . ' 15:40:00',
            'description' => 'Lorem ipsum',
            'presentation' => ''
        ];

        $response = $this->actingAs($announcer)->json('POST', 'api/reports', $overlapReport);

        $response->assertStatus(422);

        $response->assertInvalid('start_time');
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
            'presentation' => UploadedFile::fake()
                ->create(
                    fake()->word(), 5000,
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                ),
            'online' => 'false'
        ];
    }
}
