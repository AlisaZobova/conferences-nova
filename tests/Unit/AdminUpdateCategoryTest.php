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

    public function test_successful_updating()
    {
        $admin = $this->getAdmin();

        $categoryId = $this->getCreatedCategoryId();

        $category = [
            'name' => fake()->word(),
            'parent' => $this->getCreatedCategoryId()
        ];

        $response = $this->actingAs($admin)
            ->json('PUT', 'nova-api/categories/' . $categoryId . '?editing=true&editMode=update', $category);

        $response->assertStatus(200);

        $updatedCategory = Category::find($categoryId);

        $this->assertTrue(
            $updatedCategory->name === $category['name'] &&
                $updatedCategory->ancestor_id === $category['parent']
        );
    }

    public function test_fail_updating_without_name()
    {
        $admin = $this->getAdmin();

        $categoryId = $this->getCreatedCategoryId();

        $category = [
            'name' => null,
            'parent' => null
        ];

        $response = $this->actingAs($admin)
            ->json('PUT', 'nova-api/categories/' . $categoryId . '?editing=true&editMode=update', $category);

        $response->assertStatus(422);

        $response->assertInvalid('name');
    }

    public function getAdmin()
    {
        return User::whereHas(
            'roles', function ($q) {
                $q->where('name', 'Admin');
            }
        )->first();
    }

    public function getCreatedCategoryId()
    {
        $admin = $this->getAdmin();

        $category = [
            'name' => fake()->word(),
            'parent' => null
        ];

        $response = $this->actingAs($admin)
            ->json('POST', 'nova-api/categories?editing=true&editMode=create', $category);

        return $response->original['resource']['id'];
    }
}
