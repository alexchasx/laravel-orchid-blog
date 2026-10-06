<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FeedTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('rss.feed');
    }

    /* ------------------------------------------------------------------
     * Базовый ответ
     * ------------------------------------------------------------------ */

    public function test_rss_returns_ok(): void
    {
        $response = $this->get('/rss');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=utf-8');
    }

    public function test_rss_is_valid_xml(): void
    {
        $response = $this->get('/rss');

        $xml = $response->getContent();
        $previous = libxml_disable_entity_loader(true);
        $dom = new \DOMDocument();
        $result = $dom->loadXML($xml);
        libxml_disable_entity_loader($previous);

        $this->assertTrue($result, 'rss должен быть валидным XML');
    }

    public function test_rss_has_rss_2_root(): void
    {
        $response = $this->get('/rss');

        $content = $response->getContent();
        $this->assertStringContainsString('<rss version="2.0"', $content);
    }

    public function test_rss_has_channel_with_metadata(): void
    {
        $response = $this->get('/rss');

        $content = $response->getContent();
        $this->assertStringContainsString('<channel>', $content);
        $this->assertStringContainsString('<title>', $content);
        $this->assertStringContainsString('<link>', $content);
        $this->assertStringContainsString('<description>', $content);
        $this->assertStringContainsString('<language>ru</language>', $content);
        $this->assertStringContainsString('<lastBuildDate>', $content);
    }

    public function test_rss_has_atom_self_link(): void
    {
        $response = $this->get('/rss');

        $content = $response->getContent();
        $this->assertStringContainsString('atom:link', $content);
        $this->assertStringContainsString('rel="self"', $content);
        $this->assertStringContainsString(route('feed'), $content);
    }

    /* ------------------------------------------------------------------
     * Статьи в ленте
     * ------------------------------------------------------------------ */

    public function test_rss_contains_items_when_articles_exist(): void
    {
        $article = $this->createPublishedArticle(['title' => 'Тестовая статья для RSS']);

        $response = $this->get('/rss');

        $content = $response->getContent();
        $this->assertStringContainsString('<item>', $content);
        $this->assertStringContainsString('<title>Тестовая статья для RSS</title>', $content);
        $this->assertStringContainsString(
            '<link>' . route('articleShow', $article) . '</link>',
            $content
        );
        $this->assertStringContainsString(
            '<guid>' . route('articleShow', $article) . '</guid>',
            $content
        );
        $this->assertStringContainsString('<pubDate>', $content);
    }

    public function test_rss_items_ordered_by_published_at_desc(): void
    {
        $older = $this->createPublishedArticle([
            'title' => 'Статья 1 (старая)',
            'published_at' => now()->subDays(2),
        ]);
        $newer = $this->createPublishedArticle([
            'title' => 'Статья 2 (новая)',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/rss');

        $content = $response->getContent();
        // Новая статья должна идти раньше старой
        $posOlder = strpos($content, 'Статья 1 (старая)');
        $posNewer = strpos($content, 'Статья 2 (новая)');
        $this->assertNotFalse($posNewer);
        $this->assertNotFalse($posOlder);
        $this->assertLessThan($posOlder, $posNewer, 'Новые статьи должны идти раньше');
    }

    public function test_rss_limits_to_20_articles(): void
    {
        // Создаём 25 опубликованных статей
        for ($i = 1; $i <= 25; $i++) {
            $this->createPublishedArticle([
                'title' => "Статья {$i}",
                'published_at' => now()->subDays($i),
            ]);
        }

        $response = $this->get('/rss');

        $content = $response->getContent();
        $itemCount = substr_count($content, '<item>');
        $this->assertEquals(20, $itemCount, 'В ленте не более 20 статей');
    }

    public function test_rss_is_empty_channel_without_articles(): void
    {
        $response = $this->get('/rss');

        $content = $response->getContent();
        $this->assertStringContainsString('<channel>', $content);
        $this->assertStringNotContainsString('<item>', $content);
        $this->assertStringContainsString('<lastBuildDate>', $content);
    }

    /* ------------------------------------------------------------------
     * Description: meta_desc > excert > пусто
     * ------------------------------------------------------------------ */

    public function test_rss_item_description_prefers_meta_desc(): void
    {
        $article = $this->createPublishedArticle([
            'title' => 'Описание из meta_desc',
            'meta_desc' => 'Тестовое описание из meta_desc',
            'excerpt' => 'Тестовое описание из excerpt',
        ]);

        $response = $this->get('/rss');

        $content = $response->getContent();
        $this->assertStringContainsString(
            '<description>Тестовое описание из meta_desc</description>',
            $content
        );
    }

    public function test_rss_item_description_falls_back_to_excerpt(): void
    {
        $article = $this->createPublishedArticle([
            'title' => 'Описание из excerpt',
            'meta_desc' => '',
            'excerpt' => 'Тестовое описание из excerpt',
        ]);

        $response = $this->get('/rss');

        $content = $response->getContent();
        $this->assertStringContainsString(
            '<description>Тестовое описание из excerpt</description>',
            $content
        );
    }

    /* ------------------------------------------------------------------
     * Enclosure: изображение
     * ------------------------------------------------------------------ */

    public function test_rss_item_has_enclosure_with_image(): void
    {
        Storage::put('articles/cover.jpg', 'fake-jpeg-bytes');

        $this->createPublishedArticle([
            'title' => 'Статья с картинкой',
            'image' => 'articles/cover.jpg',
        ]);

        $response = $this->get('/rss');

        $content = $response->getContent();
        $this->assertStringContainsString('<enclosure', $content);
        $this->assertStringContainsString('type="image/jpeg"', $content);
        $this->assertStringContainsString('length="', $content);
    }

    public function test_rss_item_has_no_enclosure_without_image(): void
    {
        $this->createPublishedArticle([
            'title' => 'Статья без картинки',
            'image' => null,
        ]);

        $response = $this->get('/rss');

        $content = $response->getContent();
        // Должен быть один item без enclosure
        $this->assertStringContainsString('<item>', $content);
        $this->assertStringNotContainsString('<enclosure', $content);
    }

    /* ------------------------------------------------------------------
     * Кэширование
     * ------------------------------------------------------------------ */

    public function test_rss_uses_cache(): void
    {
        $this->createPublishedArticle(['title' => 'Кэшированная статья']);

        $response = $this->get('/rss');
        $response->assertStatus(200);

        // Удаляем статью — ответ не должен измениться (из кэша)
        Article::query()->delete();

        $response2 = $this->get('/rss');
        $response2->assertStatus(200);

        $content = $response2->getContent();
        $this->assertStringContainsString('<title>Кэшированная статья</title>', $content);
    }

    /* ------------------------------------------------------------------
     * Ссылка в <head>
     * ------------------------------------------------------------------ */

    public function test_head_contains_rss_alternate_link(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('rel="alternate"', $content);
        $this->assertStringContainsString('type="application/rss+xml"', $content);
        $this->assertStringContainsString(route('feed'), $content);
        