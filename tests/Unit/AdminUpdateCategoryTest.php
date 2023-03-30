<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUpdateCategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_updating_with_parent()
    {
        $admin = $this->getAdmin();

        $category = Category::create(['name' => fake()->word(), 'ancestor_id' => null]);

        $parentCategory = Category::create(['name' => fake()->word(), 'ancestor_id' => null]);

        $newFields = [
            'name' => fake()->word(),
            'parent' => $parentCategory->id
        ];

        $response = $this->actingAs($admin)
            ->json(
                'PUT',
                'nova-api/categories/' . $category->id . '?editing=true&editMode=update', $newFields
            );

        $response->assertStatus(200);

        $updatedCategory = Category::find($category->id);

        $this->assertTrue(
            $updatedCategory->name === $newFields['name'] &&
            $updatedCategory->ancestor_id === $newFields['parent']
        );
    }

    public function test_successful_updating_without_parent()
    {
        $admin = $this->getAdmin();

        $category = Category::create(['name' => fake()->word(), 'ancestor_id' => null]);

        $newFields = [
            'name' => fake()->word(),
            'parent' => null
        ];

        $response = $this->actingAs($admin)
            ->json(
                'PUT',
                'nova-api/categories/' . $category->id . '?editing=true&editMode=update', $newFields
            );

        $response->assertStatus(200);

        $updatedCategory = Category::find($category->id);

        $this->assertTrue(
            $updatedCategory->name === $newFields['name'] &&
            $updatedCategory->ancestor_id === $newFields['parent']
        );
    }

    public function test_fail_updating_without_name()
    {
        $admin = $this->getAdmin();

        $category = Category::create(['name' => fake()->word(), 'ancestor_id' => null]);

        $newFields = [
            'name' => null,
            'parent' => null
        ];

        $response = $this->actingAs($admin)
            ->json(
                'PUT',
                'nova-api/categories/' . $category->id . '?editing=true&editMode=update', $newFields
            );

        $response->assertStatus(422);

        $response->assertInvalid('name');
    }

    public function test_fail_no_auth()
    {
        $category = Category::create(['name' => fake()->word(), 'ancestor_id' => null]);

        $parentCategory = Category::create(['name' => fake()->word(), 'ancestor_id' => null]);

        $newFields = [
            'name' => fake()->word(),
            'parent' => $parentCategory->id
        ];

        $response = $this
            ->json(
                'PUT',
                'nova-api/categories/' . $category->id . '?editing=true&editMode=update', $newFields
            );

        $response->assertStatus(401);
    }

    public function test_fail_no_admin()
    {
        $user = User::factory()->create();

        $category = Category::create(['name' => fake()->word(), 'ancestor_id' => null]);

        $parentCategory = Category::create(['name' => fake()->word(), 'ancestor_id' => null]);

        $newFields = [
            'name' => fake()->word(),
            'parent' => $parentCategory->id
        ];

        $response = $this->actingAs($user)
            ->json(
                'PUT',
                'nova-api/categories/' . $category->id . '?editing=true&editMode=update', $newFields
            );

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
