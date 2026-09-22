<?php

namespace Tests\Feature;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use App\Orchid\Screens\Article\ArticleListScreen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ArticleListScreenTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->permissions = ['platform.index' => true];
        $admin->save();

        return $admin;
    }

    /**
     * Вызывает метод экрана напрямую с сформированным запросом модалки
     * (поля вида article[title], article[tags] и т.п.).
     */
    private function callCreateOrUpdate(array $payload): void
    {
        $request = ArticleRequest::create('/admin/articles', 'POST', $payload);

        (new ArticleListScreen())->createOrUpdateArticle($request);
    }

    public function test_create_or_update_article_creates_article_with_tags(): void
    {
        $this->actingAs($this->admin());

        $rubric = Rubric::factory()->create();
        $tag = Tag::factory()->create(['active' => true]);

        $this->callCreateOrUpdate([
            'article' => [
                'id' => null,
                'title' => 'Новая тестовая статья',
                'slug' => null,
                'rubric_id' => $rubric->id,
                'tags' => [$tag->id],
                'content_raw' => "# Заголовок\n\nТекст абзаца.",
                'is_published' => true,
                'published_at' => now()->format('Y-m-d'),
                'keywords' => 'тест',
                'meta_desc' => 'Описание статьи',
            ],
        ]);

        $article = Article::where('title', 'Новая тестовая статья')->first();

        $this->assertNotNull($article);
        $this->assertTrue($article->is_published);
        $this->assertSame($rubric->id, $article->rubric_id);
        $this->assertSame([$tag->id], $article->tags->pluck('id')->all());
    }

    public function test_create_or_update_article_updates_existing_article_and_syncs_tags(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $rubric = Rubric::factory()->create();
        $oldTag = Tag::factory()->create(['active' => true]);
        $newTag = Tag::factory()->create(['active' => true]);

        $article = Article::factory()->create([
            'rubric_id' => $rubric->id,
            'user_id' => $admin->id,
            'title' => 'Статья для обновления',
            'is_published' => false,
        ]);
        $article->tags()->attach($oldTag->id);

        $this->callCreateOrUpdate([
            'article' => [
                'id' => $article->id,
                'title' => 'Обновлённый заголовок',
                'slug' => null,
                'rubric_id' => $rubric->id,
                'tags' => [$newTag->id],
                'content_raw' => 'Новый контент.',
                'is_published' => true,
                'published_at' => now()->format('Y-m-d'),
                'keywords' => 'обновление',
                'meta_desc' => 'Новое описание',
            ],
        ]);

        $article->refresh();

        $this->assertSame('Обновлённый заголовок', $article->title);
        $this->assertTrue($article->is_published);
        $this->assertSame([$newTag->id], $article->tags->pluck('id')->all());
    }

    public function test_article_request_rejects_invalid_rubric_and_tags(): void
    {
        $rules = (new ArticleRequest())->rules();

        $validator = Validator::make([
            'article' => [
                'title' => 'Заголовок',
                'content_raw' => 'Контент',
                'rubric_id' => 999_999,
                'published_at' => now()->format('Y-m-d'),
                'tags' => [999_999],
            ],
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('article.rubric_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('article.tags.0', $validator->errors()->toArray());
    }

    public function test_article_request_accepts_valid_payload(): void
    {
        $rules = (new ArticleRequest())->rules();

        $rubric = Rubric::factory()->create();
        $tag = Tag::factory()->create(['active' => true]);

        $validator = Validator::make([
            'article' => [
                'title' => 'Заголовок',
                'content_raw' => 'Контент',
                'rubric_id' => $rubric->id,
                'published_at' => now()->format('Y-m-d'),
                'tags' => [$tag->id],
            ],
        ], $rules);

        $this->assertTrue($validator->passes(), implode(PHP_EOL, $validator->errors()->all()));
    }
}
