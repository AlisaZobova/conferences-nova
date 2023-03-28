<?php

namespace Tests\Unit;

use App\Mail\NewReportComment;
use App\Models\Conference;
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
        $user = User::whereHas(
            'roles', function ($q) {
                $q->where('name', 'Admin');
            }
        )->first();

        $comment = $this->getCommentData($user);

        $response = $this->actingAs($user)->json('POST', 'api/comments', $comment);

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

        $comment = [
            'content' => null,
            'publication_date' => now(),
            'user_id' => $user->id,
            'report_id' => $this->getCreatedReportId()
        ];

        $response = $this->actingAs($user)->json('POST', 'api/comments', $comment);

        $response->assertStatus(422);

        $response->assertInvalid('content');
    }

    public function getCreatedReportId()
    {
        $conference = Conference::factory()->create();
        $announcer = User::factory()->create_announcer();

        $report = [
            'user_id' => $announcer->id,
            'conference_id' => $conference->id,
            'topic' => fake()->word(),
            'start_time' => $conference->conf_date->format('Y-m-d') . ' 12:00:00',
            'end_time' => $conference->conf_date->format('Y-m-d') . ' 12:30:00',
            'description' => '',
            'presentation' => null,
            'online' => 'false'
        ];

        $response = $this->actingAs($announcer)
            ->json('POST', 'api/reports', $report);

        $this->actingAs($announcer)
            ->json('POST', 'api/logout');

        return $response->original['id'];
    }

    public function getCommentData($user)
    {
        return [
            'content' => fake()->sentence(),
            'publication_date' => now(),
            'user_id' => $user->id,
            'report_id' => $this->getCreatedReportId()
        ];
    }
}
