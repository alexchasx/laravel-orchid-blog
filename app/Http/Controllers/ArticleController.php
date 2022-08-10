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
        }

        return view('index', $this->getResponseArray($builder, $metaTitle));
    }

    public function showNotPublic(): View
    {
        return view('index', $this->getResponseArray(
            Article::orderBy('id', 'desc')->where('is_published', false),
            'Неопубликованные статьи'
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
            $tag->title
        ));
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

    protected function getResponseArray($builder, $metaTitle): array
    {
        return $this->getSideBar() + [
            'articles' => $builder->paginate(self::PAGINATE),
            'metaTitle' => $metaTitle,
            'metaDesc' => '',
        ];
    }
}
