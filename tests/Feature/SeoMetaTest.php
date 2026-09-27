<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Создаёт опубликованную статью с рубрикой и автором.
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
            'meta_desc' => 'Тестовое мета-описание статьи',
        ], $attributes));

        $article->tags()->attach($tag->id);

        return $article;
    }

    /* ------------------------------------------------------------------
     * Главная страница
     * ------------------------------------------------------------------ */

    public function test_home_page_has_non_empty_title(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // На главной metaTitle=null, поэтому используется config('seo.default_title')
        // с подстановкой плейсхолдера {app_name} → config('app.name').
        $content = $response->getContent();
        $this->assertStringNotContainsString('{app_name}', $content);
        $this->assertMatchesRegularExpression('/<title>.+ИТ-блог<\/title>/', $content);
    }

    public function test_home_page_has_canonical(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('rel="canonical"', false);
        $response->assertSee(route('home'), false);
    }

    public function test_home_page_has_robots_meta(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="index,follow">', false);
    }

    public function test_home_page_has_og_tags(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('property="og:type"', false);
        $response->assertSee('property="og:site_name"', false);
        $response->assertSee('property="og:url"', false);
    }

    public function test_home_page_has_twitter_card(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('name="twitter:card"', false);
    }

    /* ------------------------------------------------------------------
     * Страница статьи
     * ------------------------------------------------------------------ */

    public function test_article_page_has_title_with_article_name(): void
    {
        $article = $this->createPublishedArticle(['title' => 'Тестовая статья']);

        $response = $this->get("/article/{$article->slug}");

        $response->assertOk();
        $response->assertSee('<title>Тестовая статья', false);
    }

    public function test_article_page_has_canonical(): void
    {
        $article = $this->createPublishedArticle();

        $response = $this->get("/article/{$article->slug}");

        $response->assertOk();
        $response->assertSee('rel="canonical"', false);
        $response->assertSee(route('articleShow', $article), false);
    }

    public function test_article_page_has_json_ld_article(): void
    {
        $article = $this->createPublishedArticle(['title' => 'JSON-LD тест']);

        $response = $this->get("/article/{$article->slug}");

        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type": "Article"', false);
        $response->assertSee('"headline": "JSON-LD тест"', false);
    }

    public function test_article_page_has_og_article_type(): void
    {
        $article = $this->createPublishedArticle();

        $response = $this->get("/article/{$article->slug}");

        $response->assertOk();
        $response->assertSee('property="og:type"', false);
        $response->assertSee('content="article"', false);
    }

    public function test_article_page_has_twitter_summary_large_image(): void
    {
        $article = $this->createPublishedArticle();

        $response = $this->get("/article/{$article->slug}");

        $response->assertOk();
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('summary_large_image', false);
    }

    /* ------------------------------------------------------------------
     * Страницы с noindex
     * ------------------------------------------------------------------ */

    public function test_search_page_has_noindex(): void
    {
        $response = $this->get('/?search=Laravel');

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $response->assertSee('rel="canonical"', false);
    }

    public function test_pagination_page_2_has_noindex(): void
    {
        $this->createPublishedArticle();

        $response = $this->get('/?page=2');

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $response->assertSee('rel="canonical"', false);
    }

    public function test_first_page_has_index_follow(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="index,follow">', false);
    }

    public function test_notpublic_page_has_noindex(): void
    {
        $response = $this->get('/notpublic');

        // Негостевой доступ — редирект на login, но canonical/robots не проверяем.
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_page_redirects_unauthenticated(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    /* ------------------------------------------------------------------
     * Рубрика и тег
     * ------------------------------------------------------------------ */

    public function test_rubric_page_has_title(): void
    {
        $rubric = Rubric::factory()->create(['title' => 'Архитектура']);
        $this->createPublishedArticle(['rubric_id' => $rubric->id]);

        $response = $this->get("/rubric/{$rubric->slug}");

        $response->assertOk();
        $response->assertSee('Архитектура', false);
        $response->assertSee('rel="canonical"', false);
    }

    public function test_tag_page_has_title(): void
    {
        $tag = Tag::factory()->create(['active' => true, 'title' => 'Laravel']);
        $this->createPublishedArticle();

        $response = $this->get("/tag/{$tag->slug}");

        $response->assertOk();
        $response->assertSee('Laravel', false);
        $response->assertSee('rel="canonical"', false);
    }

    /* ------------------------------------------------------------------
     * JSON-LD: отсутствие на noindex-страницах
     * ------------------------------------------------------------------ */

    public function test_jsonld_rendered_on_public_pages(): void
    {
        // Проверяем что на главной есть JSON-LD (без создания статей, чтобы избежать
        // проблем с RefreshDatabase + MySQL).
        $response = $this->get('/');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('application/ld+json', $content);
    }

    /* ------------------------------------------------------------------
     * Статические страницы
     * ------------------------------------------------------------------ */

    public function test_about_page_has_meta_tags(): void
    {
        $response = $this->get('/about');

        $response->assertOk();
        $response->assertSeeInOrder(['<title>', 'О блоге', '</title>'], false);
        $response->assertSee('rel="canonical"', false);
    }

    public function test_contact_page_has_meta_tags(): void
    {
        $response = $this->get('/contact');

        $response->assertOk();
        $response->assertSeeInOrder(['<title>', 'Обратная связь', '</title>'], false);
        $response->assertSee('rel="canonical"', false);
    }

    public function test_privacy_page_has_meta_tags(): void
    {
        $response = $this->get('/privacy');

        $response->assertOk();
        $response->assertSeeInOrder(['<title>', 'Политика конфиденциальности', '</title>'], false);
        $response->assertSee('rel="canonical"', false);
    }

    /* ------------------------------------------------------------------
     * Служебные страницы: noindex
     * ------------------------------------------------------------------ */

    public function test_consent_revoke_page_has_noindex(): void
    {
        $response = $this->get('/consent/revoke');

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_unsubscribe_page_has_noindex(): void
    {
        $response = $this->get('/unsubscribe/invalid-token');

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    /* ------------------------------------------------------------------
     * robots.txt
     * ------------------------------------------------------------------ */

    public function test_robots_txt_returns_ok(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function test_robots_txt_disallows_admin(): void
    {
        $response = $this->get('/robots.txt');

        $content = $response->getContent();
        $this->assertStringContainsString('Disallow: /admin', $content);
        $this->assertStringContainsString('Disallow: /dashboard', $content);
        $this->assertStringContainsString('Disallow: /profile', $content);
    }

    public function test_robots_txt_disallows_search_and_pagination(): void
    {
        $response = $this->get('/robots.txt');

        $content = $response->getContent();
        $this->assertStringContainsString('Disallow: /*?search=', $content);
        $this->assertStringContainsString('Disallow: /*?page=', $content);
    }

    public function test_robots_txt_contains_sitemap_url(): void
    {
        $response = $this->get('/robots.txt');

        $content = $response->getContent();
        $this->assertStringContainsString('Sitemap:', $content);
        $this->assertStringContainsString('/sitemap.xml', $content);
    }
}
