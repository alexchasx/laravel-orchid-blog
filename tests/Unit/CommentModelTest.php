<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Rubric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModelTest extends TestCase
{
    use RefreshDatabase;

    private function createComment(): Comment
    {
        $user = User::factory()->create(['active' => true]);
        $author = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();

        $article = Article::factory()->create([
            'user_id' => $author->id,
            'rubric_id' => $rubric->id,
        ]);

        return Comment::factory()->create([
            'article_id' => $article->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_belongs_to_article(): void
    {
        $comment = $this->createComment();

        $this->assertInstanceOf(Article::class, $comment->article);
        $this->assertSame($comment->article_id, $comment->article->id);
    }

    public function test_belongs_to_user(): void
    {
        $comment = $this->createComment();

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertSame($comment->user_id, $comment->user->id);
    }

    public function test_fillable_fields(): void
    {
        $comment = new Comment();

        $this->assertSame([
            'name',
            'email',
            'website',
            'ip',
            'user_id',
            'article_id',
            'content',
            'active',
        ], $comment->getFillable());
    }

    public function test_soft_deletes(): void
    {
        $comment = $this->createComment();

        $comment->delete();

        $this->assertNull(Comment::find($comment->id));
        $this->assertNotNull(Comment::withTrashed()->find($comment->id));
    }
}