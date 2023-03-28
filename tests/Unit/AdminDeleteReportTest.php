<?php

namespace Tests\Unit;

use App\Mail\AdminDeleteReport;
use App\Models\Conference;
use App\Models\User;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminDeleteReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_report_delete()
    {
        Mail::fake();

        $admin = $this->getAdmin();

        $reportId = $this->getCreatedReportId();

        $response = $this->actingAs($admin)
            ->json('DELETE', 'nova-api/reports?resources[]=' . $reportId);

        $response->assertStatus(200);

        $report = Report::withTrashed()->find($reportId);

        $this->assertSoftDeleted($report);

        Mail::assertQueued(AdminDeleteReport::class);
    }

    public function test_delete_non_existent_report()
    {
        Mail::fake();

        $admin = $this->getAdmin();

        $latest = Report::withTrashed()->latest()->first();

        $reportId = $latest ? $latest->id + 1 : 1;

        $response = $this->actingAs($admin)->json('DELETE', 'nova-api/reports?resources[]=' . $reportId);

        $response->assertStatus(200);

        Mail::assertNotQueued(AdminDeleteReport::class);
    }

    public function getConferenceWithListener()
    {
        $listener = User::factory()->create_listener();

        $listener->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')->create();

        $conference = Conference::factory()->create();

        $this->actingAs($listener)->json('POST', 'api/conferences/' . $conference->id . '/join');

        return $conference;
    }

    public function getAdmin()
    {
        return User::whereHas(
            'roles', function ($q) {
                $q->where('name', 'Admin');
            }
        )->first();
    }

    public function getCreatedReportId()
    {
            $admin = $this->getAdmin();

            $conference = $this->getConferenceWithListener();

            $announcer = User::factory()->create_announcer();

            $report = [
                'user' => $announcer->id,
                'conference' => $conference->id,
                'topic' => 'Topic',
                'start_time' => '12:00',
                'end_time' => '12:30',
                'description' => '',
                'presentation' => null
            ];

            $response = $this->actingAs($admin)
                ->json('POST', 'nova-api/reports?editing=true&editMode=create', $report);

            return $response->original['resource']['id'];
    }

}
