<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteReportCommentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->seed();
    }

    public function test_delete_comment_method_not_allowed()
    {
        $comment = Comment::factory()->create();
        $author = User::find($comment->user_id);

        $response = $this
            ->actingAs($author)
            ->json('DELETE', 'api/comments/' . $comment->id);

        $response->assertStatus(405);
    }
}
