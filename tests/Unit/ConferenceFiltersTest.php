<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Conference;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\ConferenceTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConferenceFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->seed(ConferenceTestSeeder::class);

        $this->categories = $this->createCategories();
        $this->conferences = $this->createConferences();
        $this->createReports();
    }

    public function test_from_filter()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/conferences?from=2023-12-12');
        $response->assertStatus(200);

        $this->assertTrue(count($response['data']) === 4);

        $this->assertFalse(
            in_array($this->conferences['second_category']->id, array_column($response['data'], 'id'))
        );
        $this->assertFalse(
            in_array($this->conferences['category_with_report']->id, array_column($response['data'], 'id'))
        );
    }

    public function test_no_old_conferences_in_result()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/conferences?from=2023-12-12');
        $response->assertStatus(200);

        $oldConference = Conference::factory()->create(['conf_date' => '2020-12-12']);

        $this->assertTrue(count($response['data']) === 4);

        $this->assertFalse(in_array($this->conferences['second_category']->id, array_column($response['data'], 'id')));
        $this->assertFalse(in_array($this->conferences['category_with_report']->id, array_column($response['data'], 'id')));
        $this->assertFalse(in_array($oldConference->id, array_column($response['data'], 'id')));
    }

    public function test_to_filter()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/conferences?to=2030-04-12');
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 5);

        $this->assertFalse(in_array($this->conferences['2030_year']->id, array_column($response['data'], 'id')));
    }

    public function test_reports_filter()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/conferences?reports=1-1');
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 2);

        $this->assertTrue(
            in_array($this->conferences['1_report']->id, array_column($response['data'], 'id'))
        );
        $this->assertTrue(
            in_array($this->conferences['category_with_report']->id, array_column($response['data'], 'id'))
        );
    }

    public function test_one_category_filter()
    {
        $response = $this->actingAs($this->getUser())->json(
            'GET',
            'api/conferences?category=' . $this->categories['first']
        );
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 2);

        $this->assertTrue(
            in_array($this->conferences['first_category']->id, array_column($response['data'], 'id'))
        );
        $this->assertTrue(
            in_array($this->conferences['category_with_report']->id, array_column($response['data'], 'id'))
        );
    }

    public function test_several_categories_filter()
    {
        $response = $this->actingAs($this->getUser())->json(
            'GET',
            'api/conferences?category=' . $this->categories['first'] . ',' . $this->categories['second']
        );
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 3);

        $this->assertTrue(
            in_array($this->conferences['first_category']->id, array_column($response['data'], 'id')) &&
            in_array($this->conferences['second_category']->id, array_column($response['data'], 'id')) &&
            in_array($this->conferences['category_with_report']->id, array_column($response['data'], 'id'))
        );
    }


    public function test_all_filters()
    {
        $response = $this->actingAs($this->getUser())->json(
            'GET',
            'api/conferences?from=2023-05-12&to=2023-12-12&reports=1-1&category=' .
            $this->categories['first'] . ',' . $this->categories['second']
        );
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 1);

        $this->assertTrue(
            in_array($this->conferences['category_with_report']->id, array_column($response['data'], 'id'))
        );
    }

    public function test_filter_without_result()
    {
        $this->createConferences();
        $response = $this->actingAs($this->getUser())->json(
            'GET', 'api/conferences?reports=2-5&category=' .
            $this->categories['first'] . ',' . $this->categories['second']
        );
        $response->assertStatus(200);
        $this->assertTrue(count($response['data']) === 0);
    }

    public function test_filter_pagination()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/conferences?from=08:00:00');
        $response->assertStatus(200);
        $this->assertTrue($response['per_page'] === 15);
    }

    public function createCategories()
    {
        return [
            'first' => Category::create(['name' => 'First'])->id,
            'second' => Category::create(['name' => 'Second'])->id,
        ];
    }

    public function createConferences()
    {
        return [
            'first_category' => Conference::factory()
                ->create(
                    [
                        'conf_date' => '2023-12-12',
                        'category_id' => $this->categories['first']
                    ]
                ),
            'second_category' => Conference::factory()
                ->create(
                    [
                        'conf_date' => '2023-05-12',
                        'category_id' => $this->categories['second']
                    ]
                ),
            'category_with_report' => Conference::factory()
                ->create(
                    [
                        'conf_date' => '2023-05-12',
                        'category_id' => $this->categories['first']
                    ]
                ),
            '1_report' => Conference::factory()
                ->create(
                    [
                        'conf_date' => '2025-05-12',
                    ]
                ),
            '2_reports' => Conference::factory()
                ->create(
                    [
                        'conf_date' => '2026-05-12',
                    ]
                ),
            '2030_year' => Conference::factory()
                ->create(
                    [
                        'conf_date' => '2030-05-12',
                    ]
                ),
        ];
    }

    public function createReports()
    {
        Report::factory()->create(['conference_id' => $this->conferences['1_report']->id]);
        Report::factory()->create(['conference_id' => $this->conferences['2_reports']->id]);
        Report::factory()->create(['conference_id' => $this->conferences['2_reports']->id]);
        Report::factory()->create(['conference_id' => $this->conferences['category_with_report']->id]);
    }

    public function getUser()
    {
        return User::factory()->create();
    }
}
