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

class ArticleController extends MainController
{
    /**
     * Display a listing of the resource
     */
    public function index(Request $request): View
    {
        // $query = Article::published();
        // dd($query->toSql(), $query->getBindings());

        return view('index', [
            'articles' => Article::published(
                Article::search($request->input('search')))
                // ->with('author', 'likes')
                // ->withCount('comments', 'thumbnail', 'likes')
                    ->paginate(self::PAGINATE),
            'rubrics' => Rubric::articlePublished()->get(),
            'currentTagId' => 'all',
            'tags' => Tag::articlePublished()->get(),
            'meta_title' => env('APP_NAME') . ' - блокнот веб-разработчика',
            'meta_desc' => 'Блог по веб-разработке.'
        ]);
    }

    /**
     *
     */
    public function showNotPublic(): View
    {
        $articles = Article::orderBy('id', 'desc')
            ->where('is_published', false)
            ->paginate(self::PAGINATE);

        return view('index', [
            'articles' => $articles,
            'rubrics' => Rubric::articlePublished()->get(),
            'currentTagId' => 'all',
            'tags' => Tag::articlePublished()->get(),
        ]);
    }

    /**
     * Display the specified resource
     */
    public function show(Article $article): View
    {
        // $article =  Article::findOrFail($id);
        // $article->comments_count = $article->comments()->count();
        // dd($article->comments_count);

        if ($article->is_published == false && (!Auth::user()
                || (Auth::user()
                    && !Auth::user()->hasAccess('platform.custom.articles')))) {

            abort(403);
        }

        return view('article', [
            'article' => $article,
            'rubrics' => Rubric::articlePublished()->get(),
            'tags' => Tag::articlePublished()->get(),
            'comments' => $article->comments,
        ]);
    }

    /**
     * Показывает статьи по категории
     */
    public function showByRubric($id): View
    {
        $rubrics = Rubric::articlePublished()->get();

        return view('index', [
            'articles' => Article::byRubric($id)->paginate(self::PAGINATE),
            'currentRubric' => $rubrics->find($id),
            'rubrics' => $rubrics,
            'tags' => Tag::articlePublished()->get(),
        ]);
    }

    /**
     * Показывает статьи по тегу
     */
    public function showByTag($tagId): View
    {
        $tags = Tag::articlePublished()->get();
        $articlesByTag = Article::published()
            ->whereHas('tags', function (Builder $builder) use ($tagId) {
                $builder->where('tag_id', $tagId);
            });

        return view('index', [
            'articles' => $articlesByTag->paginate(self::PAGINATE),
            'currentTagId' => $tagId,
            'rubrics' => Rubric::articlePublished()->get(),
            'tags' => $tags,
        ]);
    }
}
