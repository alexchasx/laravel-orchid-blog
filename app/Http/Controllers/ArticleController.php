<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return view('index', $this->getResponseArray($builder, $metaTitle));
    }

    public function showNotPublic(): View
    {
        return view('index', $this->getResponseArray(
            Article::orderBy('id', 'desc')->where('is_published', false),
            'Неопубликованные статьи',
            // 'noindex, nofollow',
        ));
    }

    public function show(Article $article): View
    {
        if (!$article->is_published) {
            $this->accessToNotPublic();
        }
        $result = $this->getSideBar()
            + [
                'article' => $article,
                'metaTitle' => '',
            ];

        return view('article', $result);
    }

    public function showByRubric(Rubric $rubric): View
    {
        return view('index', $this->getResponseArray(
            Article::byRubric($rubric->id),
            $rubric->title
        ));
    }

    public function showByTag(Tag $tag): View
    {
        $builder = Article::published()
            ->whereHas('tags', function (Builder $builder) use ($tag) {
                $builder->where('tag_id', $tag->id);
            });

        return view('index', $this->getResponseArray(
            $builder,
            'Записи с меткой «' . $tag->title . '»'
        ));
    }
}
