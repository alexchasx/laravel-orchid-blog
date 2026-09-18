<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Rubric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private function createAuthoredArticle(): Article
    {
        $author = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();

        return Article::factory()->create([
            'user_id' => $author->id,
            'rubric_id' => $rubric->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function test_auth_user_can_store_comment(): void
    {
        $user = User::factory()->create(['active' => true]);
        $article = $this->createAuthoredArticle();

        $response = $this->actingAs($user)->post('/comment.create', [
            'comment' => 'Отличная статья, спасибо!',
            'article_id' => $article->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'content' => 'Отличная статья, спасибо!',
            'active' => true,
        ]);
    }

    public function test_guest_cannot_store_comment(): void
    {
        $article = $this->createAuthoredArticle();

        $response = $this->post('/comment.create', [
            'comment' => 'Комментарий от гостя',
            'article_id' => $article->id,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_store_comment_with_empty_body_fails_validation(): void
    {
        $user = User::factory()->create(['active' => true]);
        $article = $this->createAuthoredArticle();

        $response = $this->actingAs($user)->post('/comment.create', [
            'comment' => '',
            'article_id' => $article->id,
        ]);

        $response->assertSessionHasErrors('comment');
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_delete_comment_soft_deletes_it(): void
    {
        $user = User::factory()->create(['active' => true]);
        $article = $this->createAuthoredArticle();

        $comment = Comment::factory()->create([
            'article_id' => $article->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(
            "/delete.{$comment->id}",
            [],
            ['HTTP_REFERER' => "/article/{$article->slug}"]
        );

        $response->assertRedirect("/article/{$article->slug}#comments");
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }
}