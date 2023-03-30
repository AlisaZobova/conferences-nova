<?php

namespace Tests\Unit;

use App\Models\Conference;
use App\Models\User;
use Database\Seeders\ConferenceTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConferenceSearchTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->seed(ConferenceTestSeeder::class);

        $this->conferences = $this->createConferences();
    }

    public function test_search_with_result()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/conferences/search?title=li');
        $response->assertStatus(200);
        $this->assertTrue(count($response->original) === 2);
        $this->assertTrue(
            $response->original->contains($this->conferences['li']) &&
            $response->original->contains($this->conferences['like'])
        );
        $this->assertFalse($response->original->contains($this->conferences['kernel']));
        $this->assertFalse($response->original->contains($this->conferences['test']));
    }

    public function test_search_without_result()
    {
        $response = $this->actingAs($this->getUser())->json('GET', 'api/conferences/search?title=aaa');
        $response->assertStatus(200);
        $this->assertTrue(count($response->original) === 0);
    }

    public function test_no_old_conferences_in_result()
    {
        $oldConference = Conference::factory()->create(['title' => 'lips', 'conf_date' => '2020-12-12']);

        $response = $this->actingAs($this->getUser())->json('GET', 'api/conferences/search?title=li');
        $response->assertStatus(200);

        $this->assertTrue(count($response->original) === 2);
        $this->assertTrue(
            $response->original->contains($this->conferences['li']) &&
            $response->original->contains($this->conferences['like'])
        );
        $this->assertFalse($response->original->contains($this->conferences['kernel']));
        $this->assertFalse($response->original->contains($this->conferences['test']));
        $this->assertFalse($response->original->contains($oldConference));
    }

    public function test_fail_no_auth()
    {
        $response = $this->json('GET', 'api/conferences/search?title=li');
        $response->assertStatus(401);
    }

    public function createConferences()
    {
        return [
            'like' => Conference::factory()->create(['title' => 'Like']),
            'li' => Conference::factory()->create(['title' => 'Li']),
            'kernel' => Conference::factory()->create(['title' => 'Kernel']),
            'test' => Conference::factory()->create(['title' => 'Test'])
        ];
    }

    public function getUser()
    {
        return User::factory()->create();
    }
}
