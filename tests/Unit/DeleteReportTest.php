<?php

namespace Tests\Unit;

use App\Mail\UpdateReportTime;
use App\Models\Conference;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteReportTest extends TestCase
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
        $this->report = $response->original;
    }


    public function test_successful_delete()
    {
        Storage::fake('upload');

        $response = $this->actingAs($this->announcer)->json('DELETE', 'api/reports/' . $this->report->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted($this->report);
    }

    public function test_fail_delete_by_listener()
    {

        $listener = User::factory()->create_listener();

        $response = $this->actingAs($listener)->json('DELETE', 'api/reports/' . $this->report->id);

        $response->assertStatus(403);
    }

    public function test_fail_delete_by_not_report_creator()
    {

        $anotherAnnouncer = User::factory()->create_announcer();

        $response = $this->actingAs($anotherAnnouncer)->json('DELETE', 'api/reports/' . $this->report->id);

        $response->assertStatus(403);
    }


    public function test_fail_with_non_existent_report()
    {
        Storage::fake('upload');

        $reportId = Report::withTrashed()->latest()->first()->id + 1;

        $response = $this->actingAs($this->announcer)->json('DELETE', 'api/reports/' . $reportId);

        $response->assertStatus(404);
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

}
