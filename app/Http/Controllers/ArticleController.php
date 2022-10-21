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
        $searchString = $request->input('search');
        $builder = Article::published( Article::search($searchString));
        $metaTitle = '';
        if ($searchString) {
            $metaTitle = 'Результаты поиска для: ' . $searchString;
            // $metaRobot = 'noindex, nofollow';
        }

        return view('index', $this->responseFormat->getArray(builder: $builder, metaTitle: $metaTitle));
    }

    public function showNotPublic(): View
    {
        return view('index', $this->responseFormat->getArray(
            builder: Article::orderBy('id', 'desc')->where('is_published', false),
            metaTitle: 'Неопубликованные статьи',
            // metaDesc: 'noindex, nofollow',
        ));
    }

    public function show(Article $article): View
    {
        if (!$article->is_published) {
            $this->accessToNotPublic();
        }
        $result = $this->sidebar->getData() + ['article' => $article, 'metaTitle' => ''];

        return view('article', $result);
    }

    public function showByRubric(Rubric $rubric): View
    {
        return view('index', $this->responseFormat->getArray(
            builder: Article::byRubric($rubric->id),
            metaTitle: $rubric->title
        ));
    }

    public function showByTag(Tag $tag): View
    {
        $builder = Article::published()
            ->whereHas('tags', function (Builder $builder) use ($tag) {
                $builder->where('tag_id', $tag->id);
            });

        return view('index', $this->responseFormat->getArray(
            builder: $builder,
            metaTitle: 'Записи с меткой «' . $tag->title . '»'
        ));
    }
}
