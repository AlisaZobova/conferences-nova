<?php

namespace Tests\Unit;

use App\Models\Conference;
use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CreateAdminSeeder;
use Database\Seeders\CreateRoleSeeder;
use Database\Seeders\ModelPermissionsSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConferenceSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_with_result()
    {
        $this->seedWithoutConferences();
        $conferences = $this->createConferences();
        $response = $this->actingAs($this->getUser())->json('GET', 'api/conferences/search?title=li');
        $response->assertStatus(200);
        $this->assertTrue(count($response->original) === 2);
        $this->assertTrue(
            $response->original->contains($conferences['li']) &&
            $response->original->contains($conferences['like'])
        );
        $this->assertFalse($response->original->contains($conferences['kernel']));
        $this->assertFalse($response->original->contains($conferences['test']));
    }

    public function test_search_without_result()
    {
        $this->seedWithoutConferences();
        $this->createConferences();
        $response = $this->actingAs($this->getUser())->json('GET', 'api/conferences/search?title=aaa');
        $response->assertStatus(200);
        $this->assertTrue(count($response->original) === 0);
    }

    public function test_fail_no_auth()
    {
        $this->seedWithoutConferences();
        $this->createConferences();
        $response = $this->json('GET', 'api/conferences/search?title=aaa');
        $response->assertStatus(401);
    }

    public function createConferences()
    {
        $conferences = [
            'like' => Conference::factory()->create(['title' => 'Like']),
            'li' => Conference::factory()->create(['title' => 'Li']),
            'kernel' => Conference::factory()->create(['title' => 'Kernel']),
            'test' => Conference::factory()->create(['title' => 'Test'])
        ];

        return $conferences;
    }

    public function getUser()
    {
        return User::factory()->create();
    }

    public function seedWithoutConferences()
    {
        $this->seed(
            [
                CountrySeeder::class,
                CreateRoleSeeder::class,
                CreateAdminSeeder::class,
                UserSeeder::class,
                PermissionSeeder::class,
                ModelPermissionsSeeder::class
            ]
        );
    }
}
