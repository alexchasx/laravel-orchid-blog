<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnpublishedArticlesPageTest extends TestCase
{
    use RefreshDatabase;

    private function createUnpublishedArticle(): Article
    {
        $author = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();

        return Article::factory()->create([
            'user_id' => $author->id,
            'rubric_id' => $rubric->id,
            'is_published' => false,
            'title' => 'Черновик из админки',
        ]);
    }

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/notpublic')->assertRedirect(route('login'));
    }

    public function test_regular_user_gets_forbidden(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->actingAs($user)->get('/notpublic')->assertForbidden();
    }

    public function test_admin_sees_unpublished_articles(): void
    {
        $article = $this->createUnpublishedArticle();
        $admin = User::factory()->create([
            'active' => true,
            'permissions' => ['platform.custom.articles' => true],
        ]);

        $response = $this->actingAs($admin)->get('/notpublic');

        $response->assertOk();
        $response->assertSee($article->title);
    }
}