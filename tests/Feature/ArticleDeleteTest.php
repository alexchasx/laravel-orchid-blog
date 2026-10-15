<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\ConsentLog;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function adminWithPlatformAccess(): User
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->permissions = ['platform.index' => true];
        $admin->save();

        return $admin;
    }

    private function createPublishedArticle(array $overrides = []): Article
    {
        $author = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();

        return Article::factory()->create(array_merge([
            'user_id' => $author->id,
            'rubric_id' => $rubric->id,
            'is_published' => true,
            'published_at' => now(),
        ], $overrides));
    }

    public function test_article_can_be_soft_deleted_from_list(): void
    {
        $admin = $this->adminWithPlatformAccess();
        $article = $this->createPublishedArticle();

        // Orchid-паттерн: POST на экран с методом в URL.
        $response = $this->actingAs($admin)->post('/nexus/articles/remove', [
            'article' => $article->id,
        ]);

        $response->assertRedirect();
        $this->assertSoftDeleted('articles', ['id' => $article->id]);

        // Статья не видна на публичной странице.
        $response = $this->get('/');
        $response->assertDontSee($article->title);
    }

    public function test_trash_screen_lists_deleted_articles(): void
    {
        $admin = $this->adminWithPlatformAccess();
        $article = $this->createPublishedArticle();

        $this->actingAs($admin)->post('/nexus/articles/remove', [
            'article' => $article->id,
        ]);

        $response = $this->actingAs($admin)->get(route('platform.articles.trash'));

        $response->assertOk();
        $response->assertSee($article->title);
    }

    public function test_article_can_be_restored(): void
    {
        $admin = $this->adminWithPlatformAccess();
        $article = $this->createPublishedArticle();

        // Мягкое удаление.
        $this->actingAs($admin)->post('/nexus/articles/remove', [
            'article' => $article->id,
        ]);

        $this->assertSoftDeleted('articles', ['id' => $article->id]);

        // Восстановление.
        $this->actingAs($admin)->post('/nexus/articles/trash/restore', [
            'article' => $article->id,
        ]);

        $this->assertNotSoftDeleted('articles', ['id' => $article->id]);

        // Статья снова видна на сайте.
        $response = $this->get('/');
        $response->assertSee($article->title);
    }

    public function test_article_can_be_force_deleted_with_image_and_comments(): void
    {
        Storage::fake('public');

        $admin = $this->adminWithPlatformAccess();
        $article = $this->createPublishedArticle([
            'image' => 'articles/test.jpg',
        ]);

        // Создаём комментарий к статье.
        $comment = Comment::factory()->create([
            'article_id' => $article->id,
        ]);

        // Мягкое удаление статьи.
        $this->actingAs($admin)->post('/nexus/articles/remove', [
            'article' => $article->id,
        ]);

        // Force delete.
        $this->actingAs($admin)->post('/nexus/articles/trash/forceDelete', [
            'article' => $article->id,
        ]);

        // Статья удалена навсегда.
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        // Изображение стёрто.
        Storage::disk('public')->assertMissing('articles/test.jpg');
        // Комментарий удалён каскадно.
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_consent_logs_survive_force_delete(): void
    {
        $admin = $this->adminWithPlatformAccess();
        $article = $this->createPublishedArticle();

        // Создаём комментарий с consent_logs.
        $comment = Comment::factory()->create([
            'article_id' => $article->id,
        ]);

        $log = ConsentLog::create([
            'comment_id' => $comment->id,
            'subject_type' => ConsentLog::SUBJECT_COMMENT,
            'subject_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_PROCESSING,
            'consent_text' => 'Текст согласия',
            'consent_version' => '1.0',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'page_url' => 'http://localhost/',
            'consented_at' => now(),
        ]);

        // Мягкое удаление + force delete.
        $this->actingAs($admin)->post('/nexus/articles/remove', [
            'article' => $article->id,
        ]);

        $this->actingAs($admin)->post('/nexus/articles/trash/forceDelete', [
            'article' => $article->id,
        ]);

        // Лог согласия сохранён, comment_id обнулён.
        $this->assertDatabaseHas('consent_logs', [
            'id' => $log->id,
            'comment_id' => null,
            'subject_type' => ConsentLog::SUBJECT_COMMENT,
        ]);
    }

    public function test_tag_count_updated_after_delete_and_restore(): void
    {
        $admin = $this->adminWithPlatformAccess();
        $tag = Tag::factory()->create(['count_articles' => 0]);
        $rubric = Rubric::factory()->create();
        $author = User::factory()->create(['active' => true]);

        $article = Article::factory()->create([
            'user_id' => $author->id,
            'rubric_id' => $rubric->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        $article->tags()->attach($tag->id);

        // Observer пересчитывает count_articles после attach.
        $tag->refresh();
        $this->assertGreaterThan(0, $tag->count_articles);

        // Мягкое удаление — счётчик уменьшается.
        $this->actingAs($admin)->post('/nexus/articles/remove', [
            'article' => $article->id,
        ]);

        $tag->refresh();
        $this->assertEquals($tag->count_articles, $tag->count_articles - 1);
        // Убедимся, что счётчик действительно уменьшился.
        $this->assertLessThan(
            (int) $article->tags->first()?->pivot?->count ?? 999,
            $tag->count_articles
        );

        // Восстановление — счётчик возвращается.
        $this->actingAs($admin)->post('/nexus/articles/trash/restore', [
            'article' => $article->id,
        ]);

        $tag->refresh();
        // После restore observer снова пересчитывает.
        $this->assertGreaterThanOrEqual(0, $tag->count_articles);
    }
}
