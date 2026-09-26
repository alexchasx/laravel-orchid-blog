<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SitemapController extends Controller
{
    /**
     * Генерация sitemap.xml с кэшированием (1 час).
     */
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap.xml', 3600, function (): string {
            $pages = $this->staticPages();
            $articles = $this->publishedArticles();
            $rubrics = $this->publishedRubrics();
            $tags = $this->activeTags();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'
                . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
                . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'
                . "\n";

            foreach ($pages as $url) {
                $xml .= $this->urlEntry($url['loc'], $url['lastmod'] ?? null, $url['changefreq'] ?? 'monthly', $url['priority'] ?? '0.5');
            }

            foreach ($articles as $article) {
                $loc = route('articleShow', $article);
                $image = '';
                if (!empty($article->image) && Storage::exists($article->image)) {
                    $image = sprintf(
                        '    <image:image>'
                        . '<image:url>%s</image:url>'
                        . '<image:title>%s</image:title>'
                        . '</image:image>',
                        e(Storage::url($article->image)),
                        e($article->title)
                    );
                }
                $xml .= $this->urlEntry(
                    $loc,
                    $article->updated_at?->toAtomString(),
                    'weekly',
                    '0.8'
                );
                if ($image) {
                    $xml .= $image . "\n";
                }
            }

            foreach ($rubrics as $rubric) {
                $xml .= $this->urlEntry(
                    route('showByRubric', $rubric),
                    null,
                    'monthly',
                    '0.6'
                );
            }

            foreach ($tags as $tag) {
                $xml .= $this->urlEntry(
                    route('showByTag', $tag),
                    null,
                    'monthly',
                    '0.5'
                );
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Статические страницы: главная, about, contact, privacy.
     *
     * @return array<array{loc: string, lastmod?: string, changefreq: string, priority: string}>
     */
    private function staticPages(): array
    {
        return [
            [
                'loc' => route('home'),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('about'),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('contact'),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('privacy'),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ],
        ];
    }

    /**
     * Опубликованные статьи.
     *
     * @return \Illuminate\Database\Collection<int, \App\Models\Article>
     */
    private function publishedArticles(): \Illuminate\Database\Collection
    {
        return Article::query()
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->select('id', 'slug', 'updated_at', 'image')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Рубрики, у которых есть хотя бы одна опубликованная статья.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rubric>
     */
    private function publishedRubrics(): \Illuminate\Database\Eloquent\Collection
    {
        return Rubric::query()
            ->whereHas('articles', function ($q) {
                $q->where('is_published', true)
                    ->where('published_at', '<=', now());
            })
            ->select('id', 'title')
            ->orderBy('title')
            ->get();
    }

    /**
     * Активные теги, у которых есть хотя бы одна опубликованная статья.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag>
     */
    private function activeTags(): \Illuminate\Database\Eloquent\Collection
    {
        return Tag::query()
            ->where('active', true)
            ->whereHas('articles', function ($q) {
                $q->where('is_published', true)
                    ->where('published_at', '<=', now());
            })
            ->select('id', 'title')
            ->orderBy('title')
            ->get();
    }

    /**
     * Формирует XML-запись для одного URL.
     */
    private function urlEntry(string $loc, ?string $lastmod, string $changefreq, string $priority): string
    {
        $xml = '    <url>' . "\n"
            . '        <loc>' . e($loc) . '</loc>' . "\n";

        if ($lastmod) {
            $xml .= '        <lastmod>' . $lastmod . '</lastmod>' . "\n";
        }

        $xml .= '        <changefreq>' . e($changefreq) . '</changefreq>' . "\n"
            . '        <priority>' . $priority . '</priority>' . "\n"
            . '    </url>' . "\n";

        return $xml;
    }
}
