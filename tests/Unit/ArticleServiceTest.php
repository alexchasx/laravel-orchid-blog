<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use App\Services\ArticleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ArticleServiceTest extends TestCase
{
    use RefreshDatabase;

    private ArticleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ArticleService();
    }

    private function createArticle(array $attributes = []): Article
    {
        $user = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();

        return Article::factory()->create(array_merge([
            'user_id' => $user->id,
            'rubric_id' => $rubric->id,
            'slug' => null,
        ], $attributes));
    }

    public function test_get_public_returns_only_published_articles_paginated(): void
    {
        $published = $this->createArticle([
            'is_published' => true,
            'published_at' => now(),
        ]);
        $unpublished = $this->createArticle([
            'is_published' => false,
            'published_at' => now(),
        ]);

        $result = $this->service->getPublic(null);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame(6, $result->perPage());
        $this->assertTrue($result->contains('id', $published->id));
        $this->assertFalse($result->contains('id', $unpublished->id));
    }

    public function test_get_public_filters_by_search(): void
    {
        $matching = $this->createArticle([
            'is_published' => true,
            'published_at' => now(),
            'title' => 'Searchable Article',
        ]);
        $this->createArticle([
            'is_published' => true,
            'published_at' => now(),
            'title' => 'Another One',
        ]);

        $result = $this->service->getPublic('Searchable');

        $this->assertTrue($result->contains('id', $matching->id));
        $this->assertCount(1, $result);
    }

    public function test_get_not_public_returns_only_unpublished_articles(): void
    {
        $draft = $this->createArticle([
            'is_published' => false,
            'published_at' => now(),
        ]);
        $published = $this->createArticle([
            'is_published' => true,
            'published_at' => now(),
        ]);

        $result = $this->service->getNotPublic();

        $this->assertTrue($result->contains('id', $draft->id));
        $this->assertFalse($result->contains('id', $published->id));
    }

    public function test_get_by_rubric_filters_correctly(): void
    {
        $rubricOne = Rubric::factory()->create();
        $rubricTwo = Rubric::factory()->create();

        $articleOne = $this->createArticle([
            'rubric_id' => $rubricOne->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        $this->createArticle([
            'rubric_id' => $rubricTwo->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $result = $this->service->getByRubric($rubricOne->id);

        $this->assertTrue($result->contains('id', $articleOne->id));
        $this->assertCount(1, $result);
    }

    public function test_get_by_tag_filters_correctly(): void
    {
        $tagOne = Tag::factory()->create(['active' => true]);
        $tagTwo = Tag::factory()->create(['active' => true]);

        $articleOne = $this->createArticle([
            'is_published' => true,
            'published_at' => now(),
        ]);
        $articleTwo = $this->createArticle([
            'is_published' => true,
            'published_at' => now(),
        ]);

        $articleOne->tags()->sync([$tagOne->id]);
        $articleTwo->tags()->sync([$tagTwo->id]);

        $result = $this->service->getByTag($tagOne->id);

        $this->assertTrue($result->contains('id', $articleOne->id));
        $this->assertFalse($result->contains('id', $articleTwo->id));
    }

    public function test_check_access_aborts_for_guest_viewing_unpublished(): void
    {
        $article = $this->createArticle(['is_published' => false]);

        try {
            $this->service->checkAccess($article);
            $this->fail('Ожидалось исключение 403.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_check_access_aborts_for_regular_user_viewing_unpublished(): void
    {
        $article = $this->createArticle(['is_published' => false]);
        $user = User::factory()->create(['active' => true]);

        $this->actingAs($user);

        try {
            $this->service->checkAccess($article);
            $this->fail('Ожидалось исключение 403.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_check_access_allows_admin_viewing_unpublished(): void
    {
        $article = $this->createArticle(['is_published' => false]);
        $admin = User::factory()->create([
            'active' => true,
            'permissions' => ['platform.custom.articles' => true],
        ]);

        $this->actingAs($admin);

        $this->service->checkAccess($article);

        $this->assertTrue(true);
    }

    public function test_check_access_allows_published_article_for_everyone(): void
    {
        $article = $this->createArticle([
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->service->checkAccess($article);

        $this->assertTrue(true);
    }

    public function test_with_toc_extracts_h2_h3_items(): void
    {
        $article = new Article();
        $article->content_html = '<h2>Introduction</h2><h3>Details</h3><p>Text</p>';

        $result = $this->service->withToc($article);

        $this->assertCount(2, $result['tocItems']);
        $this->assertSame('introduction', $result['tocItems'][0]['id']);
        $this->assertSame(2, $result['tocItems'][0]['level']);
        $this->assertSame('Introduction', $result['tocItems'][0]['text']);
        $this->assertSame('details', $result['tocItems'][1]['id']);
        $this->assertSame(3, $result['tocItems'][1]['level']);
        $this->assertStringContainsString('<h2 id="introduction">', $result['contentHtml']);
    }

    public function test_with_toc_makes_duplicate_headings_ids_unique(): void
    {
        $article = new Article();
        $article->content_html = '<h2>Topic</h2><h2>Topic</h2>';

        $result = $this->service->withToc($article);

        $this->assertSame(['topic', 'topic-1'], array_column($result['tocItems'], 'id'));
        $this->assertStringContainsString('<h2 id="topic">', $result['contentHtml']);
        $this->assertStringContainsString('<h2 id="topic-1">', $result['contentHtml']);
    }

    public function test_with_toc_honors_existing_id(): void
    {
        $article = new Article();
        $article->content_html = '<h2 id="custom-heading">Existing</h2>';

        $result = $this->service->withToc($article);

        $this->assertSame('custom-heading', $result['tocItems'][0]['id']);
        $this->assertSame('<h2 id="custom-heading">Existing</h2>', $result['contentHtml']);
    }

    public function test_with_toc_transliterates_cyrillic_headings_to_slug_ids(): void
    {
        $article = new Article();
        $article->content_html = '<h2>Введение</h2>';

        $result = $this->service->withToc($article);

        $this->assertSame('vvedenie', $result['tocItems'][0]['id']);
    }

    public function test_with_toc_returns_empty_items_for_empty_content(): void
    {
        $article = new Article();
        $article->content_html = '   ';

        $result = $this->service->withToc($article);

        $this->assertSame([], $result['tocItems']);
        $this->assertSame('   ', $result['contentHtml']);
    }

    public function test_tags_relation_returns_related_tags(): void
    {
        $user = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();
        $tag = Tag::factory()->create(['active' => true]);

        $article = Article::factory()->create([
            'user_id' => $user->id,
            'rubric_id' => $rubric->id,
        ]);
        $article->tags()->attach($tag->id);

        $this->assertInstanceOf(Collection::class, $article->tags);
        $this->assertTrue($article->tags->contains('id', $tag->id));
    }
}
