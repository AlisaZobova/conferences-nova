<?php

namespace Tests\Unit;

use App\Mail\JoinAnnouncer;
use App\Models\Conference;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateReportTest extends TestCase
{
    public function test_successful_creating()
    {
        Storage::fake('upload');

        $announcer = User::factory()->create_announcer();

        $conference = Conference::factory()->create();

        $report = [
            'user_id' => $announcer->id,
            'conference_id' => $conference->id,
            'topic' => 'Topic',
            'start_time' => $conference->conf_date->format('Y-m-d') . ' 12:00:00',
            'end_time' => $conference->conf_date->format('Y-m-d') . ' 12:30:00',
            'description' => 'Lorem ipsum',
            'presentation' => UploadedFile::fake()
                ->create(
                    'Presentation', 5000,
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                )
        ];

        $response = $this->actingAs($announcer)->json('POST', 'api/reports', $report);

        $response->assertStatus(201);

        Storage::disk('upload')->assertExists($response->original->presentation);

        $this->assertModelExists($response->original);
    }

    public function test_fail_creating_by_listener()
    {

        $listener = User::factory()->create_listener();

        $conference = Conference::factory()->create();

        $report = [
            'user_id' => $listener->id,
            'conference_id' => $conference->id,
            'topic' => 'Topic',
            'start_time' => $conference->conf_date->format('Y-m-d') . ' 12:00:00',
            'end_time' => $conference->conf_date->format('Y-m-d') . ' 12:30:00',
            'description' => 'Lorem ipsum',
            'presentation' => ''
        ];

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

        $report = [
            'user_id' => $announcer->id,
            'conference_id' => $conference->id,
            'topic' => 'Topic',
            'start_time' => $conference->conf_date->format('Y-m-d') . ' 15:00:00',
            'end_time' => $conference->conf_date->format('Y-m-d') . ' 15:30:00',
            'description' => 'Lorem ipsum',
            'presentation' => ''
        ];

        $this->actingAs($announcer)->json('POST', 'api/reports', $report);


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
}
