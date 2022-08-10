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
                'metaTitle' => env('APP_NAME') . ' - ' . env('SUB_LOGO'),
                'metaDesc' => 'Блог по веб-разработке.',
                'withoutPageTitle' => true,
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
                'metaTitle' => 'Неопубликованные статьи',
                'metaDesc' => ''
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
                'withoutPageTitle' => true,
            ];

        return view('article', $result);
    }

    public function showByRubric(Rubric $rubric): View
    {
        $result = $this->getSideBar()
            + [
                'articles' => Article::byRubric($rubric->id)->paginate(self::PAGINATE),
                'metaTitle' => $rubric->title,
                'metaDesc' => '',
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
            'metaTitle' => $pageTitle,
            'metaDesc' => ''
        ];

        return view('index', $result);
    }

    protected function accessToNotPublic()
    {
        /** @var User $user */
        $user = Auth::user();
        if ( ($user && !$user->isAdmin()) || !$user) {
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
