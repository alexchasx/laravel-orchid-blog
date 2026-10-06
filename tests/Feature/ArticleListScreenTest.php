<?php

namespace Tests\Feature;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use App\Orchid\Screens\Article\ArticleListScreen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
    private function callCreateOrUpdate(array $payload, array $files = []): void
    {
        // Сигнатура create(): uri, method, parameters, cookies, files, server,
        // поэтому файлы передаются 5-м аргументом.
        $request = ArticleRequest::create('/nexus/articles', 'POST', $payload, [], $files);

        (new ArticleListScreen())->createOrUpdateArticle($request);
    }

    /**
     * Базовый payload статьи без изображения (для переиспользования в тестах).
     */
    private function basePayload(int $rubricId, array $overrides = []): array
    {
        return ['article' => array_merge([
            'id' => null,
            'title' => 'Новая тестовая статья',
            'slug' => null,
            'rubric_id' => $rubricId,
            'tags' => [],
            'content_raw' => "# Заголовок\n\nТекст абзаца.",
            'is_published' => true,
            'published_at' => now()->format('Y-m-d'),
            'keywords' => '',
            'meta_desc' => '',
        ], $overrides)];
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

    public function test_create_or_update_article_uploads_image_file(): void
    {
        $this->actingAs($this->admin());
        Storage::fake('public');

        $rubric = Rubric::factory()->create();
        // fake()->image() требует GD-расширение в контейнере, поэтому используем
        // обычный fake-файл с MIME image/jpeg — ветку UploadedFile он покрывает полностью.
        $file = UploadedFile::fake()->create('cover.jpg', 1024, 'image/jpeg');

        $this->callCreateOrUpdate(
            $this->basePayload($rubric->id, ['title' => 'Статья с изображением']),
            ['article' => ['image' => $file]]
        );

        $article = Article::where('title', 'Статья с изображением')->first();

        $this->assertNotNull($article);
        $this->assertNotNull($article->image);
        $this->assertStringStartsWith('articles/', $article->image);
        $this->assertTrue(Storage::disk('public')->exists($article->image));
    }

    public function test_create_or_update_article_normalizes_image_relative_url(): void
    {
        $this->actingAs($this->admin());

        $rubric = Rubric::factory()->create();

        $this->callCreateOrUpdate($this->basePayload($rubric->id, [
            'title' => 'Статья с relativeUrl картинки',
            'image' => '/storage/articles/cover.jpg',
        ]));

        $article = Article::where('title', 'Статья с relativeUrl картинки')->first();

        $this->assertNotNull($article);
        $this->assertSame('articles/cover.jpg', $article->image);
    }

    public function test_create_or_update_article_normalizes_image_full_url(): void
    {
        $this->actingAs($this->admin());

        $rubric = Rubric::factory()->create();

        $this->callCreateOrUpdate($this->basePayload($rubric->id, [
            'title' => 'Статья с полным URL картинки',
            'image' => 'http://localhost:8080/storage/articles/cover.jpg',
        ]));

        $article = Article::where('title', 'Статья с полным URL картинки')->first();

        $this->assertNotNull($article);
        $this->assertSame('articles/cover.jpg', $article->image);
    }

    public function test_update_article_with_cleared_image_deletes_old_file(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        Storage::fake('public');
        Storage::disk('public')->put('articles/old.jpg', 'fake-content');

        $rubric = Rubric::factory()->create();
        $article = Article::factory()->create([
            'rubric_id' => $rubric->id,
            'user_id' => $admin->id,
            'title' => 'Статья для очистки картинки',
            'image' => 'articles/old.jpg',
        ]);

        $this->callCreateOrUpdate($this->basePayload($rubric->id, [
            'id' => $article->id,
            'title' => 'Статья для очистки картинки',
            'image' => '',
        ]));

        $article->refresh();

        $this->assertNull($article->image);
        $this->assertFalse(Storage::disk('public')->exists('articles/old.jpg'));
    }

    public function test_update_article_replacing_image_deletes_old_file(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        Storage::fake('public');
        Storage::disk('public')->put('articles/old.jpg', 'fake-content');

        $rubric = Rubric::factory()->create();
        $article = Article::factory()->create([
            'rubric_id' => $rubric->id,
            'user_id' => $admin->id,
            'title' => 'Статья для замены картинки',
            'image' => 'articles/old.jpg',
        ]);

        $this->callCreateOrUpdate($this->basePayload($rubric->id, [
            'id' => $article->id,
            'title' => 'Статья для замены картинки',
            'image' => '/storage/articles/new.jpg',
        ]));

        $article->refresh();

        $this->assertSame('articles/new.jpg', $article->image);
        $this->assertFalse(Storage::disk('public')->exists('articles/old.jpg'));
    }

    public function test_update_article_with_same_image_keeps_file(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        Storage::fake('public');
        Storage::disk('public')->put('articles/same.jpg', 'fake-content');

        $rubric = Rubric::factory()->create();
        $article = Article::factory()->create([
            'rubric_id' => $rubric->id,
            'user_id' => $admin->id,
            'title' => 'Статья без изменения картинки',
            'image' => 'articles/same.jpg',
        ]);

        $this->callCreateOrUpdate($this->basePayload($rubric->id, [
            'id' => $article->id,
            'title' => 'Статья без изменения картинки',
            'image' => '/storage/articles/same.jpg',
        ]));

        $article->refresh();

        $this->assertSame('articles/same.jpg', $article->image);
        $this->assertTrue(Storage::disk('public')->exists('articles/same.jpg'));
    }

    public function test_article_request_image_rules_accept_string_and_reject_non_image_file(): void
    {
        $rules = (new ArticleRequest())->rules();

        $rubric = Rubric::factory()->create();
        $tag = Tag::factory()->create(['active' => true]);

        $base = [
            'title' => 'Заголовок',
            'content_raw' => 'Контент',
            'rubric_id' => $rubric->id,
            'published_at' => now()->format('Y-m-d'),
            'tags' => [$tag->id],
        ];

        // Относительный путь от Picture-поля — валиден.
        $relative = Validator::make(
            ['article' => $base + ['image' => '/storage/articles/cover.jpg']],
            $rules
        );
        $this->assertTrue($relative->passes(), implode(PHP_EOL, $relative->errors()->all()));

        // Полный URL от Picture-поля — валиден.
        $absolute = Validator::make(
            ['article' => $base + ['image' => 'https://example.com/storage/articles/cover.jpg']],
            $rules
        );
        $this->assertTrue($absolute->passes(), implode(PHP_EOL, $absolute->errors()->all()));

        // Файл-не-изображение — отклоняется.
        $notImage = Validator::make(
            ['article' => $base + ['image' => UploadedFile::fake()->create('doc.txt', 100)]],
            $rules
        );
        $this->assertTrue($notImage->fails());
    }

    public function test_articles_screen_renders_meta_desc_counter(): void
    {
        $this->actingAs($this->admin());

        $response = $this->get('/nexus/articles');

        $response->assertOk();
        // Поле и его счётчик присутствуют в модалке «Создать статью».
        $response->assertSee('article[meta_desc]', false);
        $response->assertSee('js-meta-desc-counter', false);
        // Скрипт подсчёта символов подключён.
        $response->assertSee('counter.textContent', false);
    }
}
