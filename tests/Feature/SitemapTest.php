<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Создаёт опубликованную статью с рубрикой, автором и тегом.
     */
    private function createPublishedArticle(array $attributes = []): Article
    {
        $user = \App\Models\User::factory()->create(['active' => true]);
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

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('sitemap.xml');
    }

    public function test_sitemap_returns_ok(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_contains_homepage(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertSee('loc', false);
        $response->assertSee(route('home'), false);
    }

    public function test_sitemap_contains_static_pages(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertSee(route('about'), false);
        $response->assertSee(route('contact'), false);
        $response->assertSee(route('privacy'), false);
    }

    public function test_sitemap_is_valid_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $xml = $response->getContent();
        $previous = libxml_disable_entity_loader(true);
        $dom = new \DOMDocument();
        $result = $dom->loadXML($xml);
        libxml_disable_entity_loader($previous);

        $this->assertTrue($result, 'sitemap.xml должен быть валидным XML');
    }

    public function test_sitemap_includes_image_namespace(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertSee('sitemap-image', false);
    }
}
