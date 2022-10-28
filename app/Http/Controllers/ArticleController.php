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

    public function __construct(
        private ArticleService $service,
        private CacheService $cache
    ) {}

    public function index(Request $request): View
    {
        return view(self::MAIN_VIEW, [
            'tags' => $this->cache->remember(Tag::class),
            'rubrics' => $this->cache->remember(Rubric::class),
            'articles' => $this->service->getPublic($search = $request->input('search')),
            'metaTitle' => $search ? __('Результаты поиска для: ') . $search : '',
            'metaRobots' => self::META_NO_ROBOTS,
            'metaDesc' => '',
        ]);
    }

    public function show(Article $article): View
    {
        $this->service->checkAccess($article);
        return view('article', [
            'tags' => $this->cache->remember(Tag::class),
            'rubrics' => $this->cache->remember(Rubric::class),
            'article' => $article,
            'metaTitle' => $article->title,
            'metaDesc' => $article->meta_desc,
        ]);
    }

    public function showNotPublic(): View
    {
        return view(self::MAIN_VIEW, [
            'tags' => $this->cache->remember(Tag::class),
            'rubrics' => $this->cache->remember(Rubric::class),
            'articles' => $this->service->getNotPublic(),
            'metaTitle' => __('Неопубликованные статьи'),
            'metaRobots' => self::META_NO_ROBOTS,
            'metaDesc' => '',
        ]);
    }

    public function showByRubric(Rubric $rubric): View
    {
        return view(self::MAIN_VIEW, [
            'tags' => $this->cache->remember(Tag::class),
            'rubrics' => $this->cache->remember(Rubric::class),
            'articles' => $this->service->getByRubric($rubric->id),
            'metaTitle' => $rubric->title,
            'metaDesc' => $rubric->description,
        ]);
    }

    public function showByTag(Tag $tag): View
    {
        return view(self::MAIN_VIEW, [
            'tags' => $this->cache->remember(Tag::class),
            'rubrics' => $this->cache->remember(Rubric::class),
            'articles' => $this->service->getByTag($tag->id),
            'metaTitle' => __('Записи с меткой «') . $tag->title . '»',
            'metaDesc' => '',
        ]);
    }
}
