<?php

namespace Tests\Unit;

use App\Models\Conference;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\ReportTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSearchTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->seed(ReportTestSeeder::class);

        $this->reports = $this->createReports();
    }

    public function test_search_with_result()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/reports/search?topic=li');
        $response->assertStatus(200);
        $this->assertTrue(count($response->original) === 2);
        $this->assertTrue(
            $response->original->contains($this->reports['li']) &&
            $response->original->contains($this->reports['like'])
        );
        $this->assertFalse($response->original->contains($this->reports['kernel']));
        $this->assertFalse($response->original->contains($this->reports['test']));
    }

    public function test_search_without_result()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/reports/search?topic=aaa');
        $response->assertStatus(200);
        $this->assertTrue(count($response->original) === 0);
    }

    public function test_no_old_reports_in_result()
    {
        $conference = Conference::factory()->create(['title' => 'lips', 'conf_date' => '2020-12-12']);

        $oldReport = Report::factory()->create(['conference_id' => $conference->id, 'topic' => 'Lips']);

        $response = $this->actingAs($this->getUser())->json('GET', 'api/reports/search?topic=li');
        $response->assertStatus(200);
        $this->assertTrue(count($response->original) === 2);
        $this->assertTrue(
            $response->original->contains($this->reports['li']) &&
            $response->original->contains($this->reports['like'])
        );
        $this->assertFalse($response->original->contains($this->reports['kernel']));
        $this->assertFalse($response->original->contains($this->reports['test']));
        $this->assertFalse($response->original->contains($oldReport));
    }

    public function test_fail_no_auth()
    {
        $response = $this->json('GET', 'api/reports/search?topic=li');
        $response->assertStatus(401);
    }

    public function createReports()
    {
        return [
            'like' => Report::factory()->create(['topic' => 'Like']),
            'li' => Report::factory()->create(['topic' => 'Li']),
            'kernel' => Report::factory()->create(['topic' => 'Kernel']),
            'test' => Report::factory()->create(['topic' => 'Test'])
        ];
    }

    public function getUser()
    {
        return User::factory()->create();
    }
}
