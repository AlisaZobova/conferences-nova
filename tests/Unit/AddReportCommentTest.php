<?php

namespace Tests\Unit;

use App\Mail\NewReportComment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AddReportCommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_adding()
    {
        Mail::fake();

        $user = User::factory()->create();
        $comment = $this->getCommentData($user);

        $response = $this->actingAs($user)->json('POST', 'api/comments', $comment);

        $response->assertStatus(201);

        $this->assertModelExists($response->original);

        Mail::assertQueued(NewReportComment::class);
    }

    public function test_fail_adding_by_admin()
    {
        $admin = User::whereHas(
            'roles', function ($q) {
            $q->where('name', 'Admin');
        }
        )->first();

        $comment = $this->getCommentData($admin);

        $response = $this->actingAs($admin)->json('POST', 'api/comments', $comment);

        $response->assertStatus(403);
    }

    public function test_fail_adding_no_auth()
    {
        $user = User::factory()->create();

        $comment = $this->getCommentData($user);

        $response = $this->json('POST', 'api/comments', $comment);

        $response->assertStatus(401);
    }

    public function test_fail_adding_without_content()
    {
        $user = User::factory()->create();

        $report = Report::factory()->create();

        $comment = [
            'content' => null,
            'publication_date' => now(),
            'user_id' => $user->id,
            'report_id' => $report->id
        ];

        $response = $this->actingAs($user)->json('POST', 'api/comments', $comment);

        $response->assertStatus(422);

        $response->assertInvalid(['content' => 'The content field is required.']);
    }

    public function getCommentData($user)
    {
        $report = Report::factory()->create();

        return [
            'content' => fake()->sentence(),
            'publication_date' => now(),
            'user_id' => $user->id,
            'report_id' => $report->id
        ];
    }
}
