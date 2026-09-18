<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RubricModelTest extends TestCase
{
    use RefreshDatabase;

    private function createRubricWithArticle(bool $published = true): Rubric
    {
        $user = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();

        Article::factory()->create([
            'user_id' => $user->id,
            'rubric_id' => $rubric->id,
            'is_published' => $published,
            'published_at' => now(),
        ]);

        return $rubric;
    }

    public function test_article_published_scope_returns_rubrics_with_published_articles(): void
    {
        $rubric = $this->createRubricWithArticle();

        $result = Rubric::articlePublished()->get();

        $this->assertTrue($result->contains('id', $rubric->id));
    }

    public function test_article_published_scope_excludes_empty_rubrics(): void
    {
        Rubric::factory()->create();

        $result = Rubric::articlePublished()->get();

        $this->assertTrue($result->isEmpty());
    }

    public function test_article_published_scope_excludes_rubrics_with_only_unpublished_articles(): void
    {
        $rubric = $this->createRubricWithArticle(false);

        $result = Rubric::articlePublished()->get();

        $this->assertFalse($result->contains('id', $rubric->id));
    }

    public function test_articles_relation(): void
    {
        $user = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();

        $article = Article::factory()->create([
            'user_id' => $user->id,
            'rubric_id' => $rubric->id,
        ]);

        $this->assertCount(1, $rubric->articles);
        $this->assertTrue($rubric->articles->contains('id', $article->id));
    }

    public function test_fillable_fields(): void
    {
        $rubric = new Rubric();

        $this->assertSame([
            'parent_id',
            'slug',
            'title',
            'description',
        ], $rubric->getFillable());
    }

    public function test_timestamps_are_disabled(): void
    {
        $rubric = new Rubric();

        $this->assertFalse($rubric->timestamps);
    }
}