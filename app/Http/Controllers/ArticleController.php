<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends ParentController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {
        // $query = Article::published();
        // dd($query->toSql(), $query->getBindings());

        // return view('index')->with([
        //     'articles' => Article::published()->paginate(self::PAGINATE, [
        //         'id',
        //         'title',
        //         'excert',
        //         'published_at',
        //     ]),
        //     'rubrics' => Rubric::articlePublished()->get(),
        //     'currentTagId' => 'all',
        //     'tags' => Tag::articlePublished()->get(),
        // ]);

        return view('index', [
            'articles' => Article::search($request->input('search'))
                // ->with('author', 'likes')
                // ->withCount('comments', 'thumbnail', 'likes')
                // ->latest()
                ->paginate(self::PAGINATE),
            'rubrics' => Rubric::articlePublished()->get(),
            'currentTagId' => 'all',
            'tags' => Tag::articlePublished()->get(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $article =  Article::findOrFail($id);
        // $article->comments_count = $article->comments()->count();
        // dd($article->comments_count);

        return view('article')->with([
            'article' => $article,
            'rubrics' => Rubric::articlePublished()->get(),
            'tags' => Tag::articlePublished()->get(),
            'comments' => $article->comments,
        ]);
    }

    /**
     * Показывает статьи по категории
     */
    public function showByRubric($id)
    {
        $rubrics = Rubric::articlePublished()->get();

        return view('index')->with([
            'articles' => Article::byRubric($id)->paginate(self::PAGINATE),
            'currentRubric' => $rubrics->find($id),
            'rubrics' => $rubrics,
            'tags' => Tag::articlePublished()->get(),
        ]);
    }

    /**
     * Показывает статьи по тегу
     */
    public function showByTag($tagId)
    {
        $tags = Tag::articlePublished()->get();
        $articlesByTag = Article::published()
            ->whereHas('tags', function (Builder $builder) use ($tagId) {
                $builder->where('tag_id', $tagId);
            });

        return view('index')->with([
            'articles' => $articlesByTag->paginate(self::PAGINATE),
            'currentTagId' => $tagId,
            'rubrics' => Rubric::articlePublished()->get(),
            'tags' => $tags,
        ]);
    }

    /**
     * Поиск статей по словам в контексте
     */
    public function search(Request $request)
    {
        $query = $request->query();

        dd($query);

        $articles = [];
        if ($query) {
            $articles = Article::published()
                ->where('title', 'LIKE', "%{$query}%")
                ->orWhere('excert', 'LIKE', "%{$query}%")
                ->orWhere('content_raw', 'LIKE', "%{$query}%")
                ->paginate(self::PAGINATE);
        }

        return view('index')->with([
            'articles' => $articles,
            'rubrics' => Rubric::articlePublished()->get(),
            'tags' => Tag::articlePublished()->get(),
        ]);
    }
}
