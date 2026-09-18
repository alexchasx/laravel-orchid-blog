<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagModelTest extends TestCase
{
    use RefreshDatabase;

    private function createTagWithPublishedArticle(): Tag
    {
        $user = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();
        $tag = Tag::factory()->create(['active' => true]);

        $article = Article::factory()->create([
            'user_id' => $user->id,
            'rubric_id' => $rubric->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        $article->tags()->attach($tag->id);

        return $tag;
    }

    public function test_article_published_scope_returns_active_tag_with_published_articles(): void
    {
        $tag = $this->createTagWithPublishedArticle();

        $result = Tag::articlePublished()->get();

        $this->assertTrue($result->contains('id', $tag->id));
    }

    public function test_article_published_scope_excludes_inactive_tags(): void
    {
        $user = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();
        $inactiveTag = Tag::factory()->create(['active' => false]);

        $article = Article::factory()->create([
            'user_id' => $user->id,
            'rubric_id' => $rubric->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        $article->tags()->attach($inactiveTag->id);

        $result = Tag::articlePublished()->get();

        $this->assertFalse($result->contains('id', $inactiveTag->id));
    }

    public function test_article_published_scope_excludes_tags_without_articles(): void
    {
        Tag::factory()->create(['active' => true]);

        $result = Tag::articlePublished()->get();

        $this->assertTrue($result->isEmpty());
    }

    public function test_article_published_scope_excludes_tags_with_only_unpublished_articles(): void
    {
        $user = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();
        $tag = Tag::factory()->create(['active' => true]);

        $article = Article::factory()->create([
            'user_id' => $user->id,
            'rubric_id' => $rubric->id,
            'is_published' => false,
            'published_at' => now(),
        ]);
        $article->tags()->attach($tag->id);

        $result = Tag::articlePublished()->get();

        $this->assertTrue($result->isEmpty());
    }

    public function test_articles_relation(): void
    {
        $user = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();
        $tag = Tag::factory()->create(['active' => true]);

        $article = Article::factory()->create([
            'user_id' => $user->id,
            'rubric_id' => $rubric->id,
        ]);
        $article->tags()->attach($tag->id);

        $this->assertCount(1, $tag->articles);
        $this->assertTrue($tag->articles->contains('id', $article->id));
    }

    public function test_fillable_fields(): void
    {
        $tag = new Tag();

        $this->assertSame([
            'title',
            'popular',
            'active',
            'count_articles',
        ], $tag->getFillable());
    }

    public function test_timestamps_are_disabled(): void
    {
        $tag = new Tag();

        $this->assertFalse($tag->timestamps);
    }
}