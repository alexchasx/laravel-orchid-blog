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