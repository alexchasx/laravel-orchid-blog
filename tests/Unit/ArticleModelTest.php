<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleModelTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_published_scope_returns_only_published_articles(): void
    {
        $published = $this->createArticle([
            'is_published' => true,
            'published_at' => now(),
        ]);
        $unpublished = $this->createArticle([
            'is_published' => false,
            'published_at' => now(),
        ]);

        $result = Article::published()->get();

        $this->assertTrue($result->contains('id', $published->id));
        $this->assertFalse($result->contains('id', $unpublished->id));
    }

    public function test_published_scope_excludes_future_articles(): void
    {
        $future = $this->createArticle([
            'is_published' => true,
            'published_at' => now()->addDay(),
        ]);
        $current = $this->createArticle([
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $result = Article::published()->get();

        $this->assertTrue($result->contains('id', $current->id));
        $this->assertFalse($result->contains('id', $future->id));
    }

    public function test_published_scope_orders_by_desc(): void
    {
        $older = $this->createArticle([
            'is_published' => true,
            'published_at' => now()->subDays(2),
        ]);
        $newer = $this->createArticle([
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $result = Article::published()->get();

        $this->assertTrue($result->first()->id === $newer->id);
    }

    public function test_auto_slug_generation_from_title(): void
    {
        $article = $this->createArticle(['title' => 'Hello World Test']);

        $this->assertSame('hello-world-test', $article->slug);
    }

    public function test_slug_uniqueness_on_save(): void
    {
        $this->createArticle(['title' => 'Duplicate Title']);
        $second = $this->createArticle(['title' => 'Duplicate Title']);

        $this->assertSame('duplicate-title-1', $second->slug);
    }

    public function test_content_raw_converts_to_html_on_save(): void
    {
        $article = $this->createArticle([
            'title' => 'Markdown',
            'content_raw' => "# Заголовок\n\nТекст абзаца.",
        ]);

        $this->assertStringContainsString('<h1>Заголовок</h1>', $article->content_html);
        $this->assertStringContainsString('<p>Текст абзаца.</p>', $article->content_html);
    }

    public function test_content_raw_strips_raw_html_on_save(): void
    {
        $article = $this->createArticle([
            'title' => 'Markdown с HTML',
            'content_raw' => "# Заголовок\n\n<script>alert(1)</script>\n\n<img src=x onerror=alert(1)>\n\n[опасная](javascript:alert(1))",
        ]);

        // Легитимная разметка CommonMark сохраняется...
        $this->assertStringContainsString('<h1>Заголовок</h1>', $article->content_html);
        // ...а сырой HTML / опасные схемы URL вырезаются (защита от Stored XSS).
        $this->assertStringNotContainsString('<script>', $article->content_html);
        $this->assertStringNotContainsString('onerror=', $article->content_html);
        $this->assertStringNotContainsString('javascript:', $article->content_html);
    }

    public function test_content_html_is_derived_and_not_mass_assignable(): void
    {
        $article = new Article([
            'title' => 'Без массового назначения',
            'content_html' => '<b>Инъекция</b>',
        ]);

        // content_html — производное поле, его нельзя протащить через массовое назначение.
        $this->assertNull($article->content_html);
    }

    public function test_search_scope_filters_by_title(): void
    {
        $matching = $this->createArticle(['title' => 'Laravel Performance Guide']);
        $other = $this->createArticle(['title' => 'Vue Components']);

        $result = Article::search('Laravel')->get();

        $this->assertTrue($result->contains('id', $matching->id));
        $this->assertFalse($result->contains('id', $other->id));
    }

    public function test_user_rubric_tags_comments_relations(): void
    {
        $user = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();
        $tag = Tag::factory()->create(['active' => true]);

        $article = Article::factory()->create([
            'user_id' => $user->id,
            'rubric_id' => $rubric->id,
        ]);
        $article->tags()->attach($tag->id);

        $comment = Comment::factory()->create([
            'article_id' => $article->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $article->user);
        $this->assertSame($user->id, $article->user->id);
        $this->assertInstanceOf(Rubric::class, $article->rubric);
        $this->assertSame($rubric->id, $article->rubric->id);
        $this->assertInstanceOf(Collection::class, $article->tags);
        $this->assertTrue($article->tags->contains('id', $tag->id));
        $this->assertInstanceOf(Collection::class, $article->comments);
        $this->assertTrue($article->comments->contains('id', $comment->id));
    }
}
