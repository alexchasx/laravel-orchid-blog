<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Rubric;
use App\Models\User;
use App\Support\MathCaptcha;
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

    private function solveCaptcha(): int
    {
        MathCaptcha::question();

        return session('captcha_answer');
    }

    public function test_auth_user_can_store_comment(): void
    {
        $user = User::factory()->create(['active' => true]);
        $article = $this->createAuthoredArticle();

        $response = $this->actingAs($user)->post('/comment.create', [
            'comment' => 'Отличная статья, спасибо!',
            'article_id' => $article->id,
            'consent_processing' => 1,
            'consent_distribution' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'content' => 'Отличная статья, спасибо!',
            'active' => true,
            'ip' => '127.0.0.1',
        ]);
    }

    public function test_guest_can_store_comment_with_captcha_and_goes_to_moderation(): void
    {
        $article = $this->createAuthoredArticle();

        $response = $this->post('/comment.create', [
            'comment' => 'Комментарий от гостя',
            'article_id' => $article->id,
            'name' => 'Гость',
            'email' => 'guest@example.com',
            'captcha' => $this->solveCaptcha(),
            'consent_processing' => 1,
            'consent_distribution' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'user_id' => null,
            'name' => 'Гость',
            'email' => 'guest@example.com',
            'content' => 'Комментарий от гостя',
            'active' => false,
            'ip' => '127.0.0.1',
        ]);
        $response->assertSessionHas('success');
    }

    public function test_guest_needs_name_and_email(): void
    {
        $article = $this->createAuthoredArticle();

        $response = $this->post('/comment.create', [
            'comment' => 'Комментарий без имени и email',
            'article_id' => $article->id,
            'captcha' => $this->solveCaptcha(),
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_guest_comment_without_captcha_fails_validation(): void
    {
        $article = $this->createAuthoredArticle();

        $response = $this->post('/comment.create', [
            'comment' => 'Комментарий без капчи',
            'article_id' => $article->id,
            'name' => 'Гость',
            'email' => 'guest@example.com',
        ]);

        $response->assertSessionHasErrors('captcha');
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_guest_comment_with_wrong_captcha_fails_validation(): void
    {
        $article = $this->createAuthoredArticle();

        $correct = $this->solveCaptcha();

        $response = $this->post('/comment.create', [
            'comment' => 'Комментарий с неверной капчей',
            'article_id' => $article->id,
            'name' => 'Гость',
            'email' => 'guest@example.com',
            'captcha' => $correct + 1,
        ]);

        $response->assertSessionHasErrors('captcha');
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_comment_for_missing_article_fails_validation(): void
    {
        $response = $this->post('/comment.create', [
            'comment' => 'Комментарий к несуществующей статье',
            'article_id' => 999999,
            'name' => 'Гость',
            'email' => 'guest@example.com',
            'captcha' => $this->solveCaptcha(),
        ]);

        $response->assertSessionHasErrors('article_id');
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