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