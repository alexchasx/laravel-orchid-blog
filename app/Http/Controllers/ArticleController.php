<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Services\ArticleService;
use App\Support\MathCaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class ArticleController extends Controller
{
    private const META_NO_ROBOTS = 'noindex, nofollow';
    private const MAIN_VIEW = 'index';

    public function __construct(
        private ArticleService $service
    ) {}

    public function index(Request $request): View
    {
        $articles = $this->service->getPublic($request->input('search'));

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

        // Счётчик просмотров: простое инкрементирование без транзакции
        // (допустима потеря пары просмотров при параллельных запросах).
        $article->increment('viewed');

        $article->load(['user', 'rubric', 'tags']);

        // Оглавление (якоря на подзаголовки) и контент с проставленными id.
        ['contentHtml' => $contentHtml, 'tocItems' => $tocItems] = $this->service->withToc($article);

        return view('article', [
            'article'     => $article,
            'contentHtml' => $contentHtml,
            'tocItems'    => $tocItems,
            'captcha'     => Auth::guest() ? MathCaptcha::question() : null,
            'metaTitle'   => $article->title,
            'metaDesc'    => $article->meta_desc,
        ]);
    }

    public function showNotPublic(): View
    {
        return view(self::MAIN_VIEW, [
            'articles' => $this->service->getNotPublic(),
            'metaTitle' => __('Неопубликованные статьи'),
            'metaRobots' => self::META_NO_ROBOTS,
            'metaDesc' => '',
        ]);
    }

    public function showByRubric(Rubric $rubric): View
    {
        return view(self::MAIN_VIEW, [
            'articles' => $this->service->getByRubric($rubric->id),
            'metaTitle' => $rubric->title,
            'metaDesc' => $rubric->description,
        ]);
    }

    public function showByTag(Tag $tag): View
    {
        return view(self::MAIN_VIEW, [
            'articles' => $this->service->getByTag($tag->id),
            'metaTitle' => __('Записи с меткой «') . $tag->title . '»',
            'metaDesc' => '',
        ]);
    }
}
