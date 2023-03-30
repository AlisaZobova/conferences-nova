<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCreateCategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_creating_root()
    {
        $admin = $this->getAdmin();

        $category = [
            'name' => 'Root',
            'parent' => null
        ];

        $response = $this->actingAs($admin)
            ->json('POST', 'nova-api/categories?editing=true&editMode=create', $category);

        $response->assertStatus(201);

        $this->assertDatabaseHas('categories', ['id' => $response->original['id']]);
    }

    public function test_successful_creating_child()
    {
        $admin = $this->getAdmin();

        $category = Category::create(['name' => fake()->word(), 'ancestor_id' => null]);

        $category = [
            'name' => 'Child',
            'parent' => $category->id
        ];

        $response = $this->actingAs($admin)
            ->json('POST', 'nova-api/categories?editing=true&editMode=create', $category);

        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', ['id' => $response->original['id']]);
    }

    public function test_fail_creating_without_name()
    {
        $admin = $this->getAdmin();

        $category = [
            'name' => null,
            'parent' => null
        ];

        $response = $this->actingAs($admin)
            ->json('POST', 'nova-api/categories?editing=true&editMode=create', $category);

        $response->assertStatus(422);

        $response->assertInvalid('name');
    }

    public function test_fail_no_auth()
    {
        $category = [
            'name' => 'Root',
            'parent' => null
        ];

        $response = $this->json('POST', 'nova-api/categories?editing=true&editMode=create', $category);

        $response->assertStatus(401);
    }

    public function test_fail_no_admin()
    {
        $user = User::factory()->create();

        $category = [
            'name' => 'Root',
            'parent' => null
        ];

        $response = $this->actingAs($user)
            ->json('POST', 'nova-api/categories?editing=true&editMode=create', $category);

        $response->assertStatus(403);
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
