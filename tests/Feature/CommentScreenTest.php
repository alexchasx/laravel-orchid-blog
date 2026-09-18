<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Rubric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentScreenTest extends TestCase
{
    use RefreshDatabase;

    private function adminWithPlatformAccess(): User
    {
        $admin = User::factory()->create(['active' => true]);
        $admin->permissions = ['platform.index' => true];
        $admin->save();

        return $admin;
    }

    private function createArticle(): Article
    {
        $author = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();

        return Article::factory()->create([
            'user_id' => $author->id,
            'rubric_id' => $rubric->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function test_comment_screen_renders_guest_comment_data(): void
    {
        $comment = Comment::factory()->create([
            'article_id' => $this->createArticle()->id,
            'user_id' => null,
            'name' => 'conia111',
            'email' => 'dfg@sdfsdfsdf',
            'content' => 'sdf sdf sdf sdf ds',
            'active' => false,
            'ip' => '172.18.0.1',
        ]);

        $response = $this->actingAs($this->adminWithPlatformAccess())
            ->get("/admin/comment/{$comment->id}");

        $response->assertOk();
        $response->assertSee('conia111');
        $response->assertSee('dfg@sdfsdfsdf');
        $response->assertSee('sdf sdf sdf sdf ds');
        $response->assertSee('На модерации');
        $response->assertSee('Одобрить');
    }

    public function test_comment_screen_shows_approved_comment_and_approve_button(): void
    {
        $comment = Comment::factory()->create([
            'article_id' => $this->createArticle()->id,
            'user_id' => null,
            'active' => false,
        ]);

        $admin = $this->adminWithPlatformAccess();

        $this->actingAs($admin)->post("/admin/comment/{$comment->id}/approve");

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'active' => true]);
        $response = $this->actingAs($admin)->get("/admin/comment/{$comment->id}");

        $response->assertOk();
        $response->assertSee('Опубликован');
        $response->assertDontSee('Одобрить');
    }
}