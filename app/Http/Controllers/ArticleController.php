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
use Orchid\Platform\Http\Middleware\Access;
use phpDocumentor\Reflection\Types\This;

class ArticleController extends MainController
{
    public function index(Request $request): View
    {
        // $query = Article::published();
        // dd($query->toSql(), $query->getBindings());

        $result = $this->getSideBar()
            + [
                'articles' => Article::published(
                    Article::search($request->input('search'))
                )
                    // ->with('author', 'likes')
                    // ->withCount('comments', 'thumbnail', 'likes')
                    ->paginate(self::PAGINATE),
                'meta_title' => env('APP_NAME') . ' - ' . env('SUB_LOGO'),
                'meta_desc' => 'Блог по веб-разработке.'
            ];

        return view('index', $result);
    }

    public function showNotPublic(): View
    {
        $result = $this->getSideBar()
            + [
                'articles' => Article::orderBy('id', 'desc')
                    ->where('is_published', false)
                    ->paginate(self::PAGINATE),
                'pageTitle' => 'Неопубликованные статьи',
                'meta_title' => 'Неопубликованные статьи',
                'meta_desc' => ''
            ];

        return view('index', $result);
    }

    public function show(Article $article): View
    {
        if (!$article->is_published) {
            $this->accessToNotPublic();
        }

        $result = $this->getSideBar()
            + [
            'article' => $article,
        ];

        return view('article', $result);
    }

    public function showByRubric($id): View
    {
        $sideBar = $this->getSideBar();
        $pageTitle = $sideBar['rubrics']->find($id)->title;

        $result = $sideBar
            + [
                'articles' => Article::byRubric($id)->paginate(self::PAGINATE),
                'pageTitle' => $pageTitle,
                'meta_title' => $pageTitle,
                'meta_desc' => '',
            ];

        return view('index', $result);
    }

    public function showByTag(Tag $tag): View
    {
        $articlesByTag = Article::published()
            ->whereHas('tags', function (Builder $builder) use ($tag) {
                $builder->where('tag_id', $tag->id);
            });
        $pageTitle = $tag->title;

        $result = $this->getSideBar()
            + [
            'articles' => $articlesByTag->paginate(self::PAGINATE),
            'pageTitle' => $pageTitle,
            'meta_title' => $pageTitle,
            'meta_desc' => ''
        ];

        return view('index', $result);
    }

    protected function accessToNotPublic()
    {
        if ( (Auth::user() && !Auth::user()->hasAccess('platform.custom.articles')
                || false == Auth::user())
            ) {
            abort(403);
        }
    }

    protected function getSideBar(): array
    {
        return [
            'tags' => Tag::articlePublished()->get(),
            'rubrics' => Rubric::articlePublished()->get(),
        ];
    }
}
