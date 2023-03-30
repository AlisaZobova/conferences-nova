<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeleteReportCommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_deleting()
    {
        $comment = $this->getComment();

        $response = $this
            ->actingAs($this->getAdmin())
            ->json('DELETE', 'nova-api/comments?resources[]=' . $comment->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_deleting_non_existent()
    {

        $latest = Comment::orderBy('id', 'DESC')->first();

        $commentId = $latest ? $latest->id + 1 : 1;

        $response = $this
            ->actingAs($this->getAdmin())
            ->json('DELETE', 'nova-api/comments?resources[]=' . $commentId);

        $response->assertStatus(200);
    }

    public function test_fail_no_auth()
    {
        $comment = $this->getComment();

        $response = $this
            ->json('DELETE', 'nova-api/comments?resources[]=' . $comment->id);

        $response->assertStatus(401);
        $this->assertModelExists($comment);
    }

    public function test_fail_no_admin()
    {
        $user = User::factory()->create();

        $comment = $this->getComment();

        $response = $this
            ->actingAs($user)
            ->json('DELETE', 'nova-api/comments?resources[]=' . $comment->id);

        $response->assertStatus(403);
        $this->assertModelExists($comment);
    }

    public function getComment()
    {
        return Comment::factory()->create();
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
