<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Services\ArticleService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ArticleController extends Controller
{
    private const META_NO_ROBOTS = 'noindex, nofollow';
    private const MAIN_VIEW = 'index';
    private const PAGINATE = 6;

    public function __construct(
        private ArticleService $service,
        private CacheService $cache
    ) {}

    public function index(Request $request): View
    {
        $query = Article::published()
            ->with(['user', 'rubric', 'tags'])
            ->select('id', 'title', 'slug', 'excert', 'image', 'published_at', 'rubric_id', 'is_published');

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(function ($q2) use ($q) {
                $q2->where('title', 'LIKE', "%{$q}%")
                   ->orWhereRaw('content_html LIKE ?', ["%{$q}%"]);
            });
        }

        $articles = $query->orderBy('published_at', 'desc')->paginate(self::PAGINATE);

        return view(self::MAIN_VIEW, [
            'articles' => $articles,
            'search' => $request->input('search'),
            'metaTitle' => $request->filled('search') ? __('Результаты поиска для: ') . $request->input('search') : '',
            'metaDesc' => '',
        ]);
    }

    public function show(Article $article): View
    {
        $this->service->checkAccess($article);
        $article->load(['user', 'rubric', 'tags']);

        // Оглавление (якоря на подзаголовки) и контент с проставленными id.
        ['contentHtml' => $contentHtml, 'tocItems' => $tocItems] = $this->service->withToc($article);

        return view('article', [
            'article'     => $article,
            'contentHtml' => $contentHtml,
            'tocItems'    => $tocItems,
            'metaTitle'   => $article->title,
            'metaDesc'    => $article->meta_desc,
        ]);
    }

    public function showNotPublic(): View
    {
        return view(self::MAIN_VIEW, [
            'articles' => Article::query()->orderBy('id', 'desc')
                ->where('is_published', false)
                ->paginate(self::PAGINATE),
            'metaTitle' => __('Неопубликованные статьи'),
            'metaRobots' => self::META_NO_ROBOTS,
            'metaDesc' => '',
        ]);
    }

    public function showByRubric(Rubric $rubric): View
    {
        return view(self::MAIN_VIEW, [
            'articles' => Article::published()
                ->with(['user', 'rubric', 'tags'])
                ->select('id', 'title', 'slug', 'excert', 'image', 'published_at', 'rubric_id', 'is_published')
                ->where('rubric_id', $rubric->id)
                ->orderBy('published_at', 'desc')
                ->paginate(self::PAGINATE),
            'metaTitle' => $rubric->title,
            'metaDesc' => $rubric->description,
        ]);
    }

    public function showByTag(Tag $tag): View
    {
        return view(self::MAIN_VIEW, [
            'articles' => Article::published()
                ->with(['user', 'rubric', 'tags'])
                ->select('id', 'title', 'slug', 'excert', 'image', 'published_at', 'rubric_id', 'is_published')
                ->whereHas('tags', function ($q) use ($tag) {
                    $q->where('tag_id', $tag->id);
                })
                ->orderBy('published_at', 'desc')
                ->paginate(self::PAGINATE),
            'metaTitle' => __('Записи с меткой «') . $tag->title . '»',
            'metaDesc' => '',
        ]);
    }
}
