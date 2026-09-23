<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Создаёт опубликованную статью с рубрикой, автором и тегом.
     */
    private function createPublishedArticle(array $attributes = []): Article
    {
        $user = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();
        $tag = Tag::factory()->create(['active' => true]);

        $article = Article::factory()->create(array_merge([
            'user_id' => $user->id,
            'rubric_id' => $rubric->id,
            'is_published' => true,
            'published_at' => now(),
        ], $attributes));

        $article->tags()->attach($tag->id);

        return $article;
    }

    public function test_home_page_returns_ok_and_shows_article_title(): void
    {
        $article = $this->createPublishedArticle();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee($article->title);
    }

    public function test_home_page_shows_only_published_articles(): void
    {
        $published = $this->createPublishedArticle(['title' => 'Опубликованная статья']);
        $unpublished = $this->createPublishedArticle([
            'is_published' => false,
            'title' => 'Черновик статьи',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee($published->title);
        $response->assertDontSee($unpublished->title);
    }

    public function test_home_page_does_not_show_future_articles(): void
    {
        $future = $this->createPublishedArticle([
            'title' => 'Статья из будущего',
            'published_at' => now()->addDay(),
        ]);
        $current = $this->createPublishedArticle(['title' => 'Актуальная статья']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee($current->title);
        $response->assertDontSee($future->title);
    }

    public function test_home_page_search_filters_by_title(): void
    {
        $this->createPublishedArticle(['title' => 'Laravel в проде']);
        $this->createPublishedArticle(['title' => 'Vue на проектах']);

        $response = $this->get('/?search=Laravel');

        $response->assertOk();
        $response->assertSee('Laravel в проде');
        $response->assertDontSee('Vue на проектах');
    }

    public function test_article_show_page_renders_content_and_toc(): void
    {
        $article = $this->createPublishedArticle([
            'title' => 'Как устроен Vite',
            'content_raw' => "## Введение\n\nТекст о сборщике.\n\n### Настройка\n\nДетали.",
        ]);

        $response = $this->get("/article/{$article->slug}");

        $response->assertOk();
        $response->assertSee($article->title);
        $response->assertSee('Введение');
        $response->assertSee('В статье');
    }

    public function test_guest_cannot_view_unpublished_article(): void
    {
        $article = $this->createPublishedArticle(['is_published' => false]);

        $this->get("/article/{$article->slug}")->assertForbidden();
    }

    public function test_regular_user_cannot_view_unpublished_article(): void
    {
        $article = $this->createPublishedArticle(['is_published' => false]);
        $user = User::factory()->create(['active' => true]);

        $this->actingAs($user)
            ->get("/article/{$article->slug}")
            ->assertForbidden();
    }

    public function test_admin_can_view_unpublished_article(): void
    {
        $article = $this->createPublishedArticle([
            'is_published' => false,
            'title' => 'Черновик для админа',
        ]);
        $admin = User::factory()->create([
            'active' => true,
            'permissions' => ['platform.custom.articles' => true],
        ]);

        $response = $this->actingAs($admin)->get("/article/{$article->slug}");

        $response->assertOk();
        $response->assertSee($article->title);
    }

    public function test_show_by_rubric_returns_only_articles_of_that_rubric(): void
    {
        $rubricOne = Rubric::factory()->create();
        $rubricTwo = Rubric::factory()->create();

        $articleOne = $this->createPublishedArticle([
            'title' => 'Статья рубрики один',
            'rubric_id' => $rubricOne->id,
        ]);
        $articleTwo = $this->createPublishedArticle([
            'title' => 'Статья рубрики два',
            'rubric_id' => $rubricTwo->id,
        ]);

        $response = $this->get("/rubric/{$rubricOne->id}");

        $response->assertOk();
        $response->assertSee($articleOne->title);
        $response->assertDontSee($articleTwo->title);
    }

    public function test_home_page_hides_hero_section(): void
    {
        $this->createPublishedArticle();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('class="hero container', false);
    }

    public function test_rubric_page_hides_hero_section(): void
    {
        $rubric = Rubric::factory()->create();
        $this->createPublishedArticle(['rubric_id' => $rubric->id]);

        $response = $this->get("/rubric/{$rubric->id}");

        $response->assertOk();
        $response->assertDontSee('class="hero container', false);
    }

    public function test_rubric_page_uses_rubric_title_as_articles_heading(): void
    {
        $rubric = Rubric::factory()->create(['title' => 'Архитектура']);
        $this->createPublishedArticle(['rubric_id' => $rubric->id]);

        $response = $this->get("/rubric/{$rubric->id}");

        $response->assertOk();
        $response->assertSee('<h2>Архитектура</h2>', false);
        $response->assertDontSee('Свежие материалы');
    }

    public function test_home_page_keeps_default_articles_heading(): void
    {
        $this->createPublishedArticle();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<h2>Свежие материалы</h2>', false);
    }

    public function test_show_by_tag_returns_only_articles_with_that_tag(): void
    {
        $tagOne = Tag::factory()->create(['active' => true]);
        $tagTwo = Tag::factory()->create(['active' => true]);

        $articleOne = $this->createPublishedArticle(['title' => 'Статья с тегом один']);
        $articleTwo = $this->createPublishedArticle(['title' => 'Статья с тегом два']);

        $articleOne->tags()->sync([$tagOne->id]);
        $articleTwo->tags()->sync([$tagTwo->id]);

        $response = $this->get("/tag/{$tagOne->id}");

        $response->assertOk();
        $response->assertSee($articleOne->title);
        $response->assertDontSee($articleTwo->title);
    }

    public function test_home_page_shows_only_rubrics_with_published_articles(): void
    {
        $withArticle = Rubric::factory()->create(['title' => 'Рубрика со статьёй']);
        $empty = Rubric::factory()->create(['title' => 'Пустая рубрика']);
        $this->createPublishedArticle(['rubric_id' => $withArticle->id]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee($withArticle->title);
        $response->assertDontSee($empty->title);
    }

    public function test_home_page_hides_rubric_without_published_articles(): void
    {
        $draftRubric = Rubric::factory()->create(['title' => 'Рубрика-черновик']);
        $this->createPublishedArticle([
            'rubric_id' => $draftRubric->id,
            'is_published' => false,
            'title' => 'Черновик статьи',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee($draftRubric->title);
    }

    public function test_home_page_hides_topics_section_when_no_rubrics_with_articles(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Исследуйте по темам');
    }

    public function test_set_locale_changes_session_locale(): void
    {
        $response = $this->get('/setlocale/ru', ['HTTP_REFERER' => '/']);

        $response->assertRedirect('/');
        $this->assertSame('ru', session('user_locale'));
    }
}
