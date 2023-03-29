<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteReportCommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_delete_comment_method_not_allowed()
    {
        $comment = $this->getComment();

        $response = $this
            ->actingAs($this->getAuthor())
            ->json('DELETE', 'api/comments/' . $comment->id);

        $response->assertStatus(405);
    }

    public function getAuthor()
    {
        return User::find($this->getComment()->user_id);
    }

    public function getComment()
    {
        return Comment::factory()->create();
    }
}
