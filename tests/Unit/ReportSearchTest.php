<?php

namespace Tests\Unit;

use App\Models\Report;
use App\Models\User;
use Database\Seeders\ConferenceSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CreateAdminSeeder;
use Database\Seeders\CreateRoleSeeder;
use Database\Seeders\ModelPermissionsSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_with_result()
    {
        $this->seedWithoutReports();
        $reports = $this->createReports();
        $response = $this->actingAs($this->getUser())->json('GET', 'api/reports/search?topic=li');
        $response->assertStatus(200);
        $this->assertTrue(count($response->original) === 2);
        $this->assertTrue(
            $response->original->contains($reports['li']) &&
            $response->original->contains($reports['like'])
        );
        $this->assertFalse(
            $response->original->contains($reports['kernel']) &&
            $response->original->contains($reports['test'])
        );
    }

    public function test_search_without_result()
    {
        $this->seedWithoutReports();
        $this->createReports();
        $response = $this->actingAs($this->getUser())->json('GET', 'api/reports/search?topic=aaa');
        $response->assertStatus(200);
        $this->assertTrue(count($response->original) === 0);
    }

    public function test_fail_no_auth()
    {
        $this->seedWithoutReports();
        $this->createReports();
        $response = $this->json('GET', 'api/reports/search?topic=aaa');
        $response->assertStatus(401);
    }

    public function createReports()
    {
        $reports = [
            'like' => Report::factory()->create(['topic' => 'Like']),
            'li' => Report::factory()->create(['topic' => 'Li']),
            'kernel' => Report::factory()->create(['topic' => 'Kernel']),
            'test' => Report::factory()->create(['topic' => 'Test'])
        ];

        return $reports;
    }

    public function getUser()
    {
        return User::factory()->create();
    }

    public function seedWithoutReports()
    {
        $this->seed(
            [
            CountrySeeder::class,
            CreateRoleSeeder::class,
            CreateAdminSeeder::class,
            UserSeeder::class,
            ConferenceSeeder::class,
            PermissionSeeder::class,
            ModelPermissionsSeeder::class
            ]
        );
    }
}
