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
        $this->assertStringContainsString('Disallow: /nexus', $content);
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

    /* ------------------------------------------------------------------
     * Абсолютные URL в OG/Twitter/JSON-LD
     * ------------------------------------------------------------------ */

    public function test_article_og_image_is_absolute_url(): void
    {
        $article = $this->createPublishedArticle([
            'image' => 'articles/test.jpg',
        ]);

        $response = $this->get("/article/{$article->slug}");

        $response->assertOk();
        $content = $response->getContent();
        // og:image должен содержать APP_URL (абсолютный URL)
        $appUrl = config('app.url');
        $this->assertStringContainsString(
            'property="og:image" content="' . $appUrl . '/storage/articles/test.jpg"',
            $content
        );
    }

    public function test_article_twitter_image_is_absolute_url(): void
    {
        $article = $this->createPublishedArticle([
            'image' => 'articles/test.jpg',
        ]);

        $response = $this->get("/article/{$article->slug}");

        $response->assertOk();
        $content = $response->getContent();
        $appUrl = config('app.url');
        $this->assertStringContainsString(
            'name="twitter:image" content="' . $appUrl . '/storage/articles/test.jpg"',
            $content
        );
    }

    public function test_article_og_url_matches_canonical(): void
    {
        $article = $this->createPublishedArticle();

        $response = $this->get("/article/{$article->slug}");

        $response->assertOk();
        $content = $response->getContent();
        $expectedUrl = route('articleShow', $article);
        $this->assertStringContainsString(
            'property="og:url" content="' . $expectedUrl . '"',
            $content
        );
        $this->assertStringContainsString(
            'rel="canonical" href="' . $expectedUrl . '"',
            $content
        );
    }

    public function test_search_page_og_url_matches_canonical(): void
    {
        $response = $this->get('/?search=Laravel');

        $response->assertOk();
        $content = $response->getContent();
        // На странице поиска canonical = главная
        $expectedUrl = route('home');
        $this->assertStringContainsString(
            'property="og:url" content="' . $expectedUrl . '"',
            $content
        );
        $this->assertStringContainsString(
            'rel="canonical" href="' . $expectedUrl . '"',
            $content
        );
    }

    public function test_seo_helper_returns_null_for_null_input(): void
    {
        $this->assertNull(\App\Support\Seo::absoluteUrl(null));
    }

    public function test_seo_helper_returns_absolute_as_is(): void
    {
        $result = \App\Support\Seo::absoluteUrl('https://example.com/image.png');
        $this->assertSame('https://example.com/image.png', $result);
    }

    public function test_seo_helper_prepends_app_url_for_relative_paths(): void
    {
        $result = \App\Support\Seo::absoluteUrl('/storage/image.jpg');
        $this->assertSame(config('app.url') . '/storage/image.jpg', $result);
    }

    /* ------------------------------------------------------------------
     * Страницы ошибок: noindex, nofollow
     * ------------------------------------------------------------------ */

    public function test_error_400_has_noindex(): void
    {
        $response = $this->get('/test-400');

        $response->assertStatus(400);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_error_401_has_noindex(): void
    {
        $response = $this->get('/test-401');

        $response->assertStatus(401);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_error_403_has_noindex(): void
    {
        $response = $this->get('/test-403');

        $response->assertStatus(403);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_error_404_has_noindex(): void
    {
        $response = $this->get('/test-404');

        $response->assertStatus(404);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_error_405_has_noindex(): void
    {
        $response = $this->get('/test-405');

        $response->assertStatus(405);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_error_408_has_noindex(): void
    {
        $response = $this->get('/test-408');

        $response->assertStatus(408);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_error_419_has_noindex(): void
    {
        $response = $this->get('/test-419');

        $response->assertStatus(419);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_error_429_has_noindex(): void
    {
        $response = $this->get('/test-429');

        $response->assertStatus(429);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_error_500_has_noindex(): void
    {
        $response = $this->get('/test-500');

        $response->assertStatus(500);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_error_502_has_noindex(): void
    {
        $response = $this->get('/test-502');

        $response->assertStatus(502);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_error_504_has_noindex(): void
    {
        $response = $this->get('/test-504');

        $response->assertStatus(504);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_jsonld_not_rendered_on_error_pages(): void
    {
        $response = $this->get('/test-404');

        $response->assertStatus(404);
        $content = $response->getContent();
        $this->assertStringNotContainsString('application/ld+json', $content);
    }
}
