<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends MainController
{
    public function index(Request $request): View
    {
        return $this->service->getResponse(
            builder: Article::published( 
                Article::search($searchString = $request->input('search'))), 
            metaTitle: $searchString ? __('Результаты поиска для: ') . $searchString : '',
            metaRobots: 'noindex, nofollow',
        );
    }

    public function show(Article $article): View
    {
        $this->checkAccess($article);

        return $this->service->getResponse(
            articles: $article,
            metaTitle: $article->title,
            metaDesc: $article->meta_desc,
            view: 'article',
        );
    }

    public function showNotPublic(): View
    {
        return $this->service->getResponse(
            builder: Article::orderBy('id', 'desc')
                ->where('is_published', false),
            metaTitle: __('Неопубликованные статьи'),
            metaRobots: 'noindex, nofollow',
        );
    }

    public function showByRubric(Rubric $rubric): View
    {
        return $this->service->getResponse(
            builder: Article::byRubric($rubric->id),
            metaTitle: $rubric->title,
            metaDesc: $rubric->description,
        );
    }

    public function showByTag(Tag $tag): View
    {
        return $this->service->getResponse(
            builder: Article::published()
                ->whereHas('tags', function (Builder $builder) use ($tag) {
                    $builder->where('tag_id', $tag->id);
                }),
            metaTitle: __('Записи с меткой «') . $tag->title . '»',
        );
    }
}
