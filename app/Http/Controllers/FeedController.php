<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class FeedController extends Controller
{
    /**
     * Генерация RSS 2.0-ленты с кэшированием (1 час).
     *
     * @return Response
     */
    public function __invoke(): Response
    {
        $xml = Cache::remember('rss.feed', 3600, function (): string {
            $articles = $this->publishedArticles();
            $siteUrl = config('app.url');
            $siteName = config('app.name');
            $siteDesc = config('seo.default_description', '');
            $lastBuildDate = $articles->isNotEmpty()
                ? $articles->first()->updated_at?->toRssString()
                : now()->toRssString();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'
                . "\n"
                . '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'
                . "\n"
                . '  <channel>'
                . "\n"
                . '    <title>' . e($siteName) . '</title>'
                . "\n"
                . '    <link>' . e($siteUrl) . '</link>'
                . "\n"
                . '    <description>' . e($siteDesc) . '</description>'
                . "\n"
                . '    <language>ru</language>'
                . "\n"
                . '    <lastBuildDate>' . e((string) $lastBuildDate) . '</lastBuildDate>'
                . "\n"
                . '    <atom:link href="' . e(route('feed')) . '" rel="self" type="application/rss+xml"/>'
                . "\n";

            foreach ($articles as $article) {
                $articleUrl = route('articleShow', $article);
                $enclosure = '';

                if (!empty($article->image) && Storage::exists($article->image)) {
                    $imageUrl = e(\App\Support\Seo::absoluteUrl(Storage::url($article->image)));
                    $fileSize = (int) Storage::size($article->image);
                    $enclosure = sprintf(
                        '    <enclosure url="%s" length="%d" type="image/jpeg"/>' . "\n",
                        $imageUrl,
                        max(0, $fileSize)
                    );
                }

                $description = $article->meta_desc ?: $article->excert ?: '';

                $xml .= '    <item>' . "\n"
                    . '      <title>' . e($article->title) . '</title>' . "\n"
                    . '      <link>' . e($articleUrl) . '</link>' . "\n"
                    . '      <guid>' . e($articleUrl) . '</guid>' . "\n"
                    . '      <pubDate>' . e((string) $article->published_at?->toRssString()) . '</pubDate>' . "\n"
                    . '      <description>' . e($description) . '</description>' . "\n"
                    . $enclosure
                    . '    </item>' . "\n";
            }

            $xml .= '  </channel>' . "\n"
                . '</rss>';

            return $xml;
        });

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=utf-8',
        ]);
    }

    /**
     * Опубликованные статьи (не более 20), отсортированные по дате публикации.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Article>
     */
    private function publishedArticles(): \Illuminate\Database\Eloquent\Collection
    {
        return Article::query()
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->select('id', 'slug', 'title', 'excert', 'meta_desc', 'published_at', 'updated_at', 'image')
            ->orderByDesc('published_at')
            ->limit(20)
            ->get();
    }
}
