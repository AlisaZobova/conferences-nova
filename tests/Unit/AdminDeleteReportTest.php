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

        $report = Report::factory()->create();

        $response = $this->actingAs($admin)
            ->json('DELETE', 'nova-api/reports?resources[]=' . $report->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted($report);

        Mail::assertQueued(AdminDeleteReport::class);
    }

    public function test_delete_non_existent_report()
    {
        Mail::fake();

        $admin = $this->getAdmin();

        $latest = Report::withTrashed()->orderBy('id', 'DESC')->first();

        $reportId = $latest ? $latest->id + 1 : 1;

        $response = $this->actingAs($admin)->json('DELETE', 'nova-api/reports?resources[]=' . $reportId);

        $response->assertStatus(200);

        Mail::assertNotQueued(AdminDeleteReport::class);
    }

    public function test_fail_no_auth()
    {
        $report = Report::factory()->create();

        $response = $this
            ->json('DELETE', 'nova-api/reports?resources[]=' . $report->id);

        $response->assertStatus(401);
    }

    public function test_fail_no_admin()
    {
        $user = User::factory()->create();

        $report = Report::factory()->create();

        $response = $this->actingAs($user)
            ->json('DELETE', 'nova-api/reports?resources[]=' . $report->id);

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

    public function getAdmin()
    {
        return User::whereHas(
            'roles', function ($q) {
            $q->where('name', 'Admin');
        }
        )->first();
    }
}
