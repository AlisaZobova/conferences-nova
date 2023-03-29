<?php

namespace Tests\Unit;

use App\Models\Category;
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

class ReportFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seedWithoutReports();
        $this->categories = $this->createCategories();
        $this->reports = $this->createReports();
    }

    public function test_from_filter()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/reports?from=09:00:00');
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 7);

        $this->assertFalse(in_array($this->reports['start_8']->id, array_column($response['data'], 'id')));
    }

    public function test_to_filter()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/reports?to=15:00:00');
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 6);

        $this->assertFalse(in_array($this->reports['first_category']->id, array_column($response['data'], 'id')));
        $this->assertFalse(in_array($this->reports['second_category']->id, array_column($response['data'], 'id')));
    }

    public function test_duration_filter()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/reports?duration=15-30');
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 3);

        $this->assertTrue(
            in_array($this->reports['first_category']->id, array_column($response['data'], 'id')) &&
            in_array($this->reports['15_min']->id, array_column($response['data'], 'id')) &&
            in_array($this->reports['30_min']->id, array_column($response['data'], 'id'))
        );
    }

    public function test_one_category_filter()
    {
        $response = $this->actingAs($this->getUser())->json(
            'GET',
            'api/reports?category=' . $this->categories['first']
        );
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 1);

        $this->assertTrue(
            in_array($this->reports['first_category']->id, array_column($response['data'], 'id'))
        );
    }

    public function test_several_categories_filter()
    {
        $response = $this->actingAs($this->getUser())->json(
            'GET',
            'api/reports?from=09:00:00&to=16:00:00&duration=10-40&category=' .
            $this->categories['first'] . ',' . $this->categories['second']
        );
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 1);

        $this->assertTrue(
            in_array($this->reports['first_category']->id, array_column($response['data'], 'id'))
        );
    }

    public function test_all_filters()
    {
        $response = $this->actingAs($this->getUser())->json(
            'GET',
            'api/reports?category=' . $this->categories['first'] . ',' . $this->categories['second']
        );
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 2);

        $this->assertTrue(
            in_array($this->reports['first_category']->id, array_column($response['data'], 'id')) &&
            in_array($this->reports['second_category']->id, array_column($response['data'], 'id'))
        );
    }

    public function test_filter_without_result()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/reports?from=19:00:00');
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 0);
    }

    public function test_filter_pagination()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/reports?from=08:00:00');
        $response->assertStatus(200);
        $this->assertTrue($response['per_page'] === 12);
    }

    public function test_fail_no_auth()
    {
        $response = $this->json('GET', 'api/reports?from=08:00:00');
        $response->assertStatus(401);
    }

    public function createCategories()
    {
        return [
            'first' => Category::create(['name' => 'First'])->id,
            'second' => Category::create(['name' => 'Second'])->id,
        ];
    }

    public function createReports()
    {
        return [
            'first_category' => Report::factory()
                ->create(
                    [
                    'start_time' => date('Y-m-d', (strtotime(now()))) . ' 15:10:00',
                    'end_time' => date('Y-m-d', (strtotime(now()))) . ' 15:40:00',
                    'category_id' => $this->categories['first']
                    ]
                ),
            'second_category' => Report::factory()
                ->create(
                    [
                    'start_time' => date('Y-m-d', (strtotime(now()))) . ' 16:00:00',
                    'end_time' => date('Y-m-d', (strtotime(now()))) . ' 16:40:00',
                    'category_id' => $this->categories['second']
                    ]
                ),
            '30_min' => Report::factory()
                ->create(
                    [
                    'start_time' => date('Y-m-d', (strtotime(now()))) . ' 13:00:00',
                    'end_time' => date('Y-m-d', (strtotime(now()))) . ' 13:30:00'
                    ]
                ),
            '15_min' => Report::factory()
                ->create(
                    [
                    'start_time' => date('Y-m-d', (strtotime(now()))) . ' 14:00:00',
                    'end_time' => date('Y-m-d', (strtotime(now()))) . ' 14:15:00'
                    ]
                ),
            '40_min' => Report::factory()
                ->create(
                    [
                    'start_time' => date('Y-m-d', (strtotime(now()))) . ' 14:20:00',
                    'end_time' => date('Y-m-d', (strtotime(now()))) . ' 15:00:00'
                    ]
                ),
            'start_8' => Report::factory()
                ->create(
                    [
                    'start_time' => date('Y-m-d', (strtotime(now()))) . ' 08:00:00',
                    'end_time' => date('Y-m-d', (strtotime(now()))) . ' 08:50:00'
                    ]
                ),
            'end_12' => Report::factory()
                ->create(
                    [
                    'start_time' => date('Y-m-d', (strtotime(now()))) . ' 11:00:00',
                    'end_time' => date('Y-m-d', (strtotime(now()))) . ' 12:00:00'
                    ]
                ),
            'start_9_end_10' => Report::factory()
                ->create(
                    [
                    'start_time' => date('Y-m-d', (strtotime(now()))) . ' 09:00:00',
                    'end_time' => date('Y-m-d', (strtotime(now()))) . ' 10:00:00'
                    ]
                ),
        ];
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
